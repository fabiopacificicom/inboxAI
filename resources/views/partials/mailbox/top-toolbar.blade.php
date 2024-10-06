<header id="top_toolbar" class="flex justify-between py-4 px-2">

    <div class="flex gap-1 items-center">
        <button type="button" wire:click="refreshMessages()" class="bg-gray-100 hover:bg-gray-300 p-2 rounded-lg m-1">
            <i class="bi bi-arrow-clockwise"></i>
            <span class="hidden sm:text-xs md:text-sm">
                Refresh
            </span>
        </button>
        <button title="Put all messages into the trash" type="button" wire:click="deleteMessages(false)"
            class="bg-gray-100 hover:bg-gray-300 p-2 rounded-lg m-1">
            <span class="hidden sm:text-xs md:text-sm">
                Trash All
            </span>
            <i class="bi bi-trash"></i>
        </button>
        <button title="Empty the trash" type="button" wire:click="cleanTrash()"
            class="bg-gray-100 hover:bg-gray-300 p-2 rounded-lg m-1">
            <span class="hidden sm:text-xs md:text-sm">
                Clean Trash
            </span>
            <i class="bi bi-trash2"></i>
        </button>
        <button title="Erase all messages permanently" type="button" wire:click="deleteMessages(true)"
            wire:confirm="Are you sure? This will delete all messages below from the imap server - this is not reversible."
            class="bg-gray-100 hover:bg-gray-300 p-2 rounded-lg m-1">
            <span class="hidden sm:text-xs md:text-sm">
                Delete All
            </span>
            <i class="bi bi-eraser-fill"></i>

        </button>
    </div>



    <div class="filters flex gap-1 items-center">

        {{-- Limit results --}}
        <div id="limit">
            <label for="limit" class="sr-only block text-sm font-medium text-gray-700">
                Limit results to:
            </label>
            <input type="number" name="limit" id="limit" min="1" max="50"
                wire:model.blur="limit"
                class="w-auto min-w-10 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                placeholder="Per page">
        </div>

        {{-- Filter email fetch backdating --}}
        <div class="period">
            <label for="period" class="sr-only block text-sm font-medium text-gray-700">Filter results
                by:</label>
            <select id="period" wire:model.live="filter"
                class="w-auto min-w-20 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">
                <option value="" disabled selected>Select a period...</option>
                <option value="day">Day</option>
                <option value="week">Week</option>
                <option value="month">Month</option>
            </select>
        </div>
    </div>
</header>
