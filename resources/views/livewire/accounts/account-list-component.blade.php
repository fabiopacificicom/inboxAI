<div class="max-w-7xl mx-auto">
    <div class="bg-white dark:bg-slate-800 shadow-sm sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-slate-200">Email Accounts</h2>
            <button wire:click="$dispatch('edit-account', { accountId: null })"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-500 dark:bg-blue-600 hover:bg-blue-700 dark:hover:bg-blue-700 text-white rounded-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                </svg>
                Add Account
            </button>
        </div>

        @if (session()->has('message'))
            <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-gray-50 dark:bg-slate-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Server</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                    @forelse ($accounts as $account)
                        <tr class="{{ $account->is_active ? '' : 'opacity-50' }}">
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900 dark:text-slate-200">{{ $account->name }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500 dark:text-slate-400">{{ $account->email }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500 dark:text-slate-400">{{ $account->imap_host }}:{{ $account->imap_port }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $account->is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                                    {{ $account->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                <button wire:click="toggleActive({{ $account->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                    {{ $account->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button wire:click="editAccount({{ $account->id }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                    Edit
                                </button>
                                <button wire:click="deleteAccount({{ $account->id }})" onclick="return confirm('Are you sure?')" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">No accounts found. Add your first email account to get started.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
