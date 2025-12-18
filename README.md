# InboxAI

Use private LLM running locally to manage your inbox. It works with Ollama and open-source models like Meta's Llama3.1, Google's Gemma, IBM's granite-code, Microsoft's phi3 and more.

Visit and download the desired models from the ollama website.

## Features

- [x] Connects with a custom maibox via SMTP
- [x] Connects with the Ollama AI server
- [x] Set AI Assistant and classifier to handles incoming messages
- [x] Process the incoming messages and generate a reply
- [x] Insert a Google Calendar Event if necessary
- [x] Classify the incoming message ()

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
