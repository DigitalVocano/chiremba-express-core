<?php

namespace App\Services;

use App\Models\PharmacyProduct;
use App\Models\Doctors; // Pharmacies are in this table
use Illuminate\Support\Facades\DB;

class PharmacyRecommendationService
{
    /**
     * Get a list of recommended pharmacies based on a scoring algorithm.
     *
     * @param array $suggestedMedicines
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getRecommendedPharmacies(array $suggestedMedicines = [], int $limit = 2)
    {
        // Base query for active, approved, recommendable pharmacies
        $pharmacies = Doctors::where('profile_type', '2') // 2 for Pharmacy
            ->where('is_approve', '1')
            ->where('recommendable', 1)
            ->withAvg('reviews', 'rating') // Eager load average rating
            ->withCount(['pharmacyProducts', 'referrals']) // Eager load counts
            ->get();

        if ($pharmacies->isEmpty()) {
            return collect();
        }

        // Pre-fetch products for matching if medicines are suggested, to avoid N+1 queries
        $pharmacyProducts = [];
        if (!empty($suggestedMedicines)) {
            $products = PharmacyProduct::whereIn('pharmacy_id', $pharmacies->pluck('id'))
                ->where(function ($query) use ($suggestedMedicines) {
                    foreach ($suggestedMedicines as $medicine) {
                        // A simple LIKE search for each medicine name
                        $query->orWhere('name', 'like', '%' . $medicine . '%');
                    }
                })
                ->pluck('pharmacy_id')
                ->unique(); // Get a unique list of pharmacy IDs that have at least one match
            $pharmacyProducts = $products->all();
        }

        // Calculate a score for each pharmacy
        $scoredPharmacies = $pharmacies->map(function ($pharmacy) use ($suggestedMedicines, $pharmacyProducts) {
            $score = 0;

            // 1. Featured Status (high impact)
            if ($pharmacy->featured == 1 && $pharmacy->featured_until && now()->lt($pharmacy->featured_until)) {
                $score += 100;
            }

            // 2. Active Subscription (high impact)
            if ($pharmacy->activeSubscription()) {
                $score += 50;
            }

            // 3. Medication Match (randomized high impact)
            // This check is randomized to prevent users from gaming the system easily.
            if (rand(0, 1) == 1 && !empty($suggestedMedicines) && in_array($pharmacy->id, $pharmacyProducts)) {
                $score += 40; // Bonus if they have at least one of the suggested medicines
            }

            // 4. Average Rating (significant impact)
            $score += ($pharmacy->reviews_avg_rating ?? 0) * 10; // Scale rating (0-5) to 0-50 points

            // 5. Number of Products (moderate impact, capped at 20 points)
            $score += min($pharmacy->pharmacy_products_count, 20);

            // 6. Referral Count (small impact, capped at 10 points)
            $score += min($pharmacy->referrals_count, 10);

            // 7. Randomness factor (small impact for tie-breaking)
            $score += rand(0, 5);

            $pharmacy->recommendation_score = $score;
            return $pharmacy;
        });

        // Sort by score descending and take the top N
        return $scoredPharmacies->sortByDesc('recommendation_score')->take($limit);
    }
}