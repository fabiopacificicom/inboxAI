# InboxAI - Copilot Instructions

## Project Overview

InboxAI is a Laravel-based email automation system that uses local LLM (Ollama) to:
- Automatically classify and process incoming emails via IMAP
- Generate contextual replies using AI models (Llama, Gemma, Phi3, etc.)
- Create Google Calendar events from email content
- Summarize newsletters and long conversations
- Provide a desktop app (NativePHP/Electron) for cross-platform usage

**Key Tech Stack**: Laravel 11, Livewire 3, NativePHP (Electron), Ollama API, PHP-IMAP, Spatie Google Calendar

## Architecture & Core Components

### 1. Trait-Based Architecture
The codebase uses Laravel traits extensively for separation of concerns:

- **`HandleAiResponse`** ([app/Traits/HandleAiResponse.php](app/Traits/HandleAiResponse.php)): Manages Ollama API communication with configurable server address and auth tokens
- **`HasMailboxConnection`** ([app/Traits/HasMailboxConnection.php](app/Traits/HasMailboxConnection.php)): IMAP connection management, mailbox operations (move, trash, delete)
- **`Processable`** ([app/Traits/Processable.php](app/Traits/Processable.php)): Email processing pipeline - classification, reply generation, event extraction
- **`Calendarable`** ([app/Traits/Calendarable.php](app/Traits/Calendarable.php)): Google Calendar event creation from AI-extracted event data
- **`Helpers`** ([app/Traits/Helpers.php](app/Traits/Helpers.php)): HTML-to-plain-text conversion, web scraping utilities

**Pattern**: Livewire components compose these traits to build feature-complete workflows. Example: `MessageListComponent` uses `Calendarable`, `Processable`, and `Helpers` together.

### 2. Livewire Component Structure
All main UI components live in `app/Livewire/AiReply/`:

- **`MainMailboxAssistantComponent`**: Parent component orchestrating the entire UI
- **`MessageListComponent`**: Displays email messages, handles pagination, triggers AI processing
- **`MailboxConnectionComponent`**: Manages IMAP sync, caches messages, handles mailbox folder selection
- **`ReplyFormComponent`**: Renders AI-generated replies, allows editing before sending
- **`OllamaSettings`**: Configuration UI for Ollama server address and model selection

**Data Flow**: IMAP → Cache → MessageList → AI Processing → Reply/Calendar

### 3. AI Integration Points

#### Ollama Configuration
- **Server Address**: Stored in `settings` table (key: `ollamaServerAddress`) or fallback to `config/responder.php`
- **Two Models Used**:
  1. **Classifier** (`inboxAI:c1`): Categorizes emails into Junk/Trash/Archive/Inbox + determines action (generateReply/insertEvent/summarize)
  2. **Assistant** (default `llama3:latest`): Generates actual email replies with calendar event extraction

#### Available AI Actions
The classifier returns instructions that determine how the assistant processes emails. Three core actions:

1. **`generateReply`**: Generate contextual email replies
   - Used for emails requiring responses
   - Reply stored in `replies` table linked to original message
   - AI signature: "Cheers Fabio - powered by InboxAI"

2. **`insertEvent`**: Create Google Calendar events
   - Extracts event details (date/time, attendees, location) from email content
   - Checks calendar availability before insertion
   - Uses Spatie Google Calendar package
   - Requires proper timezone in AI response

3. **`summarize`**: Summarize newsletters and long threads
   - Used for newsletters, long conversations, or when uncertain about reply
   - Summary stored as reply content

**Note**: Instructions can be arrays (e.g., `["generateReply", "insertEvent"]`) for multi-action processing.

#### Custom Modelfiles
Located at project root as `.example` files (actual `Modelfile` is gitignored):
- **`Modelfile.example`**: Main assistant prompt for reply generation
- **`Modelfile_classification.example`**: Email classification prompt
- **`Modelfile_responder.example`**: Alternative responder configuration

**To create custom models**:
```bash
ollama create inboxAI:c1 -f Modelfile_classification.example
ollama create inboxAI:assistant -f Modelfile.example
```

#### AI Response Format
AI must return JSON with specific structure:
```json
{
  "reply": "Email reply text...",
  "event": false | {
    "summary": "Meeting title",
    "start": {"dateTime": "2024-01-01T09:00:00-07:00", "timeZone": "America/Los_Angeles"},
    "end": {"dateTime": "2024-01-01T10:00:00-07:00", "timeZone": "America/Los_Angeles"},
    "attendees": [{"email": "user@example.com"}]
  }
}
```

