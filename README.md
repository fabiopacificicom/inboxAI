# InboxAI

Use private LLM running locally to manage your inbox. It works with Ollama and open-source models like Meta's Llama3.1, Google's Gemma, IBM's granite-code, Microsoft's phi3 and more. 

Visit and download the desired models from the ollama website.

## Features:
- [x] Connects with a custom maibox via SMTP
- [x] Connects with the Ollama AI server
- [x] Set AI Assistant and classifier to handles incoming messages 
- [x] Process the incoming messages and generate a reply
- [x] Insert a Google Calendar Event if necessary
- [ ] Classify the incoming message (WIP)


## General Requirements:
- Install Ollama in your system, see the download page [here]()
- Install one or more models using the command line, for instance: `ollama pull llama3.1`

## Laravel app requirements:
- Make sure you have installed and enabled the imap extension in your php.ini file

## Windows Desktop app: 
coming soon..


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
- [ ] Add Documentation
- [ ] Classification
- [ ] Automate the process
- [ ] Create a Native windows app (WIP)
- [ ] Create a Docker image
