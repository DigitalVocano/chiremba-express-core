<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Services;
use App\Models\Doctors;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap XML file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sitemap = Sitemap::create();

        // Add static pages
        $sitemap->add(Url::create('/')->setPriority(1.0));
        $sitemap->add(Url::create('/aboutus')->setPriority(0.8));
        $sitemap->add(Url::create('/contactus')->setPriority(0.8));
        $sitemap->add(Url::create('/ai-health-assistant')->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
        $sitemap->add(Url::create('/insurance')->setPriority(0.8));
        $sitemap->add(Url::create('/corporates')->setPriority(0.8));

        // Add services
        Services::all()->each(function (Services $service) use ($sitemap) {
            $sitemap->add(Url::create("/searchdoctor?type={$service->id}")
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.9));
        });

        // Add doctors (adjust the route as needed)
        Doctors::all()->each(function (Doctors $doctor) use ($sitemap) {
            $sitemap->add(Url::create("/viewdoctor/{$doctor->slug}")
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.7));
        });

        // Save the sitemap to the public directory
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');
    }
}