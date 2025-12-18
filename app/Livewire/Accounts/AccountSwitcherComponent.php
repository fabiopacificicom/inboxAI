<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccountSwitcherComponent extends Component
{
    public $accounts = [];
    public $selectedAccountId;

    public function mount()
    {
        $this->loadAccounts();

        // Set first active account as default
        $firstActive = $this->accounts->where('is_active', true)->first();
        if ($firstActive) {
            $this->selectedAccountId = $firstActive->id;
        }
    }

    #[On('account-created')]
    #[On('account-updated')]
    public function loadAccounts()
    {
        $this->accounts = Account::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function switchAccount($accountId)
    {
        $this->selectedAccountId = $accountId;
        $account = Account::find($accountId);

        if ($account) {
            Log::info('🔄 Switched to account', ['account' => $account->name]);
            $this->dispatch('account-switched', accountId: $accountId);
        }
    }

    public function render()
    {
        return view('livewire.accounts.account-switcher-component');
    }
}

