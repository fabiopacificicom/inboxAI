<x-app-layout>
    <x-slot name="header">
        <div class=" flex items-center justify-between">
            <h2 class="font-semibold text-xs uppercase md:text-sm lg:text-lg text-gray-800 dark:text-slate-400 leading-tight">

                <i class="bi bi-inbox"></i>
                {{ __('Unified Inbox') }}
            </h2>

            <div class="tools flex items-center">
                @livewire('ai-reply.knowledge-base-component')
                @livewire('show-calendar', ['settings' => $settings])
                @livewire('ai-reply.mailbox-connection-component', ['settings' => $settings])
            </div>
        </div>

    </x-slot>



    <div class="py-4 w-full sm:px-6 lg:px-8">
        @include('partials.session-message')

        {{-- Main mailbox assistant component showing message list --}}

        @livewire('ai-reply.main-mailbox-assistant-component', ['settings' => $settings])

    </div>
</x-app-layout>
