# InboxAI - Product Roadmap

> **Vision**: Transform InboxAI from a single-account AI assistant into a comprehensive, AI-powered email management system that replaces traditional email clients with intelligent automation and unified communication management.

## Current State Assessment

### ✅ What We Have (v0.9)
- Single IMAP account connection
- AI-powered email classification (Junk/Archive/Inbox/Trash)
- Automated reply generation with 3 AI models (Llama, Gemma, Phi3)
- Google Calendar event extraction and insertion
- Newsletter summarization
- Knowledge Base (experimental - web scraping)
- Cross-platform desktop app (NativePHP/Electron)
- Trait-based architecture for code reuse
- Livewire-driven reactive UI

### ⚠️ Current Limitations
- **Single Account**: Only one IMAP account supported
- **No Unified Inbox**: Can't manage multiple email sources
- **Limited Interaction**: No conversational interface with AI
- **No Local Archive**: Messages stored temporarily in cache
- **No Cross-Account Analysis**: AI processes messages individually
- **No RAG**: Knowledge Base loads entire content (token limit issues)
- **Manual Processing**: User triggers AI actions manually

---

## Product Vision: The AI-First Email Client

InboxAI will become a **conversational email management system** where:
- Users manage **multiple email accounts** from one interface
- An **AI Agent** acts as a personal assistant with **tooling access**
- Users interact via **natural language chat** instead of traditional UI
- System provides **proactive insights** (critical emails, summaries, trends)
- Local archiving enables **offline access** and **long-term storage**

---

## Development Roadmap

### Phase 1: Multi-Account Foundation (Q1 2026)
**Goal**: Support multiple IMAP accounts with unified inbox view

#### 1.1 Database Schema Evolution
**Effort**: 2-3 days

```php
// New migration: create_accounts_table
Schema::create('accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name'); // User-friendly label
    $table->string('email')->unique();
    $table->string('imap_host');
    $table->integer('imap_port')->default(993);
    $table->string('imap_encryption')->default('ssl');
    $table->text('imap_password'); // Encrypted
    $table->string('smtp_host')->nullable();
    $table->integer('smtp_port')->nullable();
    $table->text('smtp_password')->nullable(); // Encrypted
    $table->boolean('is_active')->default(true);
    $table->json('settings')->nullable(); // Account-specific AI config
    $table->timestamps();
});

// Update messages table
Schema::table('messages', function (Blueprint $table) {
    $table->foreignId('account_id')->after('id')->constrained()->onDelete('cascade');
    $table->index(['account_id', 'mailbox_folder']);
});
```

**Models**:
- `Account` model with `hasMany(Message::class)`
- `Message` model update: `belongsTo(Account::class)`
- Encryption for credentials using Laravel `Crypt`

**Changes**:
- Refactor `HasMailboxConnection` trait to accept `Account` model
- Update settings table to store per-account configurations
- Multi-tenancy pattern: filter all queries by `account_id`

#### 1.2 Account Management UI
**Effort**: 3-4 days

**New Livewire Components**:
```
app/Livewire/Accounts/
├── AccountListComponent.php      # Display all accounts
├── AccountFormComponent.php      # Add/edit account (IMAP/SMTP)
├── AccountTestComponent.php      # Test connection before saving
└── AccountSwitcherComponent.php  # Quick account dropdown
```

**Features**:
- CRUD for email accounts
- Connection validation (test IMAP before save)
- Credential encryption/decryption
- Default account designation
- Enable/disable accounts without deletion

**UI Flow**:
```
Settings > Accounts > [+ Add Account]
├── Email address
├── IMAP Settings (host, port, encryption)
├── SMTP Settings (optional for sending)
├── [Test Connection] button
└── [Save Account]
```

#### 1.3 Unified Inbox View
**Effort**: 4-5 days

**New Component**: `UnifiedInboxComponent`
- Fetches messages from **all active accounts**
- Displays with account badge/color coding
- Filter by account (dropdown or sidebar)
- Sort by date across all accounts

**Cache Strategy Update**:
```php
// Current: Cache::get('messages')
// New: Cache::get("messages:account:{$accountId}")
// Unified: Cache::get("messages:unified:user:{$userId}")
```

**Query Pattern**:
```php
// Fetch messages across accounts
$messages = Message::whereHas('account', function($q) use ($userId) {
    $q->where('user_id', $userId)->where('is_active', true);
})
->with('account:id,name,email')
->orderBy('date', 'desc')
->paginate(50);
```

