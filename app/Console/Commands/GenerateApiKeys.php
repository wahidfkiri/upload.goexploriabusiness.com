<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateApiKeys extends Command
{
    protected $signature = 'cdn:generate-keys';
    protected $description = 'Generate API keys for CDN';

    public function handle()
    {
        $apiKey = Str::random(40);
        $apiSecret = Str::random(60);
        
        $this->info('=== CDN API Keys ===');
        $this->info("API_KEY: {$apiKey}");
        $this->info("API_SECRET: {$apiSecret}");
        $this->info('====================');
        
        // Option pour ajouter automatiquement au .env
        if ($this->confirm('Ajouter ces clés au fichier .env ?')) {
            $this->addToEnv($apiKey, $apiSecret);
            $this->info('Clés ajoutées avec succès !');
        }
    }
    
    private function addToEnv($apiKey, $apiSecret)
    {
        $envFile = base_path('.env');
        
        // Supprimer les anciennes valeurs
        $content = file_get_contents($envFile);
        $content = preg_replace('/API_KEY=.*/', '', $content);
        $content = preg_replace('/API_SECRET=.*/', '', $content);
        
        // Ajouter les nouvelles valeurs
        $content .= "\nAPI_KEY={$apiKey}\n";
        $content .= "API_SECRET={$apiSecret}\n";
        
        file_put_contents($envFile, $content);
    }
}