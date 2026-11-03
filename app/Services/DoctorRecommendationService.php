<?php

namespace App\Services;

use App\Models\Doctors;
use App\Models\BookAppointment;
use App\Models\Doctor_Hoilday;
use App\Models\Services;
use Illuminate\Support\Facades\DB;

class DoctorRecommendationService
{
    /**
     * Get a list of recommended doctors based on specialty and a scoring algorithm.
     *
     * @param string $specialtyName
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getRecommendedDoctors(string $specialtyName, int $limit = 3)
    {
        // Find the service (specialty) ID from the name
        $service = Services::where('name', 'like', '%' . $specialtyName . '%')
            ->where('recommendable', 1) // Only consider recommendable specialties
            ->first();

        if (!$service) {
            return collect(); // Return an empty collection if specialty not found
        }

        // Base query for active, approved doctors of the correct specialty
        $doctors = Doctors::where('department_id', $service->id)
            ->where('is_approve', '1')
            ->where('recommendable', 1) // Only consider recommendable doctors
            ->where('profile_type', '1') // Ensure it's a doctor
            ->withAvg('reviews', 'rating')
            ->with(['schedules.getslotls', 'holidays'])->withCount('referrals') // Eager load nested availability info
            ->get();

        if ($doctors->isEmpty()) {
            return collect();
        }

        // Pre-fetch all booked slots for today to avoid N+1 queries inside the loop
        $today = now();
        $bookedSlotIdsByDoctor = BookAppointment::where('date', $today->toDateString())
            ->whereIn('doctor_id', $doctors->pluck('id'))
            ->get()
            ->groupBy('doctor_id')
            ->map(function ($appointments) {
                return $appointments->pluck('slot_id');
            });

        // Calculate a score for each doctor
        $scoredDoctors = $doctors->map(function ($doctor) use ($today, $bookedSlotIdsByDoctor) {
            $score = 0;

            // 1. Featured Status (highest impact)
            if ($doctor->featured == 1 && $doctor->featured_until && $today->lt($doctor->featured_until)) {
                $score += 100;
            }

            // 2. Availability (high impact)
            $isOnHoliday = $doctor->holidays->some(function ($holiday) use ($today) {
                try {
                    return $today->isBetween(\Carbon\Carbon::parse($holiday->start_date), \Carbon\Carbon::parse($holiday->end_date));
                } catch (\Exception $e) {
                    return false; // Ignore invalid holiday date formats
                }
            });

            if (!$isOnHoliday) {
                $schedulesForToday = $doctor->schedules->where('day_id', $today->dayOfWeekIso - 1);
                if ($schedulesForToday->isNotEmpty()) {
                    // Get all possible slot IDs for the doctor today
                    $allSlotIdsForToday = $schedulesForToday->flatMap(fn($schedule) => $schedule->getslotls->pluck('id'));
                    // Get the slots already booked for this doctor today
                    $bookedSlotsForThisDoctor = $bookedSlotIdsByDoctor->get($doctor->id, collect());
                    // If there's a difference, it means there are available slots
                    if ($allSlotIdsForToday->diff($bookedSlotsForThisDoctor)->isNotEmpty()) {
                        $score += 25; // Bonus for having open slots today
                    }
                }
            }

            // 3. Active Subscription (high impact)
            if ($doctor->activeSubscription()) {
                $score += 50;
            }

            // 4. Average Rating (significant impact)
            $score += ($doctor->reviews_avg_rating ?? 0) * 10; // Scale rating (0-5) to 0-50 points

            // 5. Profile Completeness (moderate impact)
            if (!empty($doctor->aboutus)) { $score += 10; }
            if ($doctor->image && $doctor->image !== '1739842923.png') { $score += 10; } // Assuming '1739842923.png' is a default
            if (!empty($doctor->working_time)) { $score += 5; }

            // 6. Referral Count (small impact, capped at 10 points)
            $score += min($doctor->referrals_count, 10);

            // 7. Randomness factor (small impact, for unpredictability)
            $score += rand(0, 5);

            $doctor->recommendation_score = $score;
            return $doctor;
        });

        // Sort by score descending and take the top N
        return $scoredDoctors->sortByDesc('recommendation_score')->take($limit);
    }
}