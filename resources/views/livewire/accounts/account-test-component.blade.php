<div class="max-w-lg mx-auto p-6">
    <div class="bg-white dark:bg-slate-800 shadow-sm sm:rounded-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 dark:text-slate-200 mb-4">Test IMAP Connection</h3>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">IMAP Host</label>
            <input type="text" wire:model="testHost" class="shadow appearance-none border dark:border-slate-600 rounded w-full py-2 px-3 text-gray-700 dark:text-slate-200 dark:bg-slate-700 focus:ring-2 focus:ring-blue-500" placeholder="imap.gmail.com">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Port</label>
            <input type="number" wire:model="testPort" class="shadow appearance-none border dark:border-slate-600 rounded w-full py-2 px-3 text-gray-700 dark:text-slate-200 dark:bg-slate-700 focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Email</label>
            <input type="email" wire:model="testEmail" class="shadow appearance-none border dark:border-slate-600 rounded w-full py-2 px-3 text-gray-700 dark:text-slate-200 dark:bg-slate-700 focus:ring-2 focus:ring-blue-500" placeholder="your@email.com">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Password</label>
            <input type="password" wire:model="testPassword" class="shadow appearance-none border dark:border-slate-600 rounded w-full py-2 px-3 text-gray-700 dark:text-slate-200 dark:bg-slate-700 focus:ring-2 focus:ring-blue-500">
        </div>

        <button wire:click="testConnection" class="bg-blue-500 dark:bg-blue-600 hover:bg-blue-700 dark:hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
            Test Connection
        </button>

        @if($testResult)
            <div class="mt-4 p-4 rounded {{ $testStatus === 'success' ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200' }}">
                {{ $testResult }}
            </div>
        @endif
    </div>
</div>

