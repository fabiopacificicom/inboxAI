# InboxAI - Project Guidelines

> **For AI Agents**: This document defines coding patterns, architectural conventions, and best practices specific to the InboxAI project. Follow these guidelines when making changes or adding new features.

## Table of Contents
- [Architectural Principles](#architectural-principles)
- [Trait-Based Design Patterns](#trait-based-design-patterns)
- [Livewire Component Conventions](#livewire-component-conventions)
- [Database & Model Patterns](#database--model-patterns)
- [AI Integration Patterns](#ai-integration-patterns)
- [Logging & Debugging](#logging--debugging)
- [Testing Requirements](#testing-requirements)
- [File Organization](#file-organization)
- [Common Workflows](#common-workflows)

---

## Architectural Principles

### 1. Trait Composition Over Inheritance
InboxAI uses **trait composition** as its primary architectural pattern. Never create deep class hierarchies; instead, compose functionality using traits.

**Pattern**:
```php
class MessageListComponent extends Component
{
    use Calendarable, Processable, Helpers;
    
    // Component-specific logic only
}
```

**Rationale**: Traits enable horizontal code reuse across Livewire components without coupling to a base class hierarchy.

### 2. Configuration-Driven AI Behavior
AI prompts, models, and response formats are **stored in database** (`settings` table) with **config file fallbacks**.

**Pattern**:
```php
$model = Setting::where('key', 'selectedModel')->first()?->value 
    ?? config('responder.assistant.model');
```

**Why**: Allows runtime configuration changes without code deployment.

### 3. Settings Management Pattern
All user-configurable options stored as key-value pairs in `settings` table.

**Pattern**:
```php
// In Livewire components
public function updated($name, $value)
{
    Setting::updateOrCreate(['key' => $name], ['value' => $value]);
}
```

**Standard Settings Keys**:
- `ollamaServerAddress`: Ollama API endpoint
- `selectedModel`: AI assistant model name
- `selectedClassifier`: Classification model name
- `assistantSystem`: Assistant system prompt
- `classifierSystem`: Classifier system prompt
- `filter`: Message time filter (day/week/month)
- `limit`: Pagination limit

---

## Trait-Based Design Patterns

### Creating New Traits

**Location**: `app/Traits/`

**Naming Convention**: Descriptive adjectives ending in `-able` or `-Response`
- Good: `Calendarable`, `Processable`, `HandleAiResponse`
- Avoid: `HelperTrait`, `UtilityFunctions`

**Structure Pattern**:
```php
<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait YourTraitName
{
    // Public methods (component API)
    public function publicMethod()
    {
        Log::info('🔧 PublicMethod called');
        return $this->privateHelper();
    }
    
    // Private methods (internal logic)
    private function privateHelper()
    {
        // Implementation
    }
}
```

### Existing Trait Responsibilities

**Do NOT mix concerns** - each trait has a single, well-defined responsibility:

| Trait | Purpose | Key Methods |
|-------|---------|-------------|
| `HandleAiResponse` | Ollama API communication | `getResponse($payload)` |
| `HasMailboxConnection` | IMAP operations | `makeMailboxFromSettings()`, `moveMailTo()` |
| `Processable` | Email processing pipeline | `classify()`, `performActions()`, `generateReply()` |
| `Calendarable` | Google Calendar integration | `updateCalendar()`, `checkCalendarAvailability()` |
| `Helpers` | Utility functions | `convertHtmlToPlainText()`, `getWebPageContent()` |

**When to Create a New Trait**:
- Logic is reused across 2+ Livewire components
- Functionality is cohesive and independently testable
- Trait name clearly describes its single responsibility

**When to Add to Existing Trait**:
- New method directly relates to existing trait's responsibility
- Method uses trait's private helper methods

---

## Livewire Component Conventions

### Component Structure

**Location**: `app/Livewire/AiReply/` (feature-grouped)

**Required Methods**:
```php
class YourComponent extends Component
{
    // 1. Properties (public = reactive, used in views)
    public $messages = [];
    public $settings;
    
    // 2. Mount (initialization)
    public function mount($settings)
    {
        $this->settings = $settings;
        // Load from config/database
    }
    
    // 3. Render (view binding)
    public function render()
    {
        return view('livewire.ai-reply.your-component');
    }
    
    // 4. Updated hook (setting persistence)
    public function updated($name, $value)
    {
        Setting::updateOrCreate(['key' => $name], ['value' => $value]);
    }
}
```

### Livewire Event Communication

**Use Livewire events for component communication**, not direct method calls.

**Dispatching Events**:
```php
// Dispatch to specific component
$this->dispatch('event-name', $data)->to(TargetComponent::class);

// Dispatch globally
$this->dispatch('event-name', $data);
```

**Listening to Events**:
```php
use Livewire\Attributes\On;

#[On('event-name')]
public function handleEvent($data)
{
    // Handle event
}
```

**Standard Events**:
- `sync-mailbox`: Trigger IMAP re-sync
- `fetch-message`: Open message details dialog
- `reply-generated`: Process AI-generated reply
- `set-reply`: Pass reply to form component
- `set-reply-content`: Load existing reply by ID

### Component Naming Pattern

**Files**: `ComponentNameComponent.php` (always end with `Component`)
**Classes**: `ComponentNameComponent extends Component`
**Views**: `kebab-case-component.blade.php`

**Examples**:
- `MessageListComponent` → `message-list-component.blade.php`
- `OllamaSettings` → `ollama-settings.blade.php`

### Property Validation

**Use Livewire validation attributes**:
```php
use Livewire\Attributes\Validate;

#[Validate('required|email')]
public $email;

#[Validate('required|min:5')]
public $content;
```

**Or traditional rules array**:
```php
protected $rules = [
    'host' => 'required',
    'port' => 'required|numeric',
];

public function save()
{
    $this->validate();
    // Process...
}
```

---

## Database & Model Patterns

### Migration Conventions

**Field Types**:
- Use `string()` for text fields with known max length
- Use `text()` for HTML content, long messages
- Use `json()` for arrays/objects (e.g., `reply_to_addresses`)
- Use `boolean()` for flags (default to 0)
- Always include `timestamps()`

**Example Pattern**:
```php
Schema::create('table_name', function (Blueprint $table) {
    $table->id();
    $table->string('identifier')->unique()->index();
    $table->string('name', 255);
    $table->text('content')->nullable();
    $table->json('metadata')->nullable();
    $table->boolean('is_active')->default(0);
    $table->timestamps();
});
```

### Model Conventions

**Fillable Pattern**: Always define `$fillable` array
```php
protected $fillable = [
    'message_identifier',
    'subject',
    'from',
    'content',
    'mailbox_folder'
];
```

**Casts Pattern**: Cast JSON fields to arrays
```php
protected function casts(): array
{
    return [
        'reply_to_addresses' => 'array',
        'metadata' => 'array',
    ];
}
```

**Relationships**: Use descriptive names
```php
public function replies()
{
    return $this->hasMany(Reply::class);
}

public function message()
{
    return $this->belongsTo(Message::class);
}
```

### Critical Database Rules

1. **Message Identifiers**: Always `string` type (changed from int in migration `2024_10_11_221051`)
2. **Mailbox Folder**: Store full IMAP path (e.g., `INBOX.Archive`, not just `Archive`)
3. **Settings Table**: Use `updateOrCreate` pattern, never manual inserts
4. **Cache Keys**: Use descriptive names (`messages`, `mailboxes`, `links`)

---

## AI Integration Patterns

### Adding New AI Actions

**1. Update Classifier Prompt** in `config/responder.php`:
```php
'classifier' => [
    'system' => '...
    - instructions: specify one of: generateReply, insertEvent, summarize, yourNewAction
    
    Instructions details:
    - yourNewAction: when to use this action
    ...'
]
```

**2. Implement Handler** in `Processable` trait:
```php
private function performActions($action, $instructions, $messageId, $settings = null): string
{
    // Handle your new action
    if ($instructions === 'yourNewAction' || 
        (is_array($instructions) && in_array('yourNewAction', $instructions))) {
        $this->handleYourAction($messageId);
    }
    
    // Existing logic...
}
```

**3. Add Logging** with emojis for visual grep:
```php
Log::info('🆕 YourAction processing', ['messageId' => $messageId]);
```

### AI Response Format Requirements

**All AI responses must be valid JSON** with expected keys:
```json
{
  "reply": "string - always present",
  "event": false | { /* Google Calendar event object */ }
}
```

**Self-Correction Pattern**: For critical responses, use `aiReviewResponse()`:
```php
$decoded = json_decode($aiResponse, true);
$expected = config('responder.assistant.json_formats.withEvent');
$validated = $this->aiReviewResponse($decoded, $expected);
```

### Ollama API Call Pattern

**Always use trait method**:
```php
use HandleAiResponse;

$payload = [
    'model' => $model,
    'stream' => false,
    'format' => 'json',  // Enforce JSON responses
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userMessage]
    ]
];

$response = $this->getResponse($payload);
```

**Error Handling**:
```php
try {
    $resp = $this->getResponse($payload);
} catch (\Throwable $th) {
    Log::error('❌ AI call failed', ['error' => $th->getMessage()]);
    return back()->with('error', 'AI service unavailable');
}
```

---

## Logging & Debugging

### Logging Standards

**Use structured logging with emojis** for easy log filtering:

```php
// Step markers
Log::info('1️⃣ SetMessage', ['id' => $id]);
Log::info('2️⃣ Classification starting');
Log::info('3️⃣ Extract data from response');

// Status indicators
Log::info('✅ Classification complete');
Log::error('❌ Classification failed', $errorData);
Log::info('👉 Next step: categorize');

// Feature identifiers
Log::info('📅 Calendar event processing');
Log::info('🤖 AI reply generated');
Log::info('🎯 Message categorized', ['category' => $category]);
```

**Benefits**:
- `grep` for specific workflow steps: `grep "1️⃣" app.log`
- Filter by status: `grep "✅" app.log`
- Track feature execution: `grep "📅" app.log`

### Debug Information to Log

**Always include**:
- Operation being performed
- Key identifiers (message ID, user ID)
- Relevant data (category, instructions, etc.)

**Example**:
```php
Log::info('Processing message', [
    'messageId' => $id,
    'subject' => $message['subject'],
    'instructions' => $instructions
]);
```

### Processing Status Updates

**For long-running operations**, maintain `$processingMessages` array:
```php
public $processingMessages = [];

private function classify($message)
{
    $this->processingMessages[] = ['✅' => 'Message classified successfully'];
    // Continue...
}
```

Display in views:
```blade
@foreach($processingMessages as $msg)
    <div>{{ key($msg) }}: {{ current($msg) }}</div>
@endforeach
```

---

## Testing Requirements

### Livewire Component Tests

**Location**: `tests/Feature/aiReply/`

**Pattern**:
```php
use Livewire\Livewire;
use App\Models\User;

it('renders correctly on the page', function () {
    $user = User::factory()->create();
    Livewire::actingAs($user);
    
    $this->get('/dashboard')
        ->assertSeeLivewire(YourComponent::class);
})->group('your-feature');

it('updates settings when value changes', function () {
    $livewire = Livewire::test(YourComponent::class, [
        'property' => 'value'
    ])
    ->set('property', 'new value')
    ->call('save');
    
    expect(Setting::where('key', 'property')->first()->value)
        ->toBe('new value');
});
```

### Test Organization

**Use Pest test groups**:
```php
it('test description', function () {
    // Test logic
})->group('settings', 'ai-integration');
```

**Run specific groups**:
```bash
php artisan test --group=settings
php artisan test --group=ai-integration
```

### What to Test

**Required**:
- Component renders without errors
- Settings persist to database
- Event dispatching works
- Validation rules trigger correctly

**Optional but recommended**:
- AI response parsing
- IMAP connection handling
- Calendar event creation

---

## File Organization

### Directory Structure

```
app/
├── Http/Controllers/         # Minimal - mostly Livewire
├── Livewire/
│   ├── AiReply/             # Feature-grouped components
│   │   ├── MainMailboxAssistantComponent.php
│   │   ├── MessageListComponent.php
│   │   └── ...
│   └── *.php                # Shared components
├── Mail/
│   └── InboxAiReplyMailable.php
├── Models/
│   ├── Message.php
│   ├── Reply.php
│   ├── Setting.php
│   └── Url.php
├── Traits/                  # Core business logic
│   ├── Calendarable.php
│   ├── HandleAiResponse.php
│   ├── HasMailboxConnection.php
│   ├── Helpers.php
│   └── Processable.php
└── Providers/
    ├── AppServiceProvider.php
    └── NativeAppServiceProvider.php

resources/
├── views/
│   ├── livewire/
│   │   ├── ai-reply/        # Match component namespace
│   │   │   ├── main-mailbox-assistant-component.blade.php
│   │   │   └── ...
│   │   └── *.blade.php
│   └── partials/            # Reusable view fragments
│       ├── mailbox/
│       └── message/

config/
└── responder.php            # AI prompts, IMAP settings

database/
└── migrations/
    └── YYYY_MM_DD_HHMMSS_descriptive_name.php
```

### Naming Conventions Summary

| Type | Pattern | Example |
|------|---------|---------|
| Livewire Component | `FeatureNameComponent.php` | `MessageListComponent.php` |
| Blade View | `kebab-case-component.blade.php` | `message-list-component.blade.php` |
| Trait | `DescriptiveAdjective.php` | `Processable.php` |
| Model | `SingularNoun.php` | `Message.php` |
| Migration | `YYYY_MM_DD_create_table_name_table.php` | `2024_09_11_create_messages_table.php` |
| Config | `lowercase.php` | `responder.php` |

---

## Common Workflows

### 1. Adding a New Livewire Component

```bash
# 1. Create component
php artisan make:livewire AiReply/YourComponent

# 2. Implement required methods (mount, render, updated)
# 3. Add traits if needed (use Processable, Helpers, etc.)
# 4. Create corresponding blade view
# 5. Register in parent component (MainMailboxAssistantComponent)
# 6. Write Pest test in tests/Feature/aiReply/
```

**Example Registration**:
```blade
<!-- In parent component view -->
@livewire('ai-reply.your-component', [
    'settings' => $settings
])
```

### 2. Adding a New Setting

```php
// 1. Add to config/responder.php as fallback
'your_setting' => env('YOUR_SETTING', 'default_value'),

// 2. In component mount():
$this->yourSetting = $settings['yourSetting'] 
    ?? config('responder.your_setting');

// 3. Add updated() hook:
public function updated($name, $value)
{
    Setting::updateOrCreate(['key' => $name], ['value' => $value]);
}

// 4. In view:
<input wire:model.blur="yourSetting" />
```

### 3. Extending the AI Processing Pipeline

**Current Flow**: `setMessage` → `classify` → `performActions` → `generateReply`

**To Add a Step**:
```php
// In Processable trait
private function performActions($action, $instructions, $messageId, $settings = null): string
{
    Log::info('4️⃣ performActions', ['instructions' => $instructions]);
    
    // Add your step here
    $preprocessed = $this->yourNewStep($messageId);
    
    // Existing flow continues
    $this->reply[$messageId] = $this->generateReply($instructions);
    // ...
}

private function yourNewStep($messageId)
{
    Log::info('🔧 YourNewStep processing', ['messageId' => $messageId]);
    // Your logic
    $this->processingMessages[] = ['✅' => 'Your step completed'];
}
```

### 4. Working with Knowledge Base

```php
// Add URL to knowledge base
$url = Url::create([
    'url' => $link,
    'content' => $this->readWebPageFromUrl($link)
]);

// Knowledge is auto-injected in preparePayloadFrom()
// See Processable::preparePayloadFrom() line ~386
```

### 5. Building Desktop App

```bash
# Development
php artisan native:serve

# Production build
php artisan native:build win x64
# Press Ctrl+C if terminal hangs after "Build complete"

# Other platforms
php artisan native:build mac
php artisan native:build linux
```

---

## Code Review Checklist

Before submitting changes, verify:

- [ ] **Traits**: Single responsibility, no mixed concerns
- [ ] **Logging**: Emoji markers + structured data
- [ ] **Settings**: Persist to database via `updateOrCreate`
- [ ] **Livewire**: Events used for component communication
- [ ] **AI Responses**: JSON format validated, errors handled
- [ ] **Database**: Migrations follow naming conventions
- [ ] **Tests**: Pest tests in correct group, passing
- [ ] **Cache**: Keys descriptive, purge strategy clear
- [ ] **Views**: Match component namespace path
- [ ] **Config**: Fallbacks defined for all settings

---

## Anti-Patterns to Avoid

❌ **Don't**:
- Create God components (use traits)
- Hardcode AI prompts in code (use config/database)
- Direct component method calls (use events)
- Forget to log errors with context
- Skip validation on AI responses
- Mix IMAP/AI/Calendar logic in one method
- Use integers for message identifiers (they're strings!)
- Cache without purge strategy

✅ **Do**:
- Compose traits for reusable logic
- Store prompts in `settings` table
- Dispatch Livewire events
- Log with emoji markers + context
- Validate AI JSON before processing
- Keep methods focused (single responsibility)
- Treat message IDs as strings
- Document cache invalidation in code comments

---

## Getting Help

**Before asking**:
1. Check `.github/copilot-instructions.md` for architecture overview
2. Review relevant trait source code
3. Search logs for emoji markers
4. Check existing tests for patterns

**When reporting issues**:
- Include relevant log output (with emoji markers)
- Show AI payload/response if relevant
- Specify component and trait involved
- Note which instruction/action triggered issue
