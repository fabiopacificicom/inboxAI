<?php
return [

    'imap' => [
        'username' => env('MAIL_FROM_ADDRESS'),
        'password' => env('INBOX_AI_MAIL_PASSWORD'),
        'server' => env('INBOX_AI_MAIL_SERVER')
    ],

    'classifier' => [
        'model'=> 'inboxAI:c1',
        'system' => '
        You are inboxAI. You are a personal AI assitant specialized in email messages classification.
            Classify the given resource message using one of the following categories:
            - spam
            - urgent
            - newsletter
            - inbox

        Output:
        You must return a JSON object with the following keys:
        - category: the category you want to use to classify the message,
        - action: true or false.
        - instructions: if action is set to true then specify one of this instructions:
        - generateReply (to be used when to generare a reply for the received email message)
        - insertEvent ( to be used to insert a calendar event given the received email message)
        - summarize ( to be used to summarize newsletters and/or long conversations)'
    ],
    'assistant' => [

        'tags' => 'http://127.0.0.1:11434/api/tags',
        'server' => 'http://127.0.0.1:11434/api/chat',
        'model' => 'llama3:latest',
        'system' => "Act as myself [name], your primary task is to reply to inbox messages. If you are not sure what to reply, summarize the received message, then suggest a potential reply."
    ]
];