---

### Phase 2: Conversational AI Interface (Q2 2026)
**Goal**: Replace traditional UI with chat-based interaction

#### 2.1 Chat Infrastructure
**Effort**: 5-7 days

**Database**:
```php
Schema::create('conversations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('title')->nullable(); // Auto-generated from first message
    $table->json('context')->nullable(); // Conversation memory
    $table->timestamps();
});

Schema::create('chat_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
    $table->enum('role', ['user', 'assistant', 'system']);
    $table->text('content');
    $table->json('tool_calls')->nullable(); // Track which tools were used
    $table->json('metadata')->nullable(); // Account IDs, message IDs referenced
    $table->timestamps();
});
```

**New Livewire Components**:
```
app/Livewire/Chat/
├── ChatWindowComponent.php       # Main chat UI
├── MessageInputComponent.php     # User input with suggestions
├── ConversationListComponent.php # Sidebar with chat history
└── ToolCallDisplayComponent.php  # Show tool execution results
```

**Chat UI Pattern**:
```blade
<!-- Chat Window -->
<div class="chat-container">
    <!-- Conversation history -->
    @foreach($messages as $msg)
        <div class="message {{ $msg->role }}">
            {{ $msg->content }}
            @if($msg->tool_calls)
                <div class="tool-execution">
                    🔧 {{ $msg->tool_calls['tool_name'] }}
                </div>
            @endif
        </div>
    @endforeach
    
    <!-- Input -->
    <textarea wire:model="userInput" 
              placeholder="Ask me anything about your emails...">
    </textarea>
</div>
```

#### 2.2 AI Agent with Function Calling
**Effort**: 7-10 days

**New Trait**: `app/Traits/HasToolAccess.php`

**Available Tools**:
```php
protected $tools = [
    [
        'type' => 'function',
        'function' => [
            'name' => 'search_emails',
            'description' => 'Search emails across all accounts or specific account',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'description' => 'Search term'],
                    'account_id' => ['type' => 'integer', 'description' => 'Optional account filter'],
                    'date_range' => ['type' => 'string', 'description' => 'today/week/month/year'],
                    'folder' => ['type' => 'string', 'description' => 'inbox/sent/archive']
                ],
                'required' => ['query']
            ]
        ]
    ],
    [
        'type' => 'function',
        'function' => [
            'name' => 'get_critical_emails',
            'description' => 'Find emails marked as important or urgent',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'account_ids' => ['type' => 'array', 'description' => 'Accounts to check'],
                    'days' => ['type' => 'integer', 'description' => 'Look back N days']
                ]
            ]
        ]
    ],
    [
        'type' => 'function',
        'function' => [
            'name' => 'summarize_account_activity',
            'description' => 'Get summary of emails received on account(s)',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'account_ids' => ['type' => 'array'],
                    'period' => ['type' => 'string', 'description' => 'day/week/month']
                ],
                'required' => ['period']
            ]
        ]
    ],
    [
        'type' => 'function',
        'function' => [
            'name' => 'compose_reply',
            'description' => 'Draft a reply to an email',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'message_id' => ['type' => 'integer'],
                    'tone' => ['type' => 'string', 'description' => 'formal/casual/brief']
                ],
                'required' => ['message_id']
            ]
        ]
    ],
    [
        'type' => 'function',
        'function' => [
            'name' => 'archive_messages',
            'description' => 'Move messages to local archive',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'message_ids' => ['type' => 'array'],
                    'delete_from_server' => ['type' => 'boolean']
                ],
                'required' => ['message_ids']
            ]
        ]
    ]
];
```

**Tool Execution Pattern**:
```php
// In HasToolAccess trait
private function executeToolCall($toolName, $arguments)
{
    Log::info('🔧 Tool execution', ['tool' => $toolName, 'args' => $arguments]);
    
    return match($toolName) {
        'search_emails' => $this->searchEmails($arguments),
        'get_critical_emails' => $this->getCriticalEmails($arguments),
        'summarize_account_activity' => $this->summarizeActivity($arguments),
        'compose_reply' => $this->composeReply($arguments),
        'archive_messages' => $this->archiveMessages($arguments),
        default => throw new \Exception("Unknown tool: {$toolName}")
    };
}
```

