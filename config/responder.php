<?php
return [

    'imap' => [
        'username' => env('MAIL_FROM_ADDRESS'),
        'password' => env('INBOX_AI_MAIL_PASSWORD'),
        'server' => env('INBOX_AI_MAIL_SERVER')
    ],

    'assistant' => [

        'tags' => 'http://127.0.0.1:11434/api/tags',
        'server' => 'http://127.0.0.1:11434/api/chat',
        'model' => 'llama3:latest',
        'system' => "Act as myself [name], your primary task is to reply to inbox messages. If you are not sure what to reply, summarize the received message, then suggest a potential reply."
    ]
];
