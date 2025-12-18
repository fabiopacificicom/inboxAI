<?php

namespace App\Traits;

use App\Models\Account;
use App\Models\Message;
use App\Models\Reply;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

trait HasToolAccess
{
    /**
     * Define available tools for AI function calling
     */
    protected function getTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_emails',
                    'description' => 'Search emails across all accounts or specific account by keyword, sender, subject, or content',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Search term to find in subject, content, or sender'
                            ],
                            'account_id' => [
                                'type' => 'integer',
                                'description' => 'Optional: specific account ID to search in'
                            ],
                            'date_range' => [
                                'type' => 'string',
                                'description' => 'Optional: today/week/month/year',
                                'enum' => ['today', 'week', 'month', 'year']
                            ],
                            'folder' => [
                                'type' => 'string',
                                'description' => 'Optional: INBOX/INBOX.Archive/INBOX.Trash'
                            ],
                        ],
                        'required' => ['query']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_critical_emails',
                    'description' => 'Find emails marked as important, flagged, or containing urgent keywords',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'account_ids' => [
                                'type' => 'array',
                                'description' => 'Optional: specific account IDs to check',
                                'items' => ['type' => 'integer']
                            ],
                            'days' => [
                                'type' => 'integer',
                                'description' => 'Look back N days (default: 7)',
                                'default' => 7
                            ]
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'summarize_account_activity',
                    'description' => 'Get summary of email activity on account(s) including message counts, senders, and topics',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'account_ids' => [
                                'type' => 'array',
                                'description' => 'Optional: specific account IDs',
                                'items' => ['type' => 'integer']
                            ],
                            'period' => [
                                'type' => 'string',
                                'description' => 'day/week/month',
                                'enum' => ['day', 'week', 'month']
                            ]
                        ],
                        'required' => ['period']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'compose_reply',
                    'description' => 'Draft a reply to a specific email with specified tone',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'message_id' => [
                                'type' => 'integer',
                                'description' => 'Database ID of the message to reply to'
                            ],
                            'tone' => [
                                'type' => 'string',
                                'description' => 'formal/casual/brief (default: formal)',
                                'enum' => ['formal', 'casual', 'brief'],
                                'default' => 'formal'
                            ],
                            'key_points' => [
                                'type' => 'array',
                                'description' => 'Optional: key points to include in reply',
                                'items' => ['type' => 'string']
                            ]
                        ],
                        'required' => ['message_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_account_stats',
                    'description' => 'Get statistics for email accounts including message counts, storage usage',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'account_ids' => [
                                'type' => 'array',
                                'description' => 'Optional: specific account IDs',
                                'items' => ['type' => 'integer']
                            ]
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'find_by_sender',
                    'description' => 'Find all emails from a specific sender or email address',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'sender_email' => [
                                'type' => 'string',
                                'description' => 'Email address or partial email to search for'
                            ],
                            'account_ids' => [
                                'type' => 'array',
                                'description' => 'Optional: specific account IDs',
                                'items' => ['type' => 'integer']
                            ],
                            'days' => [
                                'type' => 'integer',
                                'description' => 'Look back N days (default: 30)',
                                'default' => 30
                            ]
                        ],
                        'required' => ['sender_email']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'archive_messages',
                    'description' => 'Move messages to INBOX.Archive folder',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'message_ids' => [
                                'type' => 'array',
                                'description' => 'Database IDs of messages to archive',
                                'items' => ['type' => 'integer']
                            ]
                        ],
                        'required' => ['message_ids']
                    ]
                ]
            ],
        ];
    }

    /**
     * Execute a tool call and return results
     */
    protected function executeToolCall(string $toolName, array $arguments): array
    {
        Log::info('🔧 Tool execution', ['tool' => $toolName, 'args' => $arguments]);

        return match ($toolName) {
            'search_emails' => $this->searchEmails($arguments),
            'get_critical_emails' => $this->getCriticalEmails($arguments),
            'summarize_account_activity' => $this->summarizeActivity($arguments),
            'compose_reply' => $this->composeReply($arguments),
            'archive_messages' => $this->archiveMessages($arguments),
            'get_account_stats' => $this->getAccountStats($arguments),
            'find_by_sender' => $this->findBySender($arguments),
            default => [
                'success' => false,
                'error' => "Unknown tool: {$toolName}"
            ]
        };
    }

    /**
     * Tool: Search emails
     */
    private function searchEmails(array $args): array
    {
        $query = Message::with('account:id,name,email');

        // Filter by account if specified
        if (isset($args['account_id'])) {
            $query->where('account_id', $args['account_id']);
        } else {
            // Only search active accounts
            $query->whereHas('account', fn($q) => $q->where('is_active', true));
        }

        // Search term
        $searchTerm = $args['query'];
        $query->where(function ($q) use ($searchTerm) {
            $q->where('subject', 'LIKE', "%{$searchTerm}%")
                ->orWhere('content', 'LIKE', "%{$searchTerm}%")
                ->orWhere('from', 'LIKE', "%{$searchTerm}%");
        });

        // Date range filter
        if (isset($args['date_range'])) {
            $date = match ($args['date_range']) {
                'today' => Carbon::today(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
            };
            $query->where('date', '>=', $date);
        }

        // Folder filter
        if (isset($args['folder'])) {
            $query->where('mailbox_folder', $args['folder']);
        }

        $messages = $query->orderByDesc('date')->limit(20)->get();

        return [
            'success' => true,
            'count' => $messages->count(),
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'subject' => $m->subject,
                'from' => $m->from,
                'date' => $m->date,
                'account' => $m->account?->name,
                'snippet' => \Str::limit($m->content, 150)
            ])
        ];
    }

    /**
     * Tool: Get critical emails
     */
    private function getCriticalEmails(array $args): array
    {
        $days = $args['days'] ?? 7;
        $query = Message::with('account:id,name,email')
            ->where('date', '>=', Carbon::now()->subDays($days));

        // Filter by accounts
        if (isset($args['account_ids'])) {
            $query->whereIn('account_id', $args['account_ids']);
        } else {
            $query->whereHas('account', fn($q) => $q->where('is_active', true));
        }

        // Get flagged messages or messages with urgent keywords
        $query->where(function ($q) {
            $q->where('is_flagged', true)
                ->orWhere('subject', 'LIKE', '%urgent%')
                ->orWhere('subject', 'LIKE', '%important%')
                ->orWhere('subject', 'LIKE', '%ASAP%');
        });

        $messages = $query->orderByDesc('date')->get();

        return [
            'success' => true,
            'count' => $messages->count(),
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'subject' => $m->subject,
                'from' => $m->from,
                'date' => $m->date,
                'account' => $m->account?->name,
                'is_flagged' => $m->is_flagged
            ])
        ];
    }

    /**
     * Tool: Summarize account activity
     */
    private function summarizeActivity(array $args): array
    {
        $period = $args['period'];
        $date = match ($period) {
            'day' => Carbon::today(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
        };

        $query = Message::where('date', '>=', $date);

        if (isset($args['account_ids'])) {
            $query->whereIn('account_id', $args['account_ids']);
        } else {
            $query->whereHas('account', fn($q) => $q->where('is_active', true));
        }

        $messages = $query->get();

        // Get top senders
        $topSenders = $messages->groupBy('from')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->take(5);

        return [
            'success' => true,
            'period' => $period,
            'total_messages' => $messages->count(),
            'unread_count' => $messages->where('is_seen', false)->count(),
            'flagged_count' => $messages->where('is_flagged', true)->count(),
            'top_senders' => $topSenders->map(fn($count, $sender) => [
                'sender' => $sender,
                'count' => $count
            ])->values()
        ];
    }

    /**
     * Tool: Compose reply
     */
    private function composeReply(array $args): array
    {
        $message = Message::find($args['message_id']);

        if (!$message) {
            return [
                'success' => false,
                'error' => 'Message not found'
            ];
        }

        // Check if reply already exists
        $existingReply = Reply::where('message_id', $message->id)->first();

        if ($existingReply) {
            return [
                'success' => true,
                'reply_id' => $existingReply->id,
                'content' => $existingReply->response_content,
                'note' => 'Reply already exists'
            ];
        }

        // This would normally trigger AI reply generation
        // For now, return a placeholder
        return [
            'success' => true,
            'message' => "Reply composition for message #{$message->id} would be triggered here",
            'original_subject' => $message->subject,
            'tone' => $args['tone'] ?? 'formal',
            'note' => 'Use the existing AI reply generation system to create the actual reply'
        ];
    }

    /**
     * Tool: Get account statistics
     */
    private function getAccountStats(array $args): array
    {
        $query = Account::with('messages');

        if (isset($args['account_ids'])) {
            $query->whereIn('id', $args['account_ids']);
        } else {
            $query->where('is_active', true);
        }

        $accounts = $query->get();

        return [
            'success' => true,
            'accounts' => $accounts->map(fn($account) => [
                'id' => $account->id,
                'name' => $account->name,
                'email' => $account->email,
                'is_active' => $account->is_active,
                'total_messages' => $account->messages()->count(),
                'unread_messages' => $account->messages()->where('is_seen', false)->count(),
                'today_messages' => $account->messages()->where('date', '>=', Carbon::today())->count(),
            ])
        ];
    }

    /**
     * Tool: Find emails by sender
     */
    private function findBySender(array $args): array
    {
        $senderEmail = $args['sender_email'];
        $days = $args['days'] ?? 30;

        $query = Message::with('account:id,name,email')
            ->where('date', '>=', Carbon::now()->subDays($days))
            ->where(function ($q) use ($senderEmail) {
                $q->where('from', 'LIKE', "%{$senderEmail}%")
                    ->orWhere('sender', 'LIKE', "%{$senderEmail}%");
            });

        if (isset($args['account_ids'])) {
            $query->whereIn('account_id', $args['account_ids']);
        }

        $messages = $query->orderByDesc('date')->get();

        return [
            'success' => true,
            'sender' => $senderEmail,
            'count' => $messages->count(),
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'subject' => $m->subject,
                'from' => $m->from,
                'date' => $m->date,
                'account' => $m->account?->name
            ])
        ];
    }

    /**
     * Tool: Archive messages
     */
    private function archiveMessages(array $args): array
    {
        $messageIds = $args['message_ids'];
        $messages = Message::whereIn('id', $messageIds)->get();

        if ($messages->isEmpty()) {
            return [
                'success' => false,
                'error' => 'No messages found'
            ];
        }

        // Update mailbox folder to archive
        $updated = Message::whereIn('id', $messageIds)
            ->update(['mailbox_folder' => 'INBOX.Archive']);

        return [
            'success' => true,
            'archived_count' => $updated,
            'message' => "{$updated} messages moved to archive"
        ];
    }
}
