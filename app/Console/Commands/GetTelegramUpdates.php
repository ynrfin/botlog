<?php

namespace App\Console\Commands;

use App\Services\TelegramWatcherService;
use Illuminate\Console\Command;

class GetTelegramUpdates extends Command
{
    protected string $botToken;
    protected string $chatId;
    protected string $apiUrl;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tele:getUpdates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get update from Telegram group then append it to DB';

    /**
     * Execute the console command.
     */
    public function handle(TelegramWatcherService $tws)
    {
        $tws->getUpdates();
    }
}
