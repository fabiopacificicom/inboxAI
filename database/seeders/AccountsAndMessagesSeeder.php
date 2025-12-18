<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class AccountsAndMessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates 2 additional fake accounts with 15 messages each
     * to test unified inbox and account switching functionality.
     */
    public function run(): void
    {
        // Get the first user (should exist from migration)
        $user = User::first();

        if (!$user) {
            $this->command->error('No user found. Please create a user first.');
            return;
        }

        $this->command->info("Creating fake accounts for user: {$user->email}");

        // Create 2 additional fake accounts (Primary Account already exists with ID 1)
        $accounts = [
            [
                'name' => 'Work Account',
                'email' => 'work@company.com',
                'imap_host' => 'imap.company.com',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'smtp_host' => 'smtp.company.com',
                'smtp_port' => 587,
                'is_active' => true,
            ],
            [
                'name' => 'Personal Gmail',
                'email' => 'personal@gmail.com',
                'imap_host' => 'imap.gmail.com',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'is_active' => true,
            ],
            [
                'name' => 'Newsletter Account',
                'email' => 'newsletters@outlook.com',
                'imap_host' => 'imap.outlook.com',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'smtp_host' => 'smtp.outlook.com',
                'smtp_port' => 587,
                'is_active' => false, // Inactive account for testing
            ],
        ];

        foreach ($accounts as $accountData) {
            // Use fake password for demo accounts
            $accountData['user_id'] = $user->id;
            $accountData['imap_password'] = 'demo-password-' . fake()->uuid();
            $accountData['smtp_password'] = 'demo-password-' . fake()->uuid();

            $account = Account::create($accountData);

            $this->command->info("Created account: {$account->name} ({$account->email})");

            // Create 15 messages for each account
            $this->createMessagesForAccount($account);
        }

        $this->command->info('✅ Seeding complete! Created 3 accounts with 45 messages total.');
    }

    /**
     * Create fake messages for the given account
     */
    private function createMessagesForAccount(Account $account): void
    {
        $messageCount = 15;

        // Different email categories for variety
        $categories = [
            'work' => [
                'subjects' => [
                    'Meeting scheduled for tomorrow',
                    'Project update required',
                    'Team standup notes',
                    'Code review request',
                    'Deployment notification',
                ],
                'senders' => [
                    'manager@company.com',
                    'colleague@company.com',
                    'devops@company.com',
                    'hr@company.com',
                ],
            ],
            'personal' => [
                'subjects' => [
                    'Dinner plans this weekend?',
                    'Check out this article',
                    'Happy birthday!',
                    'Vacation photos',
                    'Book club meeting',
                ],
                'senders' => [
                    'friend@gmail.com',
                    'family@yahoo.com',
                    'neighbor@hotmail.com',
                ],
            ],
            'newsletter' => [
                'subjects' => [
                    '[Newsletter] Top 10 Laravel tips',
                    'Weekly digest: AI developments',
                    'Your daily dose of tech news',
                    '[Promo] 50% off sale ends today',
                    'New features in our latest release',
                ],
                'senders' => [
                    'newsletter@laravel-news.com',
                    'digest@techdaily.com',
                    'noreply@promotions.com',
                    'updates@saas-company.com',
                ],
            ],
        ];

        // Choose category based on account name
        $categoryKey = match (true) {
            str_contains(strtolower($account->name), 'work') => 'work',
            str_contains(strtolower($account->name), 'newsletter') => 'newsletter',
            default => 'personal',
        };

        $category = $categories[$categoryKey];

        for ($i = 0; $i < $messageCount; $i++) {
            $subject = fake()->randomElement($category['subjects']);
            $sender = fake()->randomElement($category['senders']);
            $from = fake()->name() . " <{$sender}>";

            // Generate realistic email content
            $content = $this->generateEmailContent($subject, $categoryKey);

            // Random date within last 30 days
            $date = now()->subDays(rand(0, 30))->format('Y-m-d H:i:s');

            Message::create([
                'account_id' => $account->id,
                'message_identifier' => 'FAKE-' . fake()->uuid(),
                'subject' => $subject,
                'from' => $from,
                'sender' => $sender,
                'reply_to_addresses' => [$sender],
                'date' => $date,
                'content' => $content,
                'is_seen' => fake()->boolean(70), // 70% chance of being seen
                'is_answered' => fake()->boolean(30), // 30% chance of being answered
                'is_recent' => fake()->boolean(20), // 20% chance of being recent
                'is_flagged' => fake()->boolean(10), // 10% chance of being flagged
                'is_deleted' => false,
                'is_draft' => false,
                'mailbox_folder' => fake()->randomElement([
                    'INBOX',
                    'INBOX.Archive',
                    'INBOX.Important',
                ]),
            ]);
        }

        $this->command->info("  → Created {$messageCount} messages for {$account->name}");
    }

    /**
     * Generate realistic email content based on category
     */
    private function generateEmailContent(string $subject, string $category): string
    {
        $templates = [
            'work' => [
                "Hi team,\n\n{$subject}\n\nPlease review the attached documents and provide your feedback by EOD.\n\nBest regards,\n" . fake()->name(),
                "Hello,\n\nRegarding {$subject}, I wanted to follow up on our previous discussion.\n\nLet me know your thoughts.\n\nThanks,\n" . fake()->name(),
                "Team,\n\nQuick update on {$subject}.\n\nEverything is on track and we should be ready for the deadline.\n\nCheers,\n" . fake()->name(),
            ],
            'personal' => [
                "Hey!\n\n{$subject}\n\nLet me know what you think!\n\n" . fake()->name(),
                "Hi there,\n\nJust wanted to reach out about {$subject}.\n\nHope you're doing well!\n\n" . fake()->name(),
                "Hello friend,\n\nThinking of you! {$subject}\n\nTalk soon!\n" . fake()->name(),
            ],
            'newsletter' => [
                "**{$subject}**\n\nWelcome to this week's edition!\n\n" . fake()->paragraph(3) . "\n\nRead more on our website.\n\nUnsubscribe | Preferences",
                "{$subject}\n\n" . fake()->paragraph(5) . "\n\nClick here to learn more.\n\n---\nThis email was sent to you because you subscribed.",
                "📧 {$subject}\n\n" . fake()->paragraph(4) . "\n\nStay tuned for more updates!\n\n© " . date('Y') . " Newsletter Inc.",
            ],
        ];

        return fake()->randomElement($templates[$category]);
    }
}
