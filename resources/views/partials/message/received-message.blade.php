 <div class="received-message mt-4">
     @if(!$message['content'])
     {{ __('Process the message first to see its content')}}
     @else

     <h3 class="text-md font-semibold text-gray-800 dark:text-slate-600 uppercase">{{__('Content')}}</h3>
     <div class="mt-2 text-sm">
         <!-- Blade Template -->
         @php
         $message_identifier =
         'emailIframe-' . md5($message['message_identifier']);
         @endphp
         <div class="overflow-y-auto"
             id="wrapper-{{ $message_identifier }}">
             {{ $message['content'] }}
         </div>

     </div>
     @endif

 </div>
 {{-- /.received-message --}}