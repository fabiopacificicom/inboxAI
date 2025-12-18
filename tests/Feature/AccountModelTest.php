<?php

use App\Models\Account;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Crypt;

test('account can be created with encrypted credentials', function () {
    $user = User::factory()->create();

    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Test Account',
        'email' => 'test@example.com',
        'imap_host' => 'imap.gmail.com',
        'imap_port' => 993,
        'imap_encryption' => 'ssl',
        'imap_password' => 'secret_password',
        'is_active' => true,
    ]);

    expect($account)->toBeInstanceOf(Account::class)
        ->and($account->name)->toBe('Test Account')
        ->and($account->email)->toBe('test@example.com')
        ->and($account->is_active)->toBeTrue();

    // Verify password is encrypted in database
    $storedPassword = $account->getAttributes()['imap_password'];
    expect($storedPassword)->not->toBe('secret_password');

    // Verify password decrypts correctly
    expect($account->imap_password)->toBe('secret_password');
});

test('account belongs to user', function () {
    $user = User::factory()->create();

    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Test Account',
        'email' => 'test@example.com',
        'imap_host' => 'imap.gmail.com',
        'imap_port' => 993,
        'imap_encryption' => 'ssl',
        'imap_password' => 'password',
    ]);

    expect($account->user)->toBeInstanceOf(User::class)
        ->and($account->user->id)->toBe($user->id);
});

test('account has many messages', function () {
    $user = User::factory()->create();

    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Test Account',
        'email' => 'test@example.com',
        'imap_host' => 'imap.gmail.com',
        'imap_port' => 993,
        'imap_encryption' => 'ssl',
        'imap_password' => 'password',
    ]);

    Message::create([
        'account_id' => $account->id,
        'message_identifier' => '12345',
        'subject' => 'Test Subject',
        'from' => 'sender@example.com',
        'sender' => 'Test Sender',
        'date' => now(),
        'content' => 'Test content',
        'mailbox_folder' => 'INBOX',
    ]);

    expect($account->messages)->toHaveCount(1)
        ->and($account->messages->first())->toBeInstanceOf(Message::class);
});

test('smtp password is encrypted', function () {
    $user = User::factory()->create();

    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Test Account',
        'email' => 'test@example.com',
        'imap_host' => 'imap.gmail.com',
        'imap_port' => 993,
        'imap_encryption' => 'ssl',
        'imap_password' => 'imap_secret',
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_password' => 'smtp_secret',
    ]);

    // Verify SMTP password is encrypted in database
    $storedSmtpPassword = $account->getAttributes()['smtp_password'];
    expect($storedSmtpPassword)->not->toBe('smtp_secret');

    // Verify password decrypts correctly
    expect($account->smtp_password)->toBe('smtp_secret');
});

test('account settings are cast to array', function () {
    $user = User::factory()->create();

    $settings = ['auto_process' => true, 'sync_interval' => 5];

    $account = Account::create([
        'user_id' => $user->id,
        'name' => 'Test Account',
        'email' => 'test@example.com',
        'imap_host' => 'imap.gmail.com',
        'imap_port' => 993,
        'imap_encryption' => 'ssl',
        'imap_password' => 'password',
        'settings' => $settings,
    ]);

    expect($account->settings)->toBeArray()
        ->and($account->settings['auto_process'])->toBeTrue()
        ->and($account->settings['sync_interval'])->toBe(5);
});

