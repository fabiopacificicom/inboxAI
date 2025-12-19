# InboxAI

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/fabiopacificicom/inboxAI/releases/tag/v1.0.0)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Use private LLM running locally to manage your inbox. It works with Ollama and open-source models like Meta's Llama3.1, Google's Gemma, IBM's granite-code, Microsoft's phi3 and more.

Visit and download the desired models from the ollama website.

📋 **[View Changelog](CHANGELOG.md)** | 🚀 **[Latest Release](https://github.com/fabiopacificicom/inboxAI/releases/tag/v1.0.0)**

## Features

### Core Email Management
- [x] **Multi-Account Support** - Manage multiple IMAP/SMTP email accounts from one interface
- [x] **Unified Inbox** - Centralized view across all connected accounts
- [x] AI-powered email classification (Junk/Archive/Inbox/Trash)
- [x] Automated reply generation with AI
- [x] Insert Google Calendar events from email content
- [x] Newsletter and email thread summarization

### AI Integration
- [x] Connects with Ollama AI server for local LLM processing
- [x] **Conversational AI Interface** - Chat with your AI assistant in natural language
- [x] Configurable AI Assistant and classifier models
- [x] Knowledge Base with web scraping (experimental)
- [x] Support for multiple models (Llama3.1, Gemma, Phi3, Granite Code)

### User Experience
- [x] **Profile Picture Upload** - Personalize your account
- [x] Account switcher for quick navigation
- [x] Cross-platform desktop app (Windows/macOS/Linux via NativePHP/Electron)
- [x] Reactive UI with Livewire 3

## General Requirements

- Install Ollama in your system, see the download page [here]()
- Install one or more models using the command line, for instance: `ollama pull llama3.1`

## Laravel app requirements

- Make sure you have installed and enabled the imap extension in your php.ini file

## Desktop Application

✅ **Fully Implemented** - Cross-platform desktop app using NativePHP/Electron

The desktop version provides the full InboxAI experience as a native application for Windows, macOS, and Linux.

### Building the Desktop App

```bash
# Development mode
php artisan native:serve

# Production builds
php artisan native:build win x64   # Windows
php artisan native:build mac        # macOS  
php artisan native:build linux      # Linux
```

**Note**: If the terminal appears stuck after building, press Ctrl+C to exit - the build is complete.

## Local Development

### Laravel web app

- clone the repository
- run `composer install`
- run `npm run dev`
- copy the `.env.example` file and name it `.env`
- run `php artisan key:generate`
- run `php artisan migrate`

### Native php desktop app

start the native server for local development

```bash
php artisan native:serve 
```

## build for a specific platform

Build for windows

```bash
php artisan native:build win x64
# press ctrl+c if the terminal is stuck
```

## Roadmap

### Completed ✅
- [x] IMAP mailbox connection
- [x] Ollama AI integration
- [x] Email classification system
- [x] AI reply generation
- [x] Google Calendar event creation
- [x] Email summarization
- [x] Knowledge Base (experimental)
- [x] NativePHP desktop app (Windows/macOS/Linux)

### In Progress 🚧
- [ ] Comprehensive documentation
- [ ] Process automation (scheduled background tasks)
- [ ] Docker image for easy deployment
- [ ] RAG implementation for scalable Knowledge Base
- [ ] Multi-language support

### Planned 📋
- [ ] Email threading support
- [ ] Advanced filtering and rules engine
- [ ] Multiple mailbox accounts
- [ ] AI model fine-tuning interface
- [ ] Mobile app (iOS/Android)
