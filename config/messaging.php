<?php


return [
    'help'=>[
        'disclaimer'=>'Reply HELP for help. STOP to cancel',
    ],
    'providers' => [
      
    ],
    'user_model' => App\Models\User::class,
    // Optional: Fully qualified class name of your domain Member model.
    // Preferably implements Prasso\Messaging\Contracts\MemberContact.
    'member_model' => env('MESSAGING_MEMBER_MODEL'),
    'date_format' => env('MESSAGING_DATE_FORMAT', config('app.date_format', 'd/m/Y')),

];
