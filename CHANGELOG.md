# Changelog

All notable changes to InboxAI will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-18

### Added - Phase 2: Conversational AI & Multi-Account Support

#### Multi-Account Management
- **Account Management System**: Full CRUD operations for managing multiple IMAP/SMTP email accounts
  - `AccountListComponent`: Display and manage all connected email accounts
  - `AccountFormComponent`: Add and edit email account configurations
  - `AccountTestComponent`: Validate IMAP/SMTP connections before saving
  - `AccountSwitcherComponent`: Quick switch between multiple email accounts
- **Database Schema**: New `accounts` table with encrypted credential storage
- **Account Migration**: Automated migration of existing single-account IMAP configuration to multi-account system
- **Account-Scoped Messages**: Messages now linked to specific accounts via `account_id` foreign key

#### Conversational AI Interface
- **Chat Window Component**: Interactive conversational interface for AI assistance
  - Natural language interaction with InboxAI assistant
  - Context-aware responses based on email content and history
- **Conversation Management**: New database tables for managing chat sessions
  - `conversations` table: Track conversation threads
  - `chat_messages` table: Store individual chat messages
- **Tool Access for AI**: `HasToolAccess` trait enabling AI agents to interact with email functions

#### User Experience Enhancements
- **Profile Picture Upload**: Users can now upload custom profile pictures
- **Unified Inbox Component**: Centralized view for managing emails across all accounts
- **Enhanced Message Processing**: Improved AI processing pipeline with better error handling

#### Developer Experience
- **Comprehensive Documentation**: Added detailed copilot instructions and project guidelines
- **ROADMAP.md**: Added product roadmap outlining future development phases
- **Code Workspace Configuration**: Added VSCode workspace settings for better development experience

### Changed
- **Message Schema**: Updated messages table to support multi-account architecture
- **Settings Management**: Enhanced settings system for per-account configurations
- **Trait Architecture**: Refactored `HasMailboxConnection` trait to work with Account model

### Fixed
- **Message Identifier Type**: Changed from integer to string to support various IMAP implementations
- **IMAP Connection Handling**: Improved connection stability and error handling

### Database Migrations
- `2025_12_18_011648_create_accounts_table.php`: Account management system
- `2025_12_18_011659_add_account_id_to_messages_table.php`: Multi-account support for messages
- `2025_12_18_014213_migrate_existing_imap_config_to_account.php`: Legacy config migration (stub)
- `2025_12_18_014216_migrate_existing_imap_config_to_account.php`: Full migration implementation
- `2025_12_18_020606_create_conversations_table.php`: Conversation tracking
- `2025_12_18_020610_create_chat_messages_table.php`: Chat message storage

## [0.9.0] - 2024-10-11 (Pre-Phase 2)

### Features
- IMAP mailbox connection and management
- Ollama AI integration with local LLM support
- Email classification system (Junk/Archive/Inbox/Trash)
- Automated reply generation using AI models (Llama3, Gemma, Phi3)
- Google Calendar event extraction and insertion from emails
- Newsletter and email thread summarization
- Knowledge Base (experimental web scraping feature)
- Cross-platform desktop application using NativePHP/Electron
- Trait-based architecture for code reuse
- Livewire 3 reactive UI components

### Supported AI Models
- Meta's Llama 3.1
- Google's Gemma 2
- Microsoft's Phi 3
- IBM's Granite Code
- Custom models via Ollama

### Platform Support
- Windows (x64)
- macOS (Intel/Apple Silicon)
- Linux
- Web application (Laravel)

---

## Version Naming Convention

- **Major version** (X.0.0): Breaking changes or significant architectural updates
- **Minor version** (0.X.0): New features and functionality
- **Patch version** (0.0.X): Bug fixes and minor improvements

## Links

- [Repository](https://github.com/fabiopacificicom/inboxAI)
- [Issues](https://github.com/fabiopacificicom/inboxAI/issues)
- [Ollama](https://ollama.com)

[1.0.0]: https://github.com/fabiopacificicom/inboxAI/releases/tag/v1.0.0
[0.9.0]: https://github.com/fabiopacificicom/inboxAI/releases/tag/v0.9.0
