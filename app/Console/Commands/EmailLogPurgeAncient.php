<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EmailLogPurgeAncient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EmailLog:purgeAncient';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old registers in email_log';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //TODO: Check if need
        \DB::delete('DELETE FROM email_log WHERE TIMESTAMPDIFF(DAY, date, CURRENT_DATE) > 30');
    }
}
