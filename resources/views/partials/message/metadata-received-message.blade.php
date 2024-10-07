<div class="metadata py-4">
    <p class="text-gray-900 dark:text-slate-400 leading-none">
        <span>
            Message ID:
        </span>
        <strong>
            {{ $message['message_identifier'] }}
        </strong>
    </p>
    <p class="text-gray-900 dark:text-slate-400">
        <span>
            Sender:
        </span>
        <strong>
            {{ $message['sender'] }}
        </strong>
    </p>
    <p class="text-gray-900 dark:text-slate-400">
        <span>
            From:
        </span>
        <strong>
            {{ $message['from'] }}
        </strong>
    </p>
    <p class="text-gray-900 dark:text-slate-400">
        <span>
            Reply to:
        </span>
        <strong>
            {{ implode(',', $message['reply_to_addresses']) }}
        </strong>
    </p>
    <p class="text-gray-900 dark:text-slate-400">
        <span>
            Date:
        </span>
        <strong>
            {{$message['date']}}
        </strong>
    </p>
</div>
{{-- /.metadata --}}
