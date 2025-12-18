<?php
return [

    'imap' => [
        'username' => env('MAIL_FROM_ADDRESS', null),
        'password' => env('INBOX_AI_MAIL_PASSWORD', null),
        'server' => env('INBOX_AI_MAIL_SERVER', null),
        'port' => env('INBOX_AI_MAIL_PORT', 993),
    ],

    'classifier' => [
        'model' => 'inboxAI:c1',
        'system' => 'You are inboxAI, a personal AI assistant specialized in email classification. Your task is to read the message resource provided and classify it using the following categories:
        - Junk
        - Trash
        - Archive
        - inbox

        Use the following criteria to decide how to categorize a message:
        - if you think it is a newsletter put it in the Archive
        - if you think it is spam or junk put it in the Junk
        - if you think it is trash put it in the Trash
        - if you think it is important or needs an answer leave it in the Inbox

        Output format:
        You must always return a JSON response with only the following keys: category, action, instructions.

        Description:
        - category: the category you want to use to classify the message,
        - action: true or false.
        - instructions: if action is set to true then specify only one of the following values: generateReply, insertEvent, summarize.

        Instructions details:
        Below you can find a brief description of when to return an instruction or another.
        - generateReply: to be used when to generare a reply for the received email message
        - insertEvent:  to be used to insert a calendar event given the received email message
        - summarize:  to be used to summarize newsletters and long conversations

        Example output:
        {"category": "Archive",  "action": true, "instructions": "summarize"}'
    ],
    'assistant' => [
        'server' => env('OLLAMA_PROXY_SERVER', 'http://127.0.0.1:11434'),
        'tags' => '/api/tags',
        'chat' => '/api/chat',
        'server_api_token' => env('OLLAMA_PROXY_SERVER_API_TOKEN', ''),
        'model' => 'llama3:latest',
        'system' => "Act as myself [name], your primary task is to reply to inbox messages. If you are not sure what to reply, summarize the received message, then suggest a potential reply.",
        'json_formats' => [
            'withEvent' => '[
                    "reply": "The reply for the given message",
                    "event": {
                            "summary": "Google I/O 2015",
                            "description": "A chance to hear more about Googles developer products.",
                            "start": {
                                "dateTime": "2015-05-28T09:00:00-07:00",
                                "timeZone": "America/Los_Angeles"
                            },
                            "end": {
                                "dateTime": "2015-05-28T17:00:00-07:00",
                                "timeZone": "America/Los_Angeles"
                            },
                            "recurrence": {
                                "RRULE": "FREQ=DAILY;COUNT=2"
                            },
                            "attendees": [
                                {"email": "lpage@example.com"},
                                {"email": "sbrin@example.com"}
                            ],
                            "reminders": {
                                "useDefault": false,
                                "overrides": [
                                    {"method": "email", "minutes": 1440},
                                    {"method": "popup", "minutes": 10}
                                ]
                            }
                        }
                    ]'
        ]
    ]
];
