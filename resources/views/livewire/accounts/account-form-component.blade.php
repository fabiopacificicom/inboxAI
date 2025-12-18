<div class="max-w-2xl mx-auto p-6">
    <div class="bg-white dark:bg-slate-800 shadow-sm sm:rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-slate-200 mb-6">{{ $isEditing ? 'Edit Account' : 'Add New Account' }}</h2>

        <form wire:submit.prevent="save">
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Account Name</label>
                <input type="text" wire:model="name" class="shadow appearance-none border dark:border-slate-600 rounded w-full py-2 px-3 text-gray-700 dark:text-slate-200 dark:bg-slate-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500" placeholder="Work Email">
                @error('name') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Email Address</label>
                <input type="email" wire:model="email" class="shadow appearance-none border dark:border-slate-600 rounded w-full py-2 px-3 text-gray-700 dark:text-slate-200 dark:bg-slate-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500" placeholder="your@email.com">
                @error('email') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="border-t border-gray-200 dark:border-slate-600 pt-4 mt-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-slate-300 mb-3">IMAP Settings</h3>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">IMAP Host</label>
                    <input type="text" wire:model="imap_host" class="shadow appearance-none border dark:border-slate-600 rounded w-full py-2 px-3 text-gray-700 dark:text-slate-200 dark:bg-slate-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500" placeholder="imap.gmail.com">
                    @error('imap_host') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Port</label>
                    <input type="number" wire:model="imap_port" class="shadow appearance-none border dark:border-slate-600 rounded w-full py-2 px-3 text-gray-700 dark:text-slate-200 dark:bg-slate-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500">
                    @error('imap_port') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Encryption</label>
                <select wire:model="imap_encryption" class="shadow appearance-none border dark:border-slate-600 rounded w-full py-2 px-3 text-gray-700 dark:text-slate-200 dark:bg-slate-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500">
                    <option value="ssl">SSL</option>
                    <option value="tls">TLS</option>
                </select>
                @error('imap_encryption') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">IMAP Password {{ $isEditing ? '(leave blank to keep current)' : '' }}</label>
                <input type="password" wire:model="imap_password" class="shadow appearance-none border dark:border-slate-600 rounded w-full py-2 px-3 text-gray-700 dark:text-slate-200 dark:bg-slate-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500">
                @error('imap_password') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 dark:bg-blue-600 hover:bg-blue-700 dark:hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    {{ $isEditing ? 'Update Account' : 'Create Account' }}
                </button>
                <button type="button" wire:click="cancel" class="bg-gray-500 dark:bg-slate-600 hover:bg-gray-700 dark:hover:bg-slate-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

