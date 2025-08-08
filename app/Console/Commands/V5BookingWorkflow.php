<?php

namespace App\Console\Commands;

use App\V5\Modules\Booking\Services\BookingWorkflowService;
use App\V5\Logging\V5Logger;
use Illuminate\Console\Command;

/**
 * V5 Booking Workflow Command
 * 
 * Automates booking workflow operations:
 * - Auto-confirm eligible bookings
 * - Auto-complete finished bookings  
 * - Cancel expired pending bookings
 * - Process no-show bookings
 */
class V5BookingWorkflow extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'v5:booking-workflow 
                            {--season-id= : Specific season ID to process}
                            {--school-id= : Specific school ID to process}
                            {--operation= : Specific operation (confirm|complete|cancel-expired|no-show)}
                            {--dry-run : Preview changes without executing}';

    /**
     * The console command description.
     */
    protected $description = 'Automate V5 booking workflow operations (confirm, complete, cancel expired, no-show)';

    public function __construct(
        private BookingWorkflowService $workflowService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        V5Logger::initializeCorrelation();
        V5Logger::logCustomEvent('booking_workflow_command_started', 'info', [
            'options' => $this->options(),
        ], 'system');

        try {
            $seasonId = $this->option('season-id');
            $schoolId = $this->option('school-id');
            $operation = $this->option('operation');
            $isDryRun = $this->option('dry-run');

            if ($isDryRun) {
                $this->info('🔍 DRY RUN MODE - No changes will be made');
                $this->newLine();
            }

            // Get active seasons/schools if not specified
            $contexts = $this->getProcessingContexts($seasonId, $schoolId);

            if (empty($contexts)) {
                $this->warn('No active seasons/schools found to process');
                return Command::SUCCESS;
            }

            $totalProcessed = 0;

            foreach ($contexts as $context) {
                $this->info("📋 Processing Season {$context['season_id']} - School {$context['school_id']}");
                
                $processed = $this->processWorkflowForContext(
                    $context['season_id'],
                    $context['school_id'], 
                    $operation,
                    $isDryRun
                );

                $totalProcessed += array_sum($processed);
                $this->displayResults($processed, $context);
            }

            $this->newLine();
            $this->info("✅ Workflow completed. Total processed: {$totalProcessed}");

            V5Logger::logCustomEvent('booking_workflow_command_completed', 'info', [
                'total_processed' => $totalProcessed,
                'contexts_processed' => count($contexts),
            ], 'system');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            V5Logger::logSystemError($e, [
                'command' => 'v5:booking-workflow',
                'options' => $this->options(),
            ]);

            $this->error('❌ Error executing booking workflow: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Process workflow operations for a specific context
     */
    private function processWorkflowForContext(
        int $seasonId, 
        int $schoolId, 
        ?string $operation, 
        bool $isDryRun
    ): array {
        $results = [
            'auto_confirmed' => 0,
            'auto_completed' => 0,
            'expired_cancelled' => 0,
            'no_shows' => 0,
        ];

        // Auto-confirm eligible bookings
        if (!$operation || $operation === 'confirm') {
            if ($isDryRun) {
                $summary = $this->workflowService->getWorkflowStatusSummary($seasonId, $schoolId);
                $results['auto_confirmed'] = $summary['eligible_for_auto_confirm'];
                $this->line("  📝 Would auto-confirm: {$results['auto_confirmed']} bookings");
            } else {
                $confirmed = $this->workflowService->autoConfirmEligibleBookings($seasonId, $schoolId);
                $results['auto_confirmed'] = count($confirmed);
                $this->line("  ✅ Auto-confirmed: {$results['auto_confirmed']} bookings");
            }
        }

        // Auto-complete finished bookings
        if (!$operation || $operation === 'complete') {
            if ($isDryRun) {
                $summary = $this->workflowService->getWorkflowStatusSummary($seasonId, $schoolId);
                $results['auto_completed'] = $summary['finished_bookings'];
                $this->line("  📝 Would auto-complete: {$results['auto_completed']} bookings");
            } else {
                $completed = $this->workflowService->autoCompleteFinishedBookings($seasonId, $schoolId);
                $results['auto_completed'] = count($completed);
                $this->line("  🏁 Auto-completed: {$results['auto_completed']} bookings");
            }
        }

        // Cancel expired pending bookings
        if (!$operation || $operation === 'cancel-expired') {
            if ($isDryRun) {
                $summary = $this->workflowService->getWorkflowStatusSummary($seasonId, $schoolId);
                $results['expired_cancelled'] = $summary['expired_pending'];
                $this->line("  📝 Would cancel expired: {$results['expired_cancelled']} bookings");
            } else {
                $cancelled = $this->workflowService->cancelExpiredPendingBookings($seasonId, $schoolId);
                $results['expired_cancelled'] = count($cancelled);
                $this->line("  ❌ Cancelled expired: {$results['expired_cancelled']} bookings");
            }
        }

        // Process no-show candidates (requires manual review, so just report)
        if (!$operation || $operation === 'no-show') {
            $summary = $this->workflowService->getWorkflowStatusSummary($seasonId, $schoolId);
            $results['no_shows'] = $summary['no_show_candidates'];
            
            if ($results['no_shows'] > 0) {
                $this->warn("  ⚠️  {$results['no_shows']} bookings may be no-shows (requires manual review)");
            }
        }

        return $results;
    }

    /**
     * Get processing contexts (season/school combinations)
     */
    private function getProcessingContexts(?int $seasonId, ?int $schoolId): array
    {
        // If both specified, return single context
        if ($seasonId && $schoolId) {
            return [['season_id' => $seasonId, 'school_id' => $schoolId]];
        }

        // For now, return empty array - in real implementation this would
        // query active seasons and schools from the database
        // This would typically look like:
        // return \App\V5\Modules\Season\Models\Season::active()
        //     ->with('school')
        //     ->get()
        //     ->map(fn($season) => [
        //         'season_id' => $season->id,
        //         'school_id' => $season->school_id
        //     ])
        //     ->toArray();

        return [];
    }

    /**
     * Display results for a context
     */
    private function displayResults(array $results, array $context): void
    {
        $total = array_sum($results);
        
        if ($total > 0) {
            $this->info("  📊 Summary for Season {$context['season_id']} - School {$context['school_id']}: {$total} operations");
        } else {
            $this->line("  ℹ️  No operations needed for Season {$context['season_id']} - School {$context['school_id']}");
        }
        
        $this->newLine();
    }
}