#### 2.3 Natural Language Interactions
**Effort**: 3-4 days

**Example User Queries**:
- "Summarize important emails from my work account today"
- "Show me all emails from john@example.com in the last week"
- "Draft a reply to the latest email from Sarah saying I'll review by Friday"
- "Which emails need urgent attention across all accounts?"
- "Archive all newsletters older than 30 days"

**AI Agent System Prompt**:
```text
You are InboxAI, an intelligent email assistant managing multiple email accounts for the user.

You have access to tools for:
- Searching emails across accounts
- Finding critical/urgent messages
- Generating summaries
- Drafting replies
- Archiving messages

When the user asks about their emails:
1. Determine which tool(s) to use
2. Call tools with appropriate parameters
3. Present results conversationally
4. Offer follow-up actions

Always specify which account you're referencing when results come from multiple sources.
```

---

### Phase 3: Local Archiving System (Q2-Q3 2026)
**Goal**: Enable offline access and long-term local storage

#### 3.1 Archive Storage Infrastructure
**Effort**: 5-6 days

**Database**:
```php
Schema::create('archived_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('account_id')->constrained()->onDelete('cascade');
    $table->string('original_message_id'); // IMAP ID before archiving
    $table->string('subject');
    $table->string('from');
    $table->string('to');
    $table->json('cc')->nullable();
    $table->json('bcc')->nullable();
    $table->datetime('sent_at');
    $table->longText('content_html')->nullable();
    $table->longText('content_plain')->nullable();
    $table->json('attachments')->nullable(); // Store metadata
    $table->boolean('deleted_from_server')->default(false);
    $table->timestamps();
    
    $table->index(['user_id', 'account_id', 'sent_at']);
    $table->fulltext(['subject', 'content_plain']); // Fast search
});

Schema::create('archived_attachments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('archived_message_id')->constrained()->onDelete('cascade');
    $table->string('filename');
    $table->string('mime_type');
    $table->bigInteger('size_bytes');
    $table->string('storage_path'); // Local file path
    $table->string('checksum'); // SHA256 for integrity
    $table->timestamps();
});
```

**File Storage Pattern**:
```
storage/app/archives/
├── user_{id}/
│   ├── account_{id}/
│   │   ├── 2026/
│   │   │   ├── 01/  # Month
│   │   │   │   ├── message_{id}/
│   │   │   │   │   ├── content.html
│   │   │   │   │   ├── content.txt
│   │   │   │   │   └── attachments/
│   │   │   │   │       ├── file1.pdf
│   │   │   │   │       └── file2.jpg
```

#### 3.2 Archive Management Features
**Effort**: 4-5 days

**New Components**:
```
app/Livewire/Archive/
├── ArchiveManagerComponent.php    # Main interface
├── ArchiveSearchComponent.php     # Search archived emails
├── ArchiveStatsComponent.php      # Storage stats, counts
└── ArchiveExportComponent.php     # Export to .mbox, .eml formats
```

**Features**:
- **Selective Archiving**: Choose messages to archive
- **Bulk Operations**: Archive by criteria (date range, sender, folder)
- **Server Cleanup**: Option to delete from IMAP after archiving
- **Attachment Handling**: Download and store locally
- **Full-Text Search**: Search archived messages offline
- **Export**: Export archives in standard formats (.mbox, .eml, .pst)

**Archive Job Pattern**:
```php
// app/Jobs/ArchiveMessagesJob.php
class ArchiveMessagesJob implements ShouldQueue
{
    public function handle()
    {
        foreach ($this->messageIds as $id) {
            $message = Message::with('account')->find($id);
            
            // 1. Fetch full content from IMAP
            $fullContent = $this->fetchFullMessage($message);
            
            // 2. Download attachments
            $attachments = $this->downloadAttachments($fullContent);
            
            // 3. Store in archive table
            $archived = ArchivedMessage::create([...]);
            
            // 4. Store attachments
            foreach ($attachments as $att) {
                ArchivedAttachment::create([...]);
            }
            
            // 5. Optionally delete from server
            if ($this->deleteFromServer) {
                $this->deleteFromImap($message);
            }
            
            Log::info('📦 Message archived', ['id' => $id]);
        }
    }
}
```

#### 3.3 Archive Intelligence
**Effort**: 3-4 days

