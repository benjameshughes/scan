<?php

namespace App\Console\Commands;

use App\Services\LinnworksApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class LinnworksRefreshToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'linnworks:refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(LinnworksApiService $linnworks)
    {
        $this->info('Refreshing token');

        try
        {
            $linnworks->refreshToken();
            $this->info('Token refreshed: ' . Cache::get('linnworks.session_token'));
        }
        catch (\Exception $exception)
        {
            Log::channel('lw_auth')->error($exception->getMessage());
            $this->error($exception->getMessage());
        }

        return 'Success';
    }
}
