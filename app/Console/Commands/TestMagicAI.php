<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestMagicAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'magic-ai:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the connection to OpenRouter AI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = core()->getConfigData('general.magic_ai.settings.api_key');
        $model = core()->getConfigData('general.magic_ai.settings.model');

        $this->info("Testing Magic AI Connection...");
        $this->info("Model: " . ($model ?: 'NOT SET'));
        $this->info("API Key: " . ($apiKey ? substr($apiKey, 0, 10) . '...' : 'NOT SET'));

        if (!$apiKey) {
            $this->error('API Key is missing in configuration.');
            return;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => $model,
                        'max_tokens' => 10,
                        'messages' => [
                            ['role' => 'user', 'content' => 'Say "Connection Verified"']
                        ],
                    ]);

            if ($response->successful()) {
                $this->info('Connection Successful!');
                $this->line('Response: ' . $response->body());
            } else {
                $this->error('Connection Failed!');
                $this->error('Status: ' . $response->status());
                $this->error('Body: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
        }
    }
}