**AI-Powered Archive Suggestions**:
- Detect newsletters (auto-suggest archiving)
- Identify old conversations (no activity in 90+ days)
- Flag large attachments for space management
- Recommend retention policies per account

**New AI Tool**:
```php
[
    'name' => 'suggest_archive_candidates',
    'description' => 'Find emails that can be safely archived',
    'parameters' => [
        'account_id' => ['type' => 'integer'],
        'criteria' => ['type' => 'string', 'enum' => ['old', 'newsletters', 'large_files']]
    ]
]
```

---

### Phase 4: Advanced AI Capabilities (Q3 2026)
**Goal**: Proactive assistance and intelligent automation

#### 4.1 RAG Implementation for Knowledge Base
**Effort**: 6-8 days

**Replace current knowledge injection** with proper RAG:

**Technology Stack**:
- **Vector DB**: Qdrant or Chroma (local deployment)
- **Embeddings**: Ollama's `nomic-embed-text` model
- **Chunking**: LangChain or custom chunker

**Architecture**:
```php
// app/Services/RagService.php
class RagService
{
    public function addDocument($url, $content)
    {
        // 1. Chunk content
        $chunks = $this->chunkText($content, maxTokens: 512);
        
        // 2. Generate embeddings
        foreach ($chunks as $chunk) {
            $embedding = $this->generateEmbedding($chunk);
            
            // 3. Store in vector DB
            VectorStore::create([
                'url' => $url,
                'chunk_index' => $index,
                'content' => $chunk,
                'embedding' => $embedding
            ]);
        }
    }
    
    public function retrieveContext($query, $topK = 3)
    {
        $queryEmbedding = $this->generateEmbedding($query);
        
        // Similarity search
        return VectorStore::nearestNeighbors($queryEmbedding, $topK);
    }
}
```

**Integration**:
```php
// In Processable::preparePayloadFrom()
// OLD: Inject entire knowledge base
// NEW: Retrieve only relevant chunks
$relevantContext = $this->ragService->retrieveContext($message['subject'] . ' ' . $message['content']);
$contextStr = implode("\n\n", array_column($relevantContext, 'content'));
```

#### 4.2 Proactive Insights
**Effort**: 5-6 days

**Background Job**: `GenerateInsightsJob` (runs daily)

**Insights Generated**:
1. **Critical Email Alerts**: "You have 3 urgent emails from your manager"
2. **Follow-up Reminders**: "You haven't replied to John's email from 3 days ago"
3. **Deadline Tracking**: "Meeting request from Sarah expires in 2 hours"
4. **Communication Patterns**: "You receive 60% more emails on Mondays"
5. **Unread Accumulation**: "Your personal account has 150 unread emails"

**Storage**:
```php
Schema::create('insights', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('type', ['critical', 'reminder', 'pattern', 'statistic']);
    $table->string('title');
    $table->text('description');
    $table->json('related_items')->nullable(); // Message IDs, account IDs
    $table->boolean('is_read')->default(false);
    $table->boolean('is_actionable')->default(false);
    $table->datetime('expires_at')->nullable();
    $table->timestamps();
});
```

**Display**: Dashboard widget + chat notifications
```
💡 Insights (3 new)
├── 🔴 Critical: 2 urgent emails need attention
├── 📅 Reminder: Follow up on project proposal
└── 📊 Pattern: Email volume increased 30% this week
```

#### 4.3 Smart Automation Rules
**Effort**: 7-9 days

**User-Defined Rules Engine**:
```php
Schema::create('automation_rules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('account_id')->nullable(); // Null = all accounts
    $table->string('name');
    $table->json('conditions'); // Match criteria
    $table->json('actions'); // What to do
    $table->boolean('is_active')->default(true);
    $table->integer('execution_count')->default(0);
    $table->timestamps();
});
```

**Example Rules**:
```json
// Auto-archive newsletters
{
    "name": "Archive Newsletters",
    "conditions": {
        "from_contains": ["newsletter", "noreply"],
        "subject_contains": ["unsubscribe"]
    },
    "actions": {
        "move_to": "INBOX.Archive",
        "mark_as_read": true
    }
}

// Urgent email alerts
{
    "name": "Alert on Boss Emails",
    "conditions": {
        "from": "boss@company.com",
        "importance": "high"
    },
    "actions": {
        "notify": "desktop",
        "flag": true,
        "add_to_insight": "critical"
    }
}
```

