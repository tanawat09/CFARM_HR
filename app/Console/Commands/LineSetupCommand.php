<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class LineSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'line:setup {secret} {token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup LINE Channel Secret and Access Token in the database settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $secret = $this->argument('secret');
        $token = $this->argument('token');

        Setting::updateOrCreate(
            ['key' => 'LINE_CHANNEL_SECRET'],
            ['value' => $secret, 'group' => 'line']
        );

        Setting::updateOrCreate(
            ['key' => 'LINE_CHANNEL_ACCESS_TOKEN'],
            ['value' => $token, 'group' => 'line']
        );

        $this->info('LINE API credentials have been successfully updated in the database!');
    }
}
