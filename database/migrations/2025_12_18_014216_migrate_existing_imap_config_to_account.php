<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Account;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates an Account record from the existing IMAP configuration
     * (stored in config/responder.php and .env) and links all existing messages
     * to this account.
     */
    public function up(): void
    {
        $imapConfig = config('responder.imap');

        // Only proceed if IMAP config exists
        if (empty($imapConfig['username']) || empty($imapConfig['server'])) {
            Log::info('⏭️ No existing IMAP config found, skipping migration');
            return;
        }

        // Get the first user (assuming single-user app initially)
        $user = User::first();

        if (!$user) {
            Log::warning('⚠️ No users found, cannot migrate IMAP config to account');
            return;
        }

        // Check if account already exists for this email
        $existingAccount = Account::where('email', $imapConfig['username'])
            ->where('user_id', $user->id)
            ->first();

        if ($existingAccount) {
            Log::info('✅ Account already exists for ' . $imapConfig['username']);
            $account = $existingAccount;
        } else {
            // Create account from existing IMAP config
            $account = Account::create([
                'user_id' => $user->id,
                'name' => 'Primary Account',
                'email' => $imapConfig['username'],
                'imap_host' => $imapConfig['server'],
                'imap_port' => $imapConfig['port'] ?? 993,
                'imap_encryption' => 'ssl',
                'imap_password' => $imapConfig['password'], // Will be encrypted by model accessor
                'smtp_host' => env('MAIL_HOST'),
                'smtp_port' => env('MAIL_PORT', 587),
                'smtp_password' => env('MAIL_PASSWORD'),
                'is_active' => true,
            ]);

            Log::info('✅ Created default account from existing IMAP config', [
                'account_id' => $account->id,
                'email' => $account->email
            ]);
        }

        // Link all messages with null account_id to this account
        $messagesUpdated = Message::whereNull('account_id')
            ->update(['account_id' => $account->id]);

        Log::info('✅ Linked existing messages to default account', [
            'messages_updated' => $messagesUpdated,
            'account_id' => $account->id
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to delete the account or unlink messages
        // as this would cause data loss. This migration is one-way.
        Log::info('⏭️ Skipping down migration - account data preserved');
    }
};
