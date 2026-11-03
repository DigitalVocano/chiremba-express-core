<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Doctors;
use Carbon\Carbon;

class UnfeatureExpiredDoctors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'featured:unfeature-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unfeatures doctors and pharmacies whose featured_until date has passed.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredProfiles = Doctors::where('featured', 1)
                                  ->whereNotNull('featured_until')
                                  ->where('featured_until', '<', Carbon::now())
                                  ->get();

        foreach ($expiredProfiles as $profile) {
            $profile->featured = 0;
            $profile->featured_until = null;
            $profile->save();
            $this->info("Unfeatured profile ID: {$profile->id} ({$profile->name})");
        }

        $this->info('Unfeaturing process completed.');
    }
}
