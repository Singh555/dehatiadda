<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class giveIncome extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'income:give';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send customers temporary income to their wallet';

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
        // write the code for giving the income on cron 
    }
}
