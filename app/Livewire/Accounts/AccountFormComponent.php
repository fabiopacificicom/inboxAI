<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccountFormComponent extends Component
{
    public $accountId;
    public $isEditing = false;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|email|max:255')]
    public $email = '';

    #[Validate('required|string')]
    public $imap_host = '';

    #[Validate('required|integer|min:1|max:65535')]
    public $imap_port = 993;

    #[Validate('required|in:ssl,tls')]
    public $imap_encryption = 'ssl';

    #[Validate('required|string')]
    public $imap_password = '';

    #[Validate('nullable|string')]
    public $smtp_host = '';

    #[Validate('nullable|integer|min:1|max:65535')]
    public $smtp_port = 587;

    #[Validate('nullable|string')]
    public $smtp_password = '';

    public $is_active = true;

    #[On('edit-account')]
    public function editAccount($accountId)
    {
        $this->reset();
        $this->accountId = $accountId;
        $this->isEditing = true;

        $account = Account::findOrFail($accountId);

        $this->name = $account->name;
        $this->email = $account->email;
        $this->imap_host = $account->imap_host;
        $this->imap_port = $account->imap_port;
        $this->imap_encryption = $account->imap_encryption;
        // Don't populate password for security
        $this->smtp_host = $account->smtp_host ?? '';
        $this->smtp_port = $account->smtp_port ?? 587;
        $this->is_active = $account->is_active;

        Log::info('✏️ Editing account', ['account' => $account->name]);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => Auth::id(),
            'name' => $this->name,
            'email' => $this->email,
            'imap_host' => $this->imap_host,
            'imap_port' => $this->imap_port,
            'imap_encryption' => $this->imap_encryption,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'is_active' => $this->is_active,
        ];

        // Only update password if provided
        if (!empty($this->imap_password)) {
            $data['imap_password'] = $this->imap_password;
        }

        if (!empty($this->smtp_password)) {
            $data['smtp_password'] = $this->smtp_password;
        }

        if ($this->isEditing && $this->accountId) {
            $account = Account::findOrFail($this->accountId);
            $account->update($data);

            Log::info('✅ Account updated', ['account' => $account->name]);
            $this->dispatch('account-updated');
            session()->flash('message', 'Account updated successfully.');
        } else {
            // For new accounts, password is required
            if (empty($this->imap_password)) {
                $this->addError('imap_password', 'IMAP password is required for new accounts.');
                return;
            }

            $account = Account::create($data);

            Log::info('✅ Account created', ['account' => $account->name]);
            $this->dispatch('account-created');
            session()->flash('message', 'Account created successfully.');
        }

        $this->reset();
    }

    public function cancel()
    {
        $this->reset();
    }

    public function render()
    {
        return view('livewire.accounts.account-form-component');
    }
}

