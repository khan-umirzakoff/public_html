# BrightBridge JobCare

Laravel-based job portal platform for the Uzbek market with AI-powered assistant.

## Quick Start

### Local Development

```bash
# 1. Clone repository
git clone https://github.com/khan-umirzakoff/public_html.git
cd public_html

# 2. Setup environment
cp .env.example .env
# Edit .env with your local database settings

# 3. Install dependencies
php composer.phar install

# 4. Generate app key
php artisan key:generate

# 5. Run migrations
php artisan migrate

# 6. Start development server
php artisan serve
```

Visit: http://localhost:8000

### Production Deployment

```bash
cd ~/public_html
git pull origin main
./deploy.sh
```

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed instructions.

## Features

- 🔍 Job search and applications
- 🏢 Company profiles
- 📰 News and blog system
- 📚 Training/course listings
- 🤖 AI-powered chatbot with RAG
- 📄 Document upload and semantic search
- 🌐 Multi-language support (Uzbek/Russian)

## Tech Stack

- **Backend:** PHP 7.4, Laravel 5.8, MySQL
- **Frontend:** Blade, Vue.js 2.5, Bootstrap 4
- **AI:** OpenAI API, Google Gemini API
- **Development:** XAMPP/LAMPP, Composer

## Project Structure

```
public_html/
├── app/                    # Application logic
│   ├── Http/Controllers/   # Request handlers
│   ├── Services/          # Business logic (AI, RAG)
│   └── Models/            # Database models
├── config/                # Configuration files
├── database/              # Migrations and seeds
├── resources/             # Views, JS, CSS
├── routes/                # Route definitions
├── storage/               # Logs, cache, uploads
├── public/                # Web root
├── deploy.sh              # Deployment script
├── DEPLOYMENT.md          # Deployment guide
└── .env.example           # Environment template
```

## Environment Configuration

### Local (.env)
```bash
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_DATABASE=brightbridge_local
```

### Production (.env)
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://brightbridge.uz
DB_DATABASE=brightbr_job
```

See `.env.example` for all available settings.

## AI Configuration

The platform supports both OpenAI and Google Gemini:

```bash
# Choose provider
AI_PROVIDER=gemini  # or "openai"

# Gemini settings
GEMINI_API_KEY=your_key_here
GEMINI_MODEL=gemini-flash-latest

# OpenAI settings
OPENAI_API_KEY=your_key_here
OPENAI_MODEL=gpt-4o
```

## Common Commands

```bash
# Development server
php artisan serve

# Database
php artisan migrate
php artisan migrate:status

# Cache management
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Optimization
php artisan config:cache
php artisan route:cache
```

## Testing

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test
vendor/bin/phpunit --filter TestName
```

## Deployment History

**Latest deployment:** Successfully deployed on production server
- ✅ All migrations completed
- ✅ Dependencies updated (41 packages)
- ✅ Cache optimized
- ✅ Application live at https://brightbridge.uz

## Troubleshooting

See [DEPLOYMENT.md](DEPLOYMENT.md) for common issues and solutions.

## Documentation

- [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment guide
- [CLAUDE.md](CLAUDE.md) - Technical documentation (if exists)
- [TODO.md](TODO.md) - Task tracking (if exists)

## License

Proprietary - BrightBridge JobCare Platform

## Support

For issues or questions, check the logs:
```bash
tail -f storage/logs/laravel.log
```
