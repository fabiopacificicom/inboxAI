<x-app-layout>
    <x-slot name="header">
        <div class=" flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>

            <div class="tools flex items-center">
                @livewire('show-calendar', ['settings' => $settings])
                @livewire('ai-reply.mailbox-connection-component', ['settings' => $settings])
            </div>
        </div>

    </x-slot>



    <div class="py-4 w-full sm:px-6 lg:px-8">
        @include('partials.session-message')

        {{-- New Main mailbox assistant component --}}

        @livewire('ai-reply.main-mailbox-assistant-component', ['settings' => $settings])


        {{--

                ## Initial implementation in a single component.
                 this component has been refactored into multiple
                 components however can still be used as a
                 standalone element.
                 <livewire:ai-reply.mailbox-connect-form />
                 --}}


    </div>
</x-app-layout>
