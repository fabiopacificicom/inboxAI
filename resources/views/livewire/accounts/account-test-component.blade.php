<div class="container mx-auto p-6 max-w-lg">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Test IMAP Connection</h3>
        
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">IMAP Host</label>
            <input type="text" wire:model="testHost" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="imap.gmail.com">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Port</label>
            <input type="number" wire:model="testPort" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" wire:model="testEmail" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="your@email.com">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <input type="password" wire:model="testPassword" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
        </div>
        
        <button wire:click="testConnection" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
            Test Connection
        </button>
        
        @if($testResult)
            <div class="mt-4 p-4 rounded {{ $testStatus === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $testResult }}
            </div>
        @endif
    </div>
</div>

