<?php

namespace App\Console\Commands;

use App\Services\LinnworksApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LinnworksRefreshToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'linnworks:refresh-token {--validate : Only validate the token without forcing refresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh or validate the Linnworks API token';

    /**
     * Execute the console command.
     */
    public function handle(LinnworksApiService $linnworks)
    {
        if ($this->option('validate')) {
            $this->info('Validating Linnworks token...');

            try {
                $result = $linnworks->validateCachedToken();

                if ($result) {
                    $this->info('✓ Token is valid and matches the API response');
                } else {
                    $this->warn('Token was updated during validation');
                }

                $this->info('Current token: '.Cache::get(config('linnworks.cache.session_token_key')));

                return 0;
            } catch (\Exception $exception) {
                Log::channel('lw_auth')->error($exception->getMessage());
                $this->error('Validation failed: '.$exception->getMessage());

                return 1;
            }
        } else {
            $this->info('Forcing token refresh...');

            try {
                $token = $linnworks->refreshToken();
                $this->info('✓ Token refreshed successfully');
                $this->info('New token: '.$token);

                return 0;
            } catch (\Exception $exception) {
                Log::channel('lw_auth')->error($exception->getMessage());
                $this->error('Refresh failed: '.$exception->getMessage());

                return 1;
            }
        }
    }
}
