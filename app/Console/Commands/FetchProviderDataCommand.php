<?php

namespace App\Console\Commands;

use App\Jobs\ProcessProviderDataJob;
use App\Services\ApiProviders\JsonProviderService;
use App\Services\ApiProviders\XmlProviderService;
use App\Parsers\JsonParser;
use App\Parsers\XmlParser;
use Illuminate\Console\Command;

class FetchProviderDataCommand extends Command
{
    protected $signature = 'app:fetch-provider-data';
    protected $description = 'Fetch data from providers and save to DB';

    public function handle()
    {
        try {
            ProcessProviderDataJob::dispatchSync(new JsonProviderService(), new JsonParser(), 'Provider JSON');
        } catch (\Throwable $e) {
            $this->error("JSON Provider job failed: " . $e->getMessage());
        }

        try {
            ProcessProviderDataJob::dispatchSync(new XmlProviderService(), new XmlParser(), 'Provider XML');
        } catch (\Throwable $e) {
            $this->error("XML Provider job failed: " . $e->getMessage());
        }

        $this->info('Jobs dispatched (or attempted) successfully.');
    }
}
