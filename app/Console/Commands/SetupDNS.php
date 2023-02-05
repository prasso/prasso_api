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
        // Log::info('dns:setup, '.config('app.aws_route53_zone_id'));
        // get the site name
        $sitename = $this->argument('site');

        $dns_command = file_get_contents(resource_path() . '/templates/setup_dns.txt');
        $dns_command = str_replace('{$sitename}', $sitename, $dns_command);
        $dns_command = str_replace('{$zone_id}', config('app.aws_route53_zone_id'), $dns_command);
        // Log::info("dns:setup, {$dns_command}");
        exec($dns_command, $output);

        // print output from command
        $this->comment( implode( PHP_EOL, $output ) );

        return 0;
    }
}