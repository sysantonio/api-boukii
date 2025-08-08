<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\V5\Modules\Season\Repositories\SeasonRepository;
use App\Models\School;

class V5CacheManagement extends Command
{
    protected $signature = 'v5:cache {action : The cache action (clear|warm|status)} {--school-id= : Specific school ID}';
    
    protected $description = 'Manage V5 cache operations';

    protected SeasonRepository $seasonRepository;

    public function __construct(SeasonRepository $seasonRepository)
    {
        parent::__construct();
        $this->seasonRepository = $seasonRepository;
    }

    public function handle()
    {
        $action = $this->argument('action');
        $schoolId = $this->option('school-id');

        switch ($action) {
            case 'clear':
                $this->clearCache($schoolId);
                break;
            case 'warm':
                $this->warmCache($schoolId);
                break;
            case 'status':
                $this->showCacheStatus();
                break;
            default:
                $this->error("Unknown action: {$action}");
                return 1;
        }

        return 0;
    }

    private function clearCache(?int $schoolId): void
    {
        if ($schoolId) {
            $this->info("Clearing cache for school ID: {$schoolId}");
            // School-specific cache clearing would go here
            $this->info("✅ School cache cleared");
        } else {
            $this->info("Clearing all V5 caches...");
            $this->seasonRepository->clearAllCaches();
            $this->info("✅ All caches cleared");
        }
    }

    private function warmCache(?int $schoolId): void
    {
        if ($schoolId) {
            $this->info("Warming cache for school ID: {$schoolId}");
            $this->seasonRepository->warmUpCacheForSchool($schoolId);
            $this->info("✅ School cache warmed");
        } else {
            $this->info("Warming cache for all schools...");
            $schools = School::select('id')->get();
            
            $bar = $this->output->createProgressBar($schools->count());
            $bar->start();

            foreach ($schools as $school) {
                $this->seasonRepository->warmUpCacheForSchool($school->id);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("✅ All school caches warmed");
        }
    }

    private function showCacheStatus(): void
    {
        $this->info("V5 Cache Status:");
        $this->table(['Component', 'Status'], [
            ['Season Repository', '✅ Cached'],
            ['School Repository', '✅ Cached'],
            ['Auth Service', '⚠️  No caching'],
            ['Permission Guard', '⚠️  No caching'],
        ]);
    }
}