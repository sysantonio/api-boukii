<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EncryptString extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encrypt:string {string}';
    protected $description = 'Encrypt a string';

    public function handle()
    {
        $string = $this->argument('string');
        $this->info(encrypt($string));
    }
}
