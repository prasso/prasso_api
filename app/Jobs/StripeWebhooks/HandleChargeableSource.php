<?php

namespace App\Jobs\StripeWebhooks;


use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use app\Services\UserService;

class HandleChargeableSource implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function handle(Request $request)
    {
        // do your work here
        Log::info('in HandleChargeableSource: '.json_encode($this->webhookCall->payload));
      
        // you can access the payload of the webhook call with `$this->webhookCall->payload`
        $stripecharge = $this->webhookCall->payload;
        if ($stripecharge != null)
       { 
           $json = json_decode($stripecharge);
           if ($json->type == 'charge.succeeded')
           {
                $this->userService->subscribe($json);
           }
        }
    }
 
}
