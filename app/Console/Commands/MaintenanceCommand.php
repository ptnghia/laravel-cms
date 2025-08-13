<?php

namespace App\Console\Commands;

use App\Http\Middleware\MaintenanceMode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance {action : enable, disable, status, progress}
                            {--message= : Maintenance message}
                            {--reason= : Reason for maintenance}
                            {--duration= : Estimated duration}
                            {--retry-after= : Retry after seconds}
                            {--progress= : Progress percentage (0-100)}
                            {--end-time= : End time (Y-m-d H:i:s)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage application maintenance mode';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        return match($action) {
            'enable' => $this->enableMaintenance(),
            'disable' => $this->disableMaintenance(),
            'status' => $this->showStatus(),
            'progress' => $this->updateProgress(),
            default => $this->error("Invalid action: {$action}. Use: enable, disable, status, progress")
        };
    }

    /**
     * Enable maintenance mode
     */
    protected function enableMaintenance(): int
    {
        $data = [];

        if ($message = $this->option('message')) {
            $data['message'] = $message;
        }

        if ($reason = $this->option('reason')) {
            $data['reason'] = $reason;
        }

        if ($duration = $this->option('duration')) {
            $data['estimated_duration'] = $duration;
        }

        if ($retryAfter = $this->option('retry-after')) {
            $data['retry_after'] = (int) $retryAfter;
        }

        if ($endTime = $this->option('end-time')) {
            try {
                $data['end_time'] = \Carbon\Carbon::parse($endTime)->toISOString();
            } catch (\Exception $e) {
                $this->error("Invalid end-time format. Use: Y-m-d H:i:s");
                return 1;
            }
        }

        $data['start_time'] = now()->toISOString();

        MaintenanceMode::enable($data);

        $this->info('✅ Maintenance mode enabled successfully!');
        $this->line('');
        $this->showMaintenanceData($data);

        return 0;
    }

    /**
     * Disable maintenance mode
     */
    protected function disableMaintenance(): int
    {
        if (!MaintenanceMode::isActive()) {
            $this->warn('⚠️  Maintenance mode is not currently active.');
            return 0;
        }

        MaintenanceMode::disable();

        $this->info('✅ Maintenance mode disabled successfully!');
        $this->info('🚀 Application is now accessible to all users.');

        return 0;
    }

    /**
     * Show maintenance status
     */
    protected function showStatus(): int
    {
        $isActive = MaintenanceMode::isActive();

        if ($isActive) {
            $this->error('🔧 Maintenance mode is ACTIVE');
            $data = Cache::get('maintenance_mode_data', []);
            $this->showMaintenanceData($data);
        } else {
            $this->info('✅ Maintenance mode is INACTIVE');
            $this->info('🚀 Application is accessible to all users.');
        }

        return 0;
    }

    /**
     * Update maintenance progress
     */
    protected function updateProgress(): int
    {
        if (!MaintenanceMode::isActive()) {
            $this->error('❌ Maintenance mode is not active. Cannot update progress.');
            return 1;
        }

        $progress = $this->option('progress');
        if ($progress === null) {
            $progress = $this->ask('Enter progress percentage (0-100)');
        }

        $progress = (int) $progress;
        if ($progress < 0 || $progress > 100) {
            $this->error('❌ Progress must be between 0 and 100.');
            return 1;
        }

        $message = $this->option('message');
        if (!$message) {
            $message = $this->ask('Enter progress message (optional)');
        }

        MaintenanceMode::updateProgress($progress, $message);

        $this->info("✅ Progress updated to {$progress}%");
        if ($message) {
            $this->info("📝 Message: {$message}");
        }

        return 0;
    }

    /**
     * Display maintenance data in a formatted table
     */
    protected function showMaintenanceData(array $data): void
    {
        $this->line('');
        $this->info('📋 Maintenance Details:');

        $tableData = [];

        if (isset($data['message'])) {
            $tableData[] = ['Message', $data['message']];
        }

        if (isset($data['reason'])) {
            $tableData[] = ['Reason', $data['reason']];
        }

        if (isset($data['estimated_duration'])) {
            $tableData[] = ['Duration', $data['estimated_duration']];
        }

        if (isset($data['start_time'])) {
            $startTime = \Carbon\Carbon::parse($data['start_time'])->format('d/m/Y H:i:s');
            $tableData[] = ['Start Time', $startTime];
        }

        if (isset($data['end_time'])) {
            $endTime = \Carbon\Carbon::parse($data['end_time'])->format('d/m/Y H:i:s');
            $tableData[] = ['End Time', $endTime];
        }

        if (isset($data['progress'])) {
            $tableData[] = ['Progress', $data['progress'] . '%'];
        }

        if (isset($data['retry_after'])) {
            $tableData[] = ['Retry After', $data['retry_after'] . ' seconds'];
        }

        if (isset($data['contact_email'])) {
            $tableData[] = ['Contact', $data['contact_email']];
        }

        if (!empty($tableData)) {
            $this->table(['Property', 'Value'], $tableData);
        }
    }
}