### 4. Database Schema Essentials

**Messages** (`messages` table):
- Stores IMAP message metadata: `message_identifier` (IMAP ID), `subject`, `from`, `content`, mailbox flags
- `mailbox_folder`: Tracks which folder message belongs to (INBOX, INBOX.Trash, etc.)
- Relationship: `hasMany(Reply::class)`

**Settings** (`settings` table):
- Key-value store for dynamic config: `ollamaServerAddress`, `filter` (day/week/month), `limit` (pagination)
- Updated via Livewire components on user interaction

**Replies** (`replies` table):
- Links generated AI replies to original messages via `message_id` foreign key
- `response_content`: Stores the AI-generated reply text
- Can be edited before sending via `ReplyFormComponent`

**Urls** (`urls` table):
- Knowledge base storage for web-scraped content
- `url`: Original URL
- `content`: Plain-text extracted content for AI context injection

## Critical Developer Workflows

### Local Development Setup
```bash
# Standard Laravel setup
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Start development servers
npm run dev                    # Vite frontend
php artisan serve              # Laravel backend (or use Herd)
php artisan native:serve       # NativePHP desktop app (development)
```

### NativePHP Desktop App
The app is **fully implemented** using NativePHP/Electron for cross-platform desktop deployment.

**Configuration**: See `app/Providers/NativeAppServiceProvider.php`
```php
// PHP runtime settings for desktop app
'memory_limit' => '512M',
'display_errors' => '1',
'max_execution_time' => '0',  // No timeout for long-running operations
```

**Commands**:
```bash
# Development mode with hot reload
php artisan native:serve

# Build for Windows
php artisan native:build win x64
# Note: Press Ctrl+C if terminal hangs after build completes

# Build for other platforms
php artisan native:build mac         # macOS
php artisan native:build linux       # Linux
```

**Desktop App Features**:
- Full Laravel web interface packaged as desktop app
- Native window management via `Window::open()`
- All Livewire components functional in desktop context
- Offline-capable IMAP sync and AI processing

### Testing
```bash
# Run Pest test suite
php artisan test

# Example test: OllamaSettings component
# tests/Feature/aiReply/OllamaSettingsTest.php
# Tests Livewire validation, settings persistence, connection checks
```

### IMAP Connection Testing
The app uses `php-imap/php-imap` package - ensure PHP IMAP extension is enabled:
```ini
; php.ini
extension=imap
```

Configuration in `.env`:
```env
MAIL_FROM_ADDRESS=your-email@example.com
INBOX_AI_MAIL_PASSWORD=your-app-password
INBOX_AI_MAIL_SERVER=imap.gmail.com
INBOX_AI_MAIL_PORT=993
```

### Ollama Server Setup
```bash
# Install Ollama (see https://ollama.com)
ollama pull llama3.1          # Or gemma2, phi3, granite-code
ollama serve                  # Default: http://127.0.0.1:11434

# For remote/proxy setup (see .env):
OLLAMA_PROXY_SERVER=http://your-server:3001
OLLAMA_PROXY_SERVER_API_TOKEN=your-token
```

## Project-Specific Conventions

### 1. Message Processing Pipeline
When `processInboxMessage($messageId)` is called:
1. **Fetch** message content from IMAP server
2. **Classify** using classifier model → returns category + action
3. **Execute action**:
   - `generateReply`: Call assistant model, store in `replies` table
   - `insertEvent`: Extract event from response, create Google Calendar entry via Spatie
   - `summarize`: Store summary as reply
4. **Move** message to appropriate mailbox folder (INBOX.Archive, INBOX.Junk, etc.)

### 2. Cache Strategy
- **Messages**: Cached forever in `Cache::rememberForever('messages', ...)` 
- **Mailboxes**: Cached list of IMAP folders
- **Purging**: Manual via `refreshMessages()` or `Cache::purge('messages')`

**Why**: IMAP operations are slow; caching reduces server round-trips during UI interactions.

### 3. Livewire Event Communication
Key events dispatched between components:
- `sync-mailbox`: Triggers MailboxConnectionComponent to re-fetch IMAP messages
- `fetch-message`: Opens MessageCardDialog with message details
- `reply-generated`: Triggers Calendarable to process event creation
- `set-reply`: Sends AI-generated reply to ReplyFormComponent for review/editing
- `set-reply-content`: Loads existing reply content by ID

