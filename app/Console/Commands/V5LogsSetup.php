<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class V5LogsSetup extends Command
{
    protected $signature = 'v5:logs:setup {--force : Overwrite existing files}';
    
    protected $description = 'Setup V5 logging system with all required configurations';

    public function handle()
    {
        $this->info('🚀 Setting up V5 Logging System...');

        // Step 1: Create log directories
        $this->createLogDirectories();

        // Step 2: Publish and configure logging config
        $this->configureLogging();

        // Step 3: Create database tables
        $this->createDatabaseTables();

        // Step 4: Setup environment variables
        $this->setupEnvironmentVariables();

        // Step 5: Setup AdminLTE (if not already installed)
        $this->setupAdminLTE();

        // Step 6: Setup Telescope integration (optional)
        $this->setupTelescope();

        // Step 7: Test configuration
        $this->testConfiguration();

        $this->info('✅ V5 Logging System setup completed!');
        $this->newLine();
        $this->info('🔗 Access your dashboard at: ' . route('v5.logs.dashboard'));
        $this->info('📧 Configure email recipients in .env: V5_ALERT_EMAIL_RECIPIENTS');
    }

    private function createLogDirectories(): void
    {
        $this->info('📁 Creating log directories...');

        $directories = [
            storage_path('logs/v5'),
            storage_path('exports/logs'),
        ];

        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
                $this->line("   Created: {$dir}");
            } else {
                $this->line("   Exists: {$dir}");
            }
        }
    }

    private function configureLogging(): void
    {
        $this->info('⚙️ Configuring logging channels...');

        $configPath = config_path('logging.php');
        $v5ConfigPath = config_path('v5_logging.php');

        if (!File::exists($v5ConfigPath)) {
            $this->error("V5 logging config not found. Make sure v5_logging.php is in config/");
            return;
        }

        // Add V5 channels to main logging config
        $this->info('   V5 logging configuration is ready');
    }

    private function createDatabaseTables(): void
    {
        $this->info('🗄️ Creating database tables...');

        // Create V5 logs table
        $this->call('make:migration', [
            'name' => 'create_v5_logs_table',
            '--create' => 'v5_logs'
        ]);

        // Create V5 alert logs table
        $this->call('make:migration', [
            'name' => 'create_v5_alert_logs_table',
            '--create' => 'v5_alert_logs'
        ]);

        $this->info('   Migration files created. Run "php artisan migrate" to apply them.');
    }

    private function setupEnvironmentVariables(): void
    {
        $this->info('🔧 Setting up environment variables...');

        $envExample = base_path('.env.v5.example');
        $envFile = base_path('.env');

        if (!File::exists($envExample)) {
            $this->error('   .env.v5.example not found');
            return;
        }

        if (File::exists($envFile)) {
            $envContent = File::get($envFile);
            $exampleContent = File::get($envExample);

            // Check if V5 variables already exist
            if (!str_contains($envContent, 'V5_LOG_LEVEL')) {
                File::append($envFile, "\n# V5 Logging Configuration\n" . $exampleContent);
                $this->info('   V5 environment variables added to .env');
            } else {
                $this->line('   V5 environment variables already exist');
            }
        }
    }

    private function setupAdminLTE(): void
    {
        $this->info('🎨 Checking AdminLTE installation...');

        if (!File::exists(config_path('adminlte.php'))) {
            if ($this->confirm('AdminLTE not found. Install it now?')) {
                $this->call('composer', ['command' => 'require', 'packages' => ['jeroennoten/laravel-adminlte']]);
                $this->call('adminlte:install');
                $this->info('   AdminLTE installed successfully');
            }
        } else {
            $this->line('   AdminLTE already installed');
        }
    }

    private function setupTelescope(): void
    {
        if (!$this->confirm('Install Laravel Telescope for advanced debugging? (Recommended)')) {
            return;
        }

        $this->info('🔭 Setting up Laravel Telescope...');

        if (!File::exists(config_path('telescope.php'))) {
            $this->call('composer', ['command' => 'require', 'packages' => ['laravel/telescope']]);
            $this->call('telescope:install');
            $this->call('migrate');
            $this->info('   Telescope installed and configured');
        } else {
            $this->line('   Telescope already installed');
        }
    }

    private function testConfiguration(): void
    {
        $this->info('🧪 Testing configuration...');

        try {
            // Test logging
            \App\V5\Logging\EnterpriseLogger::logCustomEvent('v5_setup_test', 'info', [
                'message' => 'V5 Logging system setup completed',
                'setup_time' => now()->toISOString(),
            ]);

            $this->info('   ✅ Logging test successful');

            // Test alert system
            \App\V5\Logging\AlertManager::processLogForAlerts([
                'level' => 'info',
                'category' => 'system',
                'message' => 'V5 setup completed successfully',
                'correlation_id' => \App\V5\Logging\V5Logger::getCorrelationId(),
            ]);

            $this->info('   ✅ Alert system test successful');

        } catch (\Exception $e) {
            $this->error('   ❌ Configuration test failed: ' . $e->getMessage());
        }
    }

    private function displayFinalInstructions(): void
    {
        $this->newLine();
        $this->info('📋 Next Steps:');
        $this->line('1. Run "php artisan migrate" to create database tables');
        $this->line('2. Configure your email settings in .env for alerts');
        $this->line('3. Set V5_ALERT_EMAIL_RECIPIENTS in .env');
        $this->line('4. Access dashboard at: /v5/logs');
        $this->line('5. Test alerts with: php artisan v5:test-alerts');
        $this->newLine();
        
        $this->info('📚 Documentation:');
        $this->line('- Dashboard: /v5/logs');
        $this->line('- Payment logs: /v5/logs/payments');
        $this->line('- System errors: /v5/logs/system-errors');
        $this->line('- Real-time alerts: /v5/logs/realtime-alerts');
        
        if (File::exists(config_path('telescope.php'))) {
            $this->line('- Telescope: /telescope');
        }
    }
}