<div class="grid grid-cols-1 sm:flex max-w-7xl mx-auto">

    <div class="sm:w-56 p-4">
        @include('partials.mailbox.mailboxes-sidebar')
    </div>


    <div id="inbox-table" class="bg-white shadow-sm sm:rounded-lg w-full">

        @include('partials.mailbox.top-toolbar')

        @if ($fetching)
        <div class="flex justify-center items-center h-full">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-12 w-12">
                Loading ...
            </div>
        </div>
        @else
        <div class="messages overflow-x-auto">
            {{-- Messages table --}}
            @include('partials.mailbox.messages-table')
        </div>
        @endif
    </div>


</div>