**Email Sending**: After AI generates reply, `ReplyFormComponent` allows editing before sending via `InboxAiReplyMailable` (using Laravel Mail).

### 4. HTML Content Handling
Emails often contain HTML. The `Helpers` trait provides:
```php
convertHtmlToPlainText($html) // Strips tags, normalizes whitespace for AI consumption
getWebPageContent($url)        // Scrapes external URLs (experimental /browse route)
```

### 5. Google Calendar Integration
Uses `spatie/laravel-google-calendar` - requires OAuth credentials in `config/google-calendar.php`.
See [app/Traits/Calendarable.php](app/Traits/Calendarable.php) for event creation logic.

### 6. Knowledge Base (Experimental)
**Component**: `KnowledgeBaseComponent` manages custom knowledge injection into AI context.

**How it works**:
1. Users add URLs via the Knowledge Base UI
2. System scrapes web pages using `wget` (via Symfony Process)
3. HTML converted to plain text via `Helpers::convertHtmlToPlainText()`
4. Content stored in `urls` table (`url`, `content` columns)
5. All knowledge base entries injected into assistant's system prompt during reply generation

**Implementation details**:
```php
// From Processable::preparePayloadFrom()
$knowledgeBase = Url::get(['url', 'content'])->toJson();
// Injected into system message:
"<knowledgeBase>" . implode(' ', Arr::flatten(json_decode($knowledgeBase, true))) . "</knowledgeBase>"
```

**Limitations**: 
- No RAG (Retrieval-Augmented Generation) - entire knowledge base sent with every request
- May hit token limits with large knowledge bases
- Cached in `Cache::forever('links')` for performance

**TODO**: Refactor to use RAG techniques for scalability (see TODO in `Processable.php` line ~386).

### 7. AI Response Validation (Self-Correction)
**Method**: `Processable::aiReviewResponse()` - Experimental AI-powered validation

**Purpose**: Ensures AI responses match expected JSON schema before processing.

**Flow**:
1. Assistant generates response
2. If action is `insertEvent`, validation kicks in
3. Secondary AI call checks response against `config('responder.assistant.json_formats.withEvent')`
4. AI corrects malformed responses using reasoning process
5. Validated response used for calendar event creation

**Why needed**: Some models don't support function calling; this ensures consistent output format.

## Key Files Reference

- **Configuration**: [config/responder.php](config/responder.php) - IMAP, classifier, assistant prompts
- **Routes**: [routes/web.php](routes/web.php) - Dashboard, experimental `/browse` endpoint
- **Main Livewire Entry**: [app/Livewire/AiReply/MainMailboxAssistantComponent.php](app/Livewire/AiReply/MainMailboxAssistantComponent.php)
- **AI Processing Logic**: [app/Traits/Processable.php](app/Traits/Processable.php) (lines 1-451)
- **Models**: [app/Models/Message.php](app/Models/Message.php), [app/Models/Reply.php](app/Models/Reply.php), [app/Models/Setting.php](app/Models/Setting.php)

## Common Pitfalls

1. **IMAP Extension Missing**: Laravel crashes if `php-imap` extension not enabled
2. **Ollama Timeout**: Default 5000ms timeout in `HandleAiResponse::getResponse()` - increase for large models
3. **Message Identifier Type**: Changed from integer to string (see migration `2024_10_11_221051`) - always treat as string
4. **Calendar Timezone**: AI responses must include timezone or Spatie throws exceptions
5. **Modelfile Signature**: Always end replies with `"Cheers Fabio - powered by InboxAI"` (per `Modelfile.example`)

## Quick Start for New Features

1. **Adding AI Capability**: Modify `config/responder.php` → Update Modelfile → Recreate with `ollama create`
2. **New Livewire Component**: Place in `app/Livewire/AiReply/` → Register in MainMailboxAssistantComponent
3. **IMAP Operations**: Use `HasMailboxConnection` trait methods (e.g., `makeMailboxFromSettings()`, `moveMailTo()`)
4. **Testing Livewire**: See `tests/Feature/aiReply/OllamaSettingsTest.php` for component testing patterns
5. **Adding Knowledge Base Entries**: Use `KnowledgeBaseComponent::addLink()` - automatically scrapes and caches content
6. **Custom AI Actions**: Add new instruction types in `config/responder.php` classifier system prompt, then implement handler in `Processable::performActions()`
7. **AI Response Validation**: Use `aiReviewResponse($output, $expectedFormat)` when strict schema compliance needed
