<div>

    @if (session('message'))
        <div class="alert bg-red-300 text-red-900 p-2 rounded">
            {{ session('message') }}
        </div>
    @endif

    {{-- Loader --}}


    <div x-data="{ open: false }">
        <div class="flex justify-between items-center mb-4">
            <button @click="open = !open"
                class="flex items-center gap-2 bg-gray-900 text-white p-3 rounded-2xl hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                Connection
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-sliders2-vertical" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M0 10.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1H3V1.5a.5.5 0 0 0-1 0V10H.5a.5.5 0 0 0-.5.5M2.5 12a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 1 0v-2a.5.5 0 0 0-.5-.5m3-6.5A.5.5 0 0 0 6 6h1.5v8.5a.5.5 0 0 0 1 0V6H10a.5.5 0 0 0 0-1H6a.5.5 0 0 0-.5.5M8 1a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 1 0v-2A.5.5 0 0 0 8 1m3 9.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1H14V1.5a.5.5 0 0 0-1 0V10h-1.5a.5.5 0 0 0-.5.5m2.5 1.5a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 1 0v-2a.5.5 0 0 0-.5-.5" />
                </svg>
            </button>
            {{-- Filter email fetch backdating --}}
            <div class="flex justify-end items-center space-x-4 mt-4">
                <div class="w-full md:w-auto">
                    <select wire:model.live="filter"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <option value="day">Day</option>
                        <option value="week">Week</option>
                        <option value="month">Month</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Email connection form --}}
        <div x-show="open" style="display: none;">
            <form wire:submit.prevent="connectMailbox" class="md:grid md:grid-cols-2 md:gap-4">
                <input
                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                    type="text" wire:model="host" placeholder="IMAP Host">
                <input
                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                    type="text" wire:model="port" placeholder="Port">
                <input
                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                    type="text" wire:model="encryption" placeholder="Encryption (ssl/tls)">
                <input
                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                    type="email" wire:model="username" placeholder="Email">
                <input
                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 md:col-span-2"
                    type="password" wire:model="password" placeholder="Password">
                <button
                    class="w-full bg-gray-900 text-white p-3 rounded-2xl hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 md:col-span-2"
                    type="submit">Connect</button>
            </form>
        </div>


    </div>

    {{-- Reply form --}}
    @if (array_key_exists('message', $reply) && array_key_exists('content', $reply['message']))
        <div class="reply my-4">
            <textarea class="w-full rounded-xxl" name="reply" id="reply" cols="30" rows="10">{!! $reply['message']['content'] !!}</textarea>
            <button class="bg-gray-800 text-white rounded-2xl">Reply</button>
        </div>
    @endif

    {{-- Messages table --}}
    @if ($fetching)
        <div class="loader">
            Loading ...
        </div>
    @else
        <div class="messages">

            @if (session()->has('message'))
                <div class="alert">{{ session('message') }}</div>
            @endif


            <hr class="my-8">

            <table>

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>
                            Sender
                        </th>
                        <th>Reply To</th>
                        <th>Subject</th>
                        <th>Actions</th>

                    </tr>
                </thead>

                <tbody>

                    @forelse ($messages as $message)
                        <tr>
                            <td>
                                {{ $message['date'] }}
                            </td>
                            <td>
                                {{ $message['sender'] }}
                                {{ $message['from'] }}
                            </td>
                            <td>
                                {{ Arr::join($message['replyToAddresses'], ',') }}
                            </td>
                            <td>
                                {{ $message['subject'] }}
                            </td>
                            <td x-data="{ show: false }">

                                <button class="p-2 rounded-xxl bg-gray-800 text-white" x-on:click="show = !show">
                                    view
                                </button>


                                <div class="modal-backdrop" x-show="show">
                                    <div class="modal-card">

                                        <div class="flex justify-between">

                                            <button class="p-3 rounded-xl btn bg-gray-800 text-white"
                                                wire:click="getAnswer('{{ $message['messageId'] }}')">
                                                Generate Reply
                                            </button>
                                            <button class="p-2 text-gray-900" x-on:click="show = !show">
                                                x
                                            </button>
                                        </div>


                                        <hr class="my-4">
                                        <header class="py-4">
                                            <div>
                                                messageID: {{ $message['messageId'] }}
                                            </div>
                                            <div>
                                                sender: {{ $message['sender'] }}
                                            </div>

                                        </header>

                                        <div class="original">
                                            <h3 class="text-xxl">Original</h3>
                                            {!! $message['content'] !!}
                                        </div>

                                    </div>
                                </div>


                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">nothing here</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>


        </div>
    @endif


</div>
