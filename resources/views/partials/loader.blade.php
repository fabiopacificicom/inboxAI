 <div class="loader">
     <span wire:loading.class.remove="{{$selectedMailbox == $box['shortpath'] ? 'hidden' : ''}}" class="hidden">
     </span>
     <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
         fill="none" viewBox="0 0 24 24">
         <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
         </circle>
         <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 01.33-2.217l1.745 1.036A6 6 0 006 12h-2z">
         </path>
     </svg>
     <span wire:loading class="text-xs">{{ __('loading...') }}</span>
 </div>
