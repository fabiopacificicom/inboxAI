<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use PhpImap\Mailbox;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Message;
use Illuminate\Support\Carbon;
use App\Livewire\AiReply\MessageListComponent;

/**
 * Trait HasMailboxConnection
 */
trait HasMailboxConnection
{
    public $mailboxes;
    public $selectedMailbox = 'INBOX';
    public $filter;

    /**
     * Get the mailbox connection from the given settings.
     * @param array $settings the settings for the mailbox connection or use the default settings
     * @param string $inbox the inbox to connect to default is 'INBOX'
     * @return Mailbox
     */

    public function makeMailboxFrom(array $settings = [], $inbox = 'INBOX'): Mailbox
    {
        // get the mailbox settings from the parameters or use the default settings
        $username = $settings['username'] ?? config('responder.imap.username');
        $password = $settings['password'] ?? config('responder.imap.password');
        $host = $settings['host'] ?? config('responder.imap.server');
        $port = $settings['port'] ?? config('responder.imap.port');

        // connect to the IMAP server and open a connection
        return new Mailbox(
            '{' . $host . ':' . $port . '/imap/ssl}' . $inbox, // IMAP server and mailbox folder
            $username, // Username for the before configured mailbox
            $password, // Password for the before configured username
            storage_path('app'), // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            true // Attachment filename mode (optional; false = random filename; true = original filename)
        );
    }


    /**
     * ## findMailboxMatching
     * Find the mailbox that matches the given category name
     * @param array $mailBoxes the mailboxes that are available on the server
     * @param string $category the category that should be found
     * @return string the mailbox short path that matches the given category
     */
    public function findMailboxMatching(array $mailBoxes, string $category)
    {
        $mailboxCategory = [...array_filter($mailBoxes, function ($mailCategory) use ($category) {
            return $mailCategory['shortpath'] === 'INBOX.' . ucfirst($category);
        })];

        if (empty($mailboxCategory) || count($mailboxCategory) == 0) {
            throw new \Exception("The provided category is not a vailad mailbox folder", 1);
        }

        return $mailboxCategory[0]['shortpath'];
    }

    /**
     * trashImapMessagesByMessageIds
     * Trash the given messages by their message id
     * @param Collection $messageIds the message ids that should be trashed
     * @return void
     */
    public function trashImapMessagesByMessageIds(Collection $messageIds)
    {
        // make the mailbox connection
        //dd($this->selectedMailbox, $messageIds);
        $mailbox = $this->makeMailboxFrom([], $this->selectedMailbox);
        Log::info('trashImapMessagesByMessageIds', [$messageIds]);

        foreach ($messageIds as $id) {
            $mailbox->moveMail(intval($id), 'INBOX.Trash');
        }
    }
    /**
     * deleteImapMessagesByMessageIds
     * deletes the given messages from the IMAP server
     * @param Collection $messageIds the message ids that should be trashed
     * @return void
     */
    public function deleteImapMessagesByMessageIds(Collection $messageIds)
    {
        // make the mailbox connection
        $mailbox = $this->makeMailboxFrom([]);
        Log::info('deleteImapMessagesByMessageIds', [$messageIds]);

        foreach ($messageIds as $id) {
            $mailbox->deleteMail(intval($id));
        }
        $mailbox->expungeDeletedMails();
    }

    public function emptyMailbox($folder = 'INBOX.Trash')
    {
        $mailbox = $this->makeMailboxFrom([], $folder);
        // remove all messages in the trash
        $ids = $mailbox->searchMailbox('ALL');
        //dd($ids, $folder);
        foreach ($ids as $id) {
            $mailbox->deleteMail(intval($id));
        }
        //dd($ids);
        $mailbox->expungeDeletedMails();
    }




    /* TODO:
    Swap the implementation with the method creatd in the HasMailboxConnection trait */
    private function establishMailboxConnection($folder = 'INBOX')
    {


        /* @todo
         These constants seems to be missing when the app is packaged using native script
        to build a desktop app.
        If activated the webapp stops working with a constant already declared error.
       define('OP_READONLY', 0);
        define('OP_ANONYMOUS', 0);
        define('OP_HALFOPEN', 0);
        define('CL_EXPUNGE', 0);
        define('OP_DEBUG', 0);
        define('OP_SHORTCACHE', 0);
        define('OP_SILENT', 0);
        define('OP_PROTOTYPE', 0);
        define('OP_SECURE', 0);
        define('SE_UID', 0); */




        return new Mailbox(
            '{' . $this->host . ':' . $this->port . '/imap/ssl}' . $folder, // IMAP server and mailbox folder
            $this->username, // Username for the before configured mailbox
            $this->password, // Password for the before configured username
            storage_path('app'), // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            true // Attachment filename mode (optional; false = random filename; true = original filename)
            // ... existing parameters ...
        );
    }

