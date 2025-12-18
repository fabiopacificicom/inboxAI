<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Message;
use Illuminate\Console\Command;

class VerifyAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify accounts and messages seeded data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Database Summary ===');
        $this->info('Total Accounts: ' . Account::count());
        $this->info('Total Messages: ' . Message::count());
        $this->newLine();

        $this->info('=== Account Details ===');

        $accounts = Account::withCount('messages')->get();

        foreach ($accounts as $account) {
            $this->info("📧 {$account->name} ({$account->email})");
            $this->line("   Messages: {$account->messages_count}");
            $this->line("   Active: " . ($account->is_active ? '✅ Yes' : '❌ No'));
            $this->line("   IMAP: {$account->imap_host}:{$account->imap_port}");
            $this->newLine();
        }

        return Command::SUCCESS;
    }
}
