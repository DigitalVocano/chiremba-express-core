<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppointmentMedicines;
use App\Services\NotificationService;

class SendMedicineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:medicine-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send medicine reminders to patients';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(NotificationService $notificationService)
    {
        $currentHour = now()->format('H:');
        $prescriptions = AppointmentMedicines::with('bookAppointment')
            ->where('created_at', '>=', now()->subDays(14))
            ->get();

        foreach ($prescriptions as $prescription) {
            $appointment = $prescription->bookAppointment;
            if (!$appointment || !$appointment->user_id) {
                continue;
            }

            $medData = json_decode($prescription->medicines, true);
            $items = $medData['medicine'] ?? [];
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                $medName = $item['medicine_name'] ?? ($item['name'] ?? 'prescribed medicine');
                $times = $item['time'] ?? [];
                if (!is_array($times)) {
                    $times = [$times];
                }
                foreach ($times as $timeVal) {
                    $timeStr = is_array($timeVal) ? ($timeVal['t_time'] ?? '') : (string)$timeVal;
                    if (!empty($timeStr) && str_starts_with($timeStr, $currentHour)) {
                        $msg = "Medicine Reminder: It is time to take your {$medName}.";
                        $notificationService->sendNotification($msg, $appointment->user_id, 'user_id', $appointment->id, [
                            'type' => 'medicine_reminder',
                            'order_id' => (string)$appointment->id,
                        ]);
                    }
                }
            }
        }

        $this->info('Medicine reminders sent successfully!');
        return 0;
    }
}
