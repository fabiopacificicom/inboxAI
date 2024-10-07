<div>
    <style>
        #knowledge:popover-open {
            width: 50%;
            max-width: 360px;
            height: 100dvh;
            position: fixed;
            inset: unset;
            bottom: 5px;
            right: 5px;
            margin: 0;

            @media (min-width: 992px) {
                max-width: 600px
            }
        }
    </style>

    <button type="button" popovertarget="knowledge" popoveraction="show" class=" text-xs flex items-center gap-1 p-3 rounded-xl hover:bg-gray-200 dark:hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 ">
        <i class="bi bi-collection"></i>
        <span class="hidden md:inline">{{__('Knowledge Base')}}</span>
    </button>

    <div id="knowledge" popover class="bg-white dark:bg-slate-800 dark:text-slate-400 p-4 rounded-lg shadow-lg fixed top-0 right-0">
        <div class="flex items-center justify-between py-3 mb-3 mt-2">
            <h3 class="pb-2 text-lg font-medium text-gray-500">Knowledge base</h3>
            <button type="button" class="p-3 text-xl text-gray-700 dark:hover:bg-slate-800 hover:text-gray-100 hover:bg-gray-800 transition-all rounded-lg" popoveraction="hide" popovertarget="knowledge">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <p>Add external links of web pages to read the content from</p>

        <form wire:submit.prevent="addLink">
            <div class="flex items-start">
                <input type="text" name="link" placeholder="https://" class="w-full p-2 mb-4 rounded-lg rounded-e-none border shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50" wire:model="link">
                <button class="uppercase p-2 border border-gray-900 bg-gray-800 text-white hover:bg-gray-700 rounded-lg shadow-sm rounded-l-none">Add</button>
            </div>
            <p class="text-xs">Insert a link to extract its contents</p>
        </form>


        @if (!empty( $links ) )
        <h4 class="mt-6 pb-2 text-lg font-medium text-gray-500">
            External Links
        </h4>
        <ul class="url_list">
            @foreach ($links as $link )

            <li class="">
                <div class="flex justify-between items-center dark:hover:bg-slate-700 hover:bg-gray-200 p-2 rounded-md">
                    <a href="{{$link->url}}" target="_blank" class=" block mb-2">
                        <i class="bi bi-box-arrow-up-right"></i> {{$link->url}}
                    </a>

                    <button type="button" class="hover:bg-red-300 hover:text-red-800 p-2 rounded-lg transition-all" wire:click="delete({{$link}})" wire:confirm>
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="content">
                    <details>
                        <summary>View Content</summary>
                        {{$link->content}}
                    </details>
                </div>
            </li>
            @endforeach

        </ul>
        @endunless


    </div>

</div>
