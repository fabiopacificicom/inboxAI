<?php

namespace App\Livewire\Accounts;

use App\Traits\HasMailboxConnection;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class AccountTestComponent extends Component
{
    use HasMailboxConnection;

    public $testHost = '';
    public $testPort = 993;
    public $testEmail = '';
    public $testPassword = '';
    public $testResult = '';
    public $testStatus = ''; // 'success', 'error', or ''

    public function testConnection()
    {
        $this->testResult = '';
        $this->testStatus = '';

        if (empty($this->testHost) || empty($this->testEmail) || empty($this->testPassword)) {
            $this->testStatus = 'error';
            $this->testResult = 'Please fill in all connection details.';
            return;
        }

        try {
            Log::info('🔌 Testing IMAP connection', [
                'host' => $this->testHost,
                'port' => $this->testPort,
                'email' => $this->testEmail
            ]);

            $settings = [
                'username' => $this->testEmail,
                'password' => $this->testPassword,
                'host' => $this->testHost,
                'port' => $this->testPort,
            ];

            $mailbox = $this->makeMailboxFromSettings(settings: $settings);

            // Try to get mailbox status
            $status = $mailbox->getMailboxInfo();

            $this->testStatus = 'success';
            $this->testResult = '✅ Connection successful! Mailbox accessible.';

            Log::info('✅ IMAP connection test passed', ['mailbox' => $status->Mailbox]);

        } catch (\Exception $e) {
            $this->testStatus = 'error';
            $this->testResult = '❌ Connection failed: ' . $e->getMessage();

            Log::error('❌ IMAP connection test failed', [
                'error' => $e->getMessage(),
                'host' => $this->testHost,
                'port' => $this->testPort
            ]);
        }
    }

    public function render()
    {
        return view('livewire.accounts.account-test-component');
    }
}

