<?php

namespace App\Providers;

use Aws\S3\S3Client;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class AwsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }


    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configure AWS SDK logging
        $this->app->bind('aws.s3', function($app) {
            $config = [
                'version' => 'latest',
                'region' => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                '@http' => [
                    'debug' => false
                ],
            ];

            // Create S3 client with debug logging
            $client = new S3Client($config);
            
            // Log AWS requests and responses
            $client->getHandlerList()->appendSign(
                function (callable $handler) {
                    return function (
                        \Aws\CommandInterface $command,
                        \Psr\Http\Message\RequestInterface $request = null
                    ) use ($handler) {
                        $start = microtime(true);
                        
                        Log::info('AWS Request', [
                            'command' => $command->getName(),
                            'params' => $command->toArray(),
                            'uri' => (string) $request->getUri(),
                            'headers' => $request->getHeaders(),
                        ]);

                        return $handler($command, $request)->then(
                            function ($response) use ($start, $command) {
                                $time = round((microtime(true) - $start) * 1000, 2);
                                
                                Log::info('AWS Response', [
                                    'command' => $command->getName(),
                                    'status' => $response['@metadata']['statusCode'] ?? null,
                                    'time_ms' => $time,
                                    'headers' => $response['@metadata']['headers'] ?? [],
                                    'effective_uri' => $response['@metadata']['effectiveUri'] ?? null,
                                ]);
                                
                                return $response;
                            },
                            function ($reason) use ($command) {
                                Log::error('AWS Error', [
                                    'command' => $command->getName(),
                                    'error' => $reason->getMessage(),
                                    'code' => $reason->getCode(),
                                    'trace' => $reason->getTraceAsString(),
                                ]);
                                
                                return new \GuzzleHttp\Promise\RejectedPromise($reason);
                            }
                        );
                    };
                },
                'debug'
            );

            return $client;
        });
    }
}
