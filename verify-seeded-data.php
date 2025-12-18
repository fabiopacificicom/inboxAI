<?php

use App\Models\Account;
use App\Models\Message;

echo "=== Database Summary ===" . PHP_EOL;
echo "Total Accounts: " . Account::count() . PHP_EOL;
echo "Total Messages: " . Message::count() . PHP_EOL;
echo PHP_EOL;

echo "=== Account Details ===" . PHP_EOL;
foreach (Account::with('messages')->get() as $account) {
    echo "📧 {$account->name} ({$account->email})" . PHP_EOL;
    echo "   Messages: {$account->messages->count()}" . PHP_EOL;
    echo "   Active: " . ($account->is_active ? '✅ Yes' : '❌ No') . PHP_EOL;
    echo "   IMAP: {$account->imap_host}:{$account->imap_port}" . PHP_EOL;
    echo PHP_EOL;
}
