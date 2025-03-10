<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\Http;


class TelegramWatcherService {
    protected string $botToken;
    protected string $chatId;
    protected string $apiUrl;

    public function __construct()
    {
        $this->botToken = env("TELEGRAM_BOT_TOKEN");
        // $this->chatId = config('services.telegram.chat_id');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/getUpdates";
    }


    function getUpdates(){

        $botToken = env("TELEGRAM_BOT_TOKEN");
        // $chatId = config('TELEGRAM_CHAT_ID');
        $apiUrl = "https://api.telegram.org/bot{$botToken}/getUpdates";
        dump($apiUrl);
        // Fetch the latest messages from Telegram
        $response = Http::get($apiUrl, [
            'allowed_updates' => ['message']
        ]);
        $updates = $response->json();

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
        // $text = preg_replace('/#(\w+)/', '', $message); // Remove hashtags from text
        return [$message, $hashtags];
    }
}