**Rule Execution**: Triggered after classification phase

---

### Phase 5: Performance & UX Polish (Q4 2026)
**Goal**: Production-ready, scalable, delightful UX

#### 5.1 Performance Optimizations
**Effort**: 5-7 days

**Database**:
- Add compound indexes for common queries
- Implement database sharding for multi-tenant scale
- Use read replicas for analytics queries

**Caching**:
- Redis for distributed caching (replace Laravel file cache)
- Cache conversation context (reduce AI calls)
- Implement cache warming on login

**Background Processing**:
- Queue all IMAP operations (don't block UI)
- Batch AI calls (process multiple messages together)
- Lazy load attachments (on-demand download)

**Query Optimization**:
```php
// Before: N+1 queries
$messages = Message::all();
foreach ($messages as $msg) {
    echo $msg->account->name; // N queries
}

// After: Eager loading
$messages = Message::with('account:id,name,email')->get();
```

#### 5.2 Advanced UI/UX
**Effort**: 6-8 days

**Features**:
- **Keyboard Shortcuts**: Gmail-like navigation (j/k, c, r, a)
- **Drag & Drop**: Move emails between folders/accounts
- **Bulk Selection**: Select multiple emails with checkboxes
- **Quick Actions Bar**: Persistent toolbar (archive, delete, reply)
- **Dark Mode**: Respect system preference
- **Email Previews**: Hover to see email content
- **Thread View**: Group conversation threads
- **Customizable Sidebar**: Pin favorite folders
- **Search Autocomplete**: Suggest senders, subjects
- **Attachment Previews**: Images, PDFs inline

**Accessibility**:
- ARIA labels for screen readers
- Keyboard-only navigation support
- High contrast mode
- Font size adjustments

#### 5.3 Mobile Companion App
**Effort**: 15-20 days (separate initiative)

**Technology**: Flutter or React Native

**Features** (Phase 1):
- View unified inbox
- Read emails
- Archive/delete messages
- Chat with AI agent
- Push notifications for critical emails

**Features** (Phase 2):
- Compose/reply to emails
- Attachment handling
- Offline mode
- Biometric authentication

---

### Phase 6: Enterprise Features (2027)
**Goal**: Team collaboration and advanced integrations

#### 6.1 Team/Organization Support
- Shared inboxes (support@, sales@)
- User roles and permissions
- Team insights and analytics
- Collaborative tagging/labeling

#### 6.2 Advanced Integrations
- **CRM**: Salesforce, HubSpot sync
- **Project Management**: Jira, Asana, Trello
- **Cloud Storage**: Google Drive, Dropbox, OneDrive
- **Communication**: Slack, Teams notifications
- **Zapier/Make**: Webhook automations

#### 6.3 Custom AI Models
- Fine-tune models per user/organization
- Industry-specific templates (legal, medical, sales)
- Multi-language support
- Custom classification categories

---

## Technical Debt & Refactoring

### Immediate (During Phase 1-2)
- [ ] Extract IMAP logic from traits into dedicated service class
- [ ] Replace `Cache::forever()` with Redis + TTL strategy
- [ ] Add retry logic for failed AI calls
- [ ] Implement proper encryption for account credentials
- [ ] Add comprehensive error logging (not just `Log::error`)

### Mid-term (During Phase 3-4)
- [ ] Migrate to RAG for knowledge base (remove token limit issues)
- [ ] Add database query monitoring (Laravel Telescope)
- [ ] Implement background job monitoring (Horizon)
- [ ] Add rate limiting for API calls (Ollama)
- [ ] Create admin panel for system health monitoring

### Long-term (Phase 5+)
- [ ] Migrate from SQLite to PostgreSQL (better full-text search)
- [ ] Implement event sourcing for audit trails
- [ ] Add multi-region support (global deployment)
- [ ] Create API for third-party integrations
- [ ] Open-source core components (encourage community contributions)

---

## Success Metrics

### Phase 1 KPIs
- [ ] 5+ email accounts per user (average)
- [ ] 95% IMAP connection success rate
- [ ] <2s unified inbox load time

### Phase 2 KPIs
- [ ] 80% of users interact via chat (vs. traditional UI)
- [ ] Average 10+ AI tool calls per user per day
- [ ] 90% user satisfaction with AI responses

### Phase 3 KPIs
- [ ] 50GB average archived data per user
- [ ] 70% of users enable auto-archiving rules
- [ ] <1s full-text search response time

### Phase 4 KPIs
- [ ] 5+ proactive insights generated per user daily
- [ ] 30% reduction in manual email management time
- [ ] 95% accuracy in critical email detection

### Phase 5 KPIs
- [ ] <500ms average page load time
- [ ] Support 10,000+ concurrent users
- [ ] 99.9% uptime SLA

---

## Resource Requirements

### Development Team (Recommended)
- **1 Full-Stack Developer** (Laravel + Livewire + AI integration)
- **1 Frontend Developer** (UI/UX focus, Phase 5)
- **1 Mobile Developer** (Flutter/React Native, Phase 5+)
- **1 DevOps Engineer** (Part-time, scaling & deployment)

### Infrastructure Costs (Estimated Monthly)
- **Phase 1-2**: $50-100 (single server + Ollama)
- **Phase 3-4**: $200-400 (database scaling + storage)
- **Phase 5**: $500-1000 (multi-server + Redis + backups)
- **Phase 6**: $2000+ (enterprise-grade infrastructure)

### Timeline Summary
| Phase | Duration | Effort (Days) | Milestones |
|-------|----------|---------------|------------|
| Phase 1 | Q1 2026 | 15-20 | Multi-account support, Unified inbox |
| Phase 2 | Q2 2026 | 20-25 | Chat interface, AI agent with tools |
| Phase 3 | Q2-Q3 2026 | 15-18 | Local archiving, offline access |
| Phase 4 | Q3 2026 | 20-25 | RAG, proactive insights, automation |
| Phase 5 | Q4 2026 | 30-40 | Performance, UX polish, mobile app |
| Phase 6 | 2027 | 60+ | Enterprise features, integrations |

**Total Estimated**: 160-228 development days (~8-11 months for Phases 1-5)

---

## Next Steps (Immediate Actions)

### Week 1-2: Planning & Setup
1. **Create GitHub project board** with Phase 1 tasks
2. **Design database schema** for `accounts` and `archived_messages` tables
3. **Sketch UI mockups** for account management and unified inbox
4. **Set up test IMAP accounts** (Gmail, Outlook, custom)

### Week 3-4: Phase 1 Kickoff
1. **Implement `Account` model** and migrations
2. **Update `Message` relationship** to `Account`
3. **Refactor `HasMailboxConnection`** to support multiple accounts
4. **Build `AccountFormComponent`** for adding accounts

### Month 2: Unified Inbox MVP
1. **Create `UnifiedInboxComponent`**
2. **Implement account switcher**
3. **Update cache strategy** for multi-account
4. **Test with 3+ email accounts** simultaneously

### Month 3: Chat Foundation
1. **Design conversation database schema**
2. **Build basic chat UI** (Livewire component)
3. **Integrate first AI tool** (search_emails)
4. **Test conversational queries**

---

## Open Questions & Decisions Needed

1. **Authentication**: Multi-user support from Day 1, or single-user first?
   - *Recommendation*: Start single-user, add multi-tenancy in Phase 5

2. **Encryption**: Which library for credential encryption?
   - *Recommendation*: Laravel's built-in `Crypt` facade

3. **Vector DB**: Self-hosted vs. cloud (Pinecone, Weaviate)?
   - *Recommendation*: Qdrant (self-hosted) for data privacy

4. **Mobile**: Native apps or PWA?
   - *Recommendation*: Flutter (cross-platform native)

5. **Monetization**: Free tier + paid plans?
   - *Recommendation*: Freemium (1 account free, unlimited paid)

---

## Conclusion

This roadmap transforms InboxAI from a single-account AI assistant into a **comprehensive, AI-first email management platform**. By following a phased approach:

1. **Phase 1-2** establishes the multi-account foundation and conversational interface
2. **Phase 3-4** adds intelligence (archiving, RAG, proactive insights)
3. **Phase 5-6** scales for production and enterprise use

**Key Differentiators**:
- **AI-Native**: Built around conversational AI, not bolted on
- **Privacy-First**: Local Ollama deployment, no cloud AI dependencies
- **Unified Experience**: Single interface for all email accounts
- **Proactive**: AI suggests actions before user asks

**The vision is clear**: Replace traditional email clients with an intelligent assistant that not only manages emails but understands context, predicts needs, and automates mundane tasks—all while keeping data local and private.