    /**
     *
     * Search messages in the given folder of the configured mailbox.
     * @param Mailbox $mailbox IMAP mailbox connection
     * @return array IMAP message ids
     */
    private function searchEmails($mailbox): array
    {

        //dd($mailbox);
        // Get all emails (messages)
        // PHP.net imap_search criteria: http://php.net/manual/en/function.imap-search.php

        return Cache::remember(
            'mailIds',
            now()->addHour(),
            function () use ($mailbox) {
                $date = Carbon::now()->subDays($this->getDays())->format('Ymd');
                //dd($date);
                $criteria = 'SINCE "' . $date . '"';
                //dd($criteria);
                $mailsIds = $mailbox->searchMailbox($criteria);
                return $mailsIds;
            }
        );
    }

    /**
     * fetchEmailMessges given a mailbox and a list of messages ids
     * obtained with searchEmails()
     *
     * @param Mailbox $mailbox IMAP mailbox connection
     * @param array $mailsIds IMAP message ids
     * @param int $limit Maximum number of messages to fetch (optional) default 15;
     * @return void
     */
    public function fetchEmailMessages($mailbox, $mailsIds, $limit = 15)
    {
        //dd($mailbox, $mailsIds);

        if (!$mailsIds) {
            //dd('here');
            Log::info('Mailbox is empty');

            return to_route('dashboard')->with('message', 'Mailbox is empty');
        }
        // Put the latest email on top of listing
        rsort($mailsIds);
        // Get the last 15 emails only (@todo make this dynamic)
        array_splice($mailsIds, $limit);

        //dd($mailsIds);
        // Loop through emails one by one
        Cache::forget('messages');

        Cache::remember('messages', now()->addDay(), function () use ($mailsIds, $mailbox) {
            //dd($mailsIds);
            return array_map(function ($num) use ($mailbox) {

                // TODO: Performance improvements:
                // Update the implementation and start by just fetching the message headers
                // and then fetch the body when user clicks on the message.
                //$head = $mailbox->getMailHeader($num);
                //dd($head->mailboxFolder);
                $markAsSeen = true;
                $mail = $mailbox->getMail($num, $markAsSeen);
                //dd($num, $mail->mailboxFolder);

                $message = [
                    'message_identifier' => $mail->id,
                    'is_seen' => $mail->isSeen,
                    'is_answered' => $mail->isAnswered,
                    'is_recent' => $mail->isRecent,
                    'is_flagged' => $mail->isFlagged,
                    'is_deleted' => $mail->isDeleted,
                    'is_draft' => $mail->isDraft,
                    'subject' => $mail->subject,
                    'from' => $mail->fromAddress,
                    'sender' => isset($mail->fromName) ? $mail->fromName : '',
                    'reply_to_addresses' => array_keys($mail->replyTo),
                    'date' => $this->normalizeDate($mail->date),
                    'content' => str_replace(["\t", "\r", "\n"], "", trim($mail->textPlain)),
                    'mailbox_folder' => $mail->mailboxFolder
                    // Add more fields as needed
                ];
                //dd($message);

                //dd($message['message_identifier'], $message);

                $messageObject = Message::updateOrCreate(['message_identifier' => $mail->id], [
                    'message_identifier' => $message['message_identifier'],
                    'subject' => $message['subject'],
                    'from' =>  $message['from'],
                    'sender' => $message['sender'],
                    'reply_to_addresses'  => $message['reply_to_addresses'],
                    'date' => $message['date'],
                    'content' => $message['content'],
                    'is_seen' => $message['is_seen'],
                    'is_answered' => $message['is_answered'],
                    'is_recent' => $message['is_recent'],
                    'is_flagged' => $message['is_flagged'],
                    'is_deleted' => $message['is_deleted'],
                    'is_draft' => $message['is_draft'],
                    'mailbox_folder' => $message['mailbox_folder']

                ]);
                //dd($messageObject);


                return $messageObject;
            }, $mailsIds);
            //dd($messages);
        });



        //dd($messages);


        $this->dispatch('mailbox-sync-event')->to(MessageListComponent::class);
    }

    private function handleConnectionException($ex)
    {
        $message = "IMAP connection failed: " . $ex->getMessage();
        $this->addError('connection', $message);
        Log::error($message);
    }


    private function getDays()
    {
        return match ($this->filter) {
            'day' => 1,
            'week' => 7,
            'month' => 30,
            default => 1,
        };
    }

    private function normalizeDate($dateString)
    {
        //dd($dateString);
        // Define an array of known formats
        $knownFormats = [
            'Y-m-d\TH:i:sP',    // Example: 2024-08-06T13:29:12+00:00
            // Add more formats as needed
            'l, d F Y H:i:s P' // Example: Tue, 6 Aug 2024 19:51:35 +0800
        ];

        // Attempt to parse the date using known formats
        foreach ($knownFormats as $format) {

            try {
                //code...
                $date = Carbon::createFromFormat($format, $dateString);
            } catch (\Throwable $th) {
                dd($th->getMessage(), $format, $dateString);
            }

            if ($date !== false) {
                return $date;
            }
        }

        // As a last resort, try letting Carbon parse the date automatically
        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            // Handle the exception if the date cannot be parsed
            // This could log an error, return null, or use a default date
            Log::error('❌MailBoxConnectionComponent Error:' . $e->getMessage(), ['date' => $dateString]);
            return null;
        }
    }
}
