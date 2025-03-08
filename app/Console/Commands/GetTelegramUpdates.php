<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
    public function handle()
    {

        $botToken = env("TELEGRAM_BOT_TOKEN");
        $chatId = config('TELEGRAM_CHAT_ID');
        $apiUrl = "https://api.telegram.org/bot{$botToken}/getUpdates";
        // Fetch the latest messages from Telegram
        $response = Http::get($apiUrl, [
            'allowed_updates' => ['message']
        ]);
        $this->info("apiurl: ");
        $this->info( $apiUrl);

        $updates = $response->json();
        $this->info("result");
        $this->info(json_encode($response->dump()));

        if (!isset($updates['result'])) {
            return;
        }

        foreach ($updates['result'] as $update) {
            if (!isset($update['message'])) continue;

            $message = $update['message']['text'] ?? '';
            $messageId = $update['message']['message_id'];
            $createdAt = date('Y-m-d H:i:s', $update['message']['date']);

            // Extract hashtags and content
            [$text, $hashtags] = $this->extractHashtags($message);

            // Store in posts table
            $post = Post::create([
                'content' => $text
            ]);

            // Store hashtags in tags table and associate them with the post
            foreach ($hashtags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName, "user_id" => 1]);
                $post->tags()->attach($tag->id);
            }
        }

    }

    private function extractHashtags(string $message): array
    {
        preg_match_all('/#(\w+)/', $message, $matches);
        $hashtags = $matches[1] ?? [];
        $text = preg_replace('/#(\w+)/', '', $message); // Remove hashtags from text
        return [trim($text), $hashtags];
    }
}
