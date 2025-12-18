<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccountListComponent extends Component
{
    public $accounts = [];
    public $selectedAccountId;

    public function mount()
    {
        $this->loadAccounts();
    }

    #[On('account-created')]
    #[On('account-updated')]
    #[On('account-deleted')]
    public function loadAccounts()
    {
        $this->accounts = Account::where('user_id', Auth::id())
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        Log::info('📋 Accounts loaded', ['count' => $this->accounts->count()]);
    }

    public function editAccount($accountId)
    {
        $this->dispatch('edit-account', accountId: $accountId);
    }

    public function toggleActive($accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->is_active = !$account->is_active;
        $account->save();

        Log::info('🔄 Account status toggled', [
            'account' => $account->name,
            'is_active' => $account->is_active
        ]);

        $this->loadAccounts();
        $this->dispatch('account-updated');
    }

    public function deleteAccount($accountId)
    {
        $account = Account::findOrFail($accountId);
        $accountName = $account->name;

        $account->delete();

        Log::info('🗑️ Account deleted', ['account' => $accountName]);

        $this->loadAccounts();
        $this->dispatch('account-deleted');
    }

    public function render()
    {
        return view('livewire.accounts.account-list-component');
    }
}

