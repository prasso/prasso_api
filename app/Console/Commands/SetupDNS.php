<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SetupDNS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dns:setup {site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'add a new site to the DNS server';

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
     * @return int
     */
    public function handle()
    {
        $sitename = $this->argument('site');
        $zoneId = config('app.aws_route53_zone_id');

        // Log configuration
        Log::info("DNS setup starting for domain: {$sitename}");
        Log::info("Zone ID configured: " . ($zoneId ? 'YES' : 'NO (MISSING!)'));

        if (!$zoneId) {
            Log::error("AWS Route53 Zone ID not configured. Set app.aws_route53_zone_id in config.");
            $this->error("ERROR: AWS Route53 Zone ID not configured!");
            return 1;
        }

        $dns_command = file_get_contents(resource_path() . '/templates/setup_dns.txt');
        $dns_command = str_replace('{$sitename}', $sitename, $dns_command);
        $dns_command = str_replace('{$zone_id}', $zoneId, $dns_command);

        Log::info("Executing DNS command for: {$sitename}");
        
        $output = [];
        $returnVar = 0;
        exec($dns_command . ' 2>&1', $output, $returnVar);

        $outputText = implode(PHP_EOL, $output);
        
        // Log the full output and return code
        Log::info("DNS command return code: {$returnVar}");
        Log::info("DNS command output: {$outputText}");

        if ($returnVar !== 0) {
            Log::error("DNS setup failed for {$sitename}. Return code: {$returnVar}. Output: {$outputText}");
            $this->error("ERROR: DNS setup failed!");
            $this->error($outputText);
            return $returnVar;
        }

        Log::info("DNS setup completed successfully for: {$sitename}");
        $this->comment("SUCCESS: DNS record created for {$sitename}");
        $this->comment($outputText);

        return 0;
    }
}