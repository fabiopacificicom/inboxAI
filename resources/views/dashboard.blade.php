<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xs uppercase md:text-sm lg:text-lg text-gray-800 dark:text-slate-400 leading-tight">
                <i class="bi bi-chat-dots"></i>
                {{ __('Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-slate-100">
                    <livewire:chat.chat-window-component />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
