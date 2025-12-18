<div class="max-w-7xl mx-auto">
    <div class="bg-white dark:bg-slate-800 shadow-sm sm:rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-slate-200">Unified Inbox</h2>
                <div class="flex items-center space-x-4">
                    @livewire('accounts.account-switcher-component')
                    <button wire:click="showAllAccounts" class="bg-gray-500 dark:bg-slate-600 hover:bg-gray-700 dark:hover:bg-slate-700 text-white font-bold py-2 px-4 rounded">
                        Show All
                    </button>
                    <button wire:click="refreshMessages" class="bg-blue-500 dark:bg-blue-600 hover:bg-blue-700 dark:hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if ($messages?->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Account</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">From</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Folder</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                            @foreach ($messages as $message)
                                <tr class="{{ $message->is_seen ? 'opacity-75' : 'font-semibold' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                            {{ $message->account->name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-slate-200">{{ $message->sender ?: $message->from }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-slate-200">{{ $message->subject }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        {{ $message->date ? $message->date->diffForHumans() : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        {{ str_replace('INBOX.', '', $message->mailbox_folder) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button wire:click="processMessage({{ $message->id }})"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                            Process
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $messages->links() }}
                </div>
            @else
                <div class="text-center py-12 text-gray-500 dark:text-slate-400">
                    <p class="text-lg">No messages found.</p>
                    <p class="text-sm mt-2">Try refreshing or check your account settings.</p>
                </div>
            @endif
        </div>
    </div>
</div>

