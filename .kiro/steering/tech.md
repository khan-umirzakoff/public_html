---
inclusion: always
---

# Technology Stack

## Backend
- **PHP 7.4** with **Laravel 5.8**
- **MySQL** database
- **Composer** for dependency management
- Document processing: FPDF, PHPWord, PDF parser

## Frontend
- **Blade** templating engine
- **Vue.js 2.5** for reactive components
- **Bootstrap 4** for UI
- **jQuery** for DOM manipulation
- **Laravel Mix** for asset compilation

## AI Integrationmac
- **OpenAI API** (GPT models + embeddings)
- **Google Gemini API** (Gemini models + gemini-embedding-001)
- Provider-agnostic service pattern with runtimmace switching
- Vector embeddings stored as JSON in MySQL LONGTEXT columns

## Development Environment
- **Nix** for reproducible development environment
- Local development via `php artisan serve`
- No Docker/containerization currently used

## Common Commands

### Initial Setup
```bash
# From repository root
./run_without_sudo.sh  # Preferred setup method
# OR
./run.sh               # Alternative with system packages

# Manual setup (from public_html directory)
cd public_html
php composer.phar install --no-interaction --prefer-dist --optimize-autoloader
php artisan key:generate
php artisan config:cache
php artisan migrate
```

### Development Server
```bash
cd public_html
php artisan serve              # Start at localhost:8000
php artisan serve --port=8080  # Custom port
```

### Database Operations
```bash
php artisan migrate                    # Run migrations
php artisan migrate:fresh              # Fresh migration
mysql -u root -p database < ../brightbr_job.sql  # Import SQL dump
```

### Cache Management
```bash
php artisan config:clear   # Clear config cache
php artisan route:clear    # Clear route cache
php artisan view:clear     # Clear view cache
php artisan cache:clear    # Clear application cache
```

### Asset Compilation
```bash
npm run dev          # Development build
npm run watch        # Watch mode
npm run production   # Production build (minified)
```

### Testing
```bash
vendor/bin/phpunit                    # Run all tests
vendor/bin/phpunit --filter TestName  # Run specific test
```

## Important Notes

- Always run `php artisan config:clear` after changing AI provider settings
- Storage and cache directories need write permissions: `chmod -R 777 storage bootstrap/cache`
- Environment variables must include at least one AI API key (GEMINI_API_KEY or OPENAI_API_KEY)
- No queue system implemented - embedding generation is synchronous
