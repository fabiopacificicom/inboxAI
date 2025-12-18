<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Message;
use App\Traits\Processable;
use App\Traits\Calendarable;
use App\Traits\Helpers;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UnifiedInboxComponent extends Component
{
    use WithPagination, Processable, Calendarable, Helpers;

    public $selectedAccountId = null; // null = all accounts
    public $accounts = [];

    public function mount()
    {
        $this->loadAccounts();
    }

    #[On('account-switched')]
    public function switchAccount($accountId)
    {
        $this->selectedAccountId = $accountId;
        $this->resetPage();

        Log::info('📬 Unified inbox filtered', [
            'account_id' => $accountId,
            'view' => $accountId ? 'single' : 'unified'
        ]);
    }

    public function showAllAccounts()
    {
        $this->selectedAccountId = null;
        $this->resetPage();

        Log::info('📬 Unified inbox showing all accounts');
    }

    public function loadAccounts()
    {
        // Load active accounts ordered by creation date (first connected first)
        $this->accounts = Account::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->get();

        // If the user has at least one active account and no account is selected,
        // default to the first (earliest connected) account so the unified inbox
        // reflects the user's initial account by default.
        if ($this->accounts->isNotEmpty() && is_null($this->selectedAccountId)) {
            $this->selectedAccountId = $this->accounts->first()->id;
        }
    }

    public function getMessagesProperty()
    {
        // Build cache key based on view (unified or single account)
        $cacheKey = $this->selectedAccountId
            ? "messages:account:{$this->selectedAccountId}:user:" . Auth::id()
            : "messages:unified:user:" . Auth::id();

        return Cache::remember($cacheKey, now()->addHour(), function () {
            $query = Message::query()
                ->with('account:id,name,email')
                ->whereHas('account', function($q) {
                    $q->where('user_id', Auth::id())
                      ->where('is_active', true);
                });

            // Filter by specific account if selected
            if ($this->selectedAccountId) {
                $query->where('account_id', $this->selectedAccountId);
            }

            return $query->orderBy('date', 'desc')->paginate(50);
        });
    }

    public function refreshMessages()
    {
        // Clear all message caches
        Cache::forget("messages:unified:user:" . Auth::id());

        foreach ($this->accounts as $account) {
            Cache::forget("messages:account:{$account->id}:user:" . Auth::id());
        }

        Log::info('🔄 Unified inbox cache cleared');

        $this->dispatch('messages-refreshed');
    }

    public function processMessage($messageId)
    {
        Log::info('🤖 Processing message from unified inbox', ['message_id' => $messageId]);

        $this->processInboxMessage($messageId);
        $this->refreshMessages();
    }

    public function render()
    {
        // In Livewire 3, computed properties (getXxxProperty methods) are accessed without explicit call
        // The view can access $messages directly and it will call getMessagesProperty() automatically
        return view('livewire.unified-inbox-component');
    }
}

