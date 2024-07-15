<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">


                {{-- New Main mailbox assistant component --}}
                <livewire:ai-reply.main-mailbox-assistant-component />

                {{--

                ## Initial implementation in a single component.
                 this component has been refactored into multiple
                 components however can still be used as a
                 standalone element.
                 <livewire:ai-reply.mailbox-connect-form />
                 --}}

            </div>
        </div>

    </div>
</x-app-layout>
