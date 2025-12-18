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
                    <div class="text-center py-20">
                        <i class="bi bi-chat-dots text-6xl text-gray-400 dark:text-slate-600 mb-4"></i>
                        <h3 class="text-2xl font-semibold mb-2">Chat UI - Phase 2</h3>
                        <p class="text-gray-600 dark:text-slate-400">Conversational AI interface coming soon</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
