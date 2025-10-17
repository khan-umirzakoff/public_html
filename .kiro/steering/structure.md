---
inclusion: always
---

# Project Structure

## Root Directory Layout

```
BrightBridge/
├── public_html/          # Main Laravel application
├── *.sh                  # Setup and deployment scripts
├── *.sql                 # Database dumps
├── SETUP.md             # Development workflow guide
├── CLAUDE.md            # Technical documentation
└── TODO.md              # Task tracking
```

## Laravel Application Structure (public_html/)

### Core Directories

**app/** - Application logic
- `Http/Controllers/` - Request handlers
  - `IndexController.php` - Main site pages
  - `AIChatController.php` - AI chat streaming endpoint
  - `CVController.php` - Resume generation
  - `DocumentController.php` - AI document management
  - `admin/` - Admin panel controllers (32KB AdminController.php is main)
- `Services/` - Business logic layer
  - `GeminiAIService.php` - Gemini API implementation
  - `OpenAIService.php` - OpenAI API implementation
  - `RAGService.php` - Retrieval-Augmented Generation with semantic search
- `Contracts/` - Interfaces
  - `AIService.php` - AI service contract
- `Observers/` - Model event listeners
  - `AiKnowledgeObserver.php` - Auto-generate embeddings on save
  - `JobObserver.php`, `NewsObserver.php`, `TrainingsObserver.php`
- `Jobs/` - Queue jobs (not actively used)
- `Providers/` - Service providers
  - `AIServiceProvider.php` - Binds active AI provider
  - `AppServiceProvider.php` - Main service provider
- Models (root of app/)
  - `AiKnowledge.php` - Knowledge base with semantic search
  - `AiSetting.php` - AI configuration with caching
  - `Jobs.php`, `News.php`, `Trainings.php` - Content models
  - `User.php`, `Company.php`, `Applications.php`

**config/** - Configuration files
- `ai.php` - AI provider configuration
- `database.php`, `app.php`, `auth.php` - Laravel configs

**database/** - Database files
- `migrations/` - Schema migrations
  - Embedding columns added via migrations (2025_10_06_*)
- `seeds/` - Database seeders
- `factories/` - Model factories

**resources/** - Frontend assets
- `views/` - Blade templates
  - `admin/` - Admin panel views
  - `main2/` - Main site templates
  - `pages/` - Static pages
  - `inc/` - Shared components (header, footer)
- `js/` - JavaScript files
  - `app.js` - Main JS entry point
  - `components/` - Vue components
- `sass/` - SCSS stylesheets

**routes/** - Route definitions
- `web.php` - All web routes (public, user, company, admin, AI)

**public/** - Web root
- `index.php` - Entry point
- `css/`, `js/`, `fonts/`, `img/` - Compiled assets
- `adminsite/` - Admin template assets (AdminLTE)
- `upl/`, `uploaded/`, `uploads/` - User uploads

**storage/** - Application storage
- `app/` - Application files
- `logs/` - Laravel logs
- `framework/` - Framework cache/sessions

## Key Files

### Configuration
- `.env` - Environment variables (NOT in git)
- `.env.example` - Environment template
- `composer.json` - PHP dependencies
- `package.json` - Node dependencies

### Entry Points
- `public/index.php` - Web entry point
- `artisan` - CLI entry point

### Documentation
- `CLAUDE.md` - Comprehensive technical guide
- `SETUP.md` - Development workflow
- `TODO.md` - Task tracking

## Database Schema

### Core Tables
- `users` - Job seeker accounts
- `companies` - Employer accounts
- `jobs` - Job postings (with `embedding` column)
- `applications` - Job applications
- `news` - News articles (with `embedding` column)
- `trainings` - Training programs (with `embedding` column)

### AI Tables
- `ai_knowledge` - Platform knowledge base (with `embedding` column)
- `ai_settings` - AI provider configuration
- `ai_documents` - Uploaded documents (with chunked `embedding` array)

### Important Notes
- All content tables have `embedding` column (LONGTEXT) storing JSON arrays
- `ai_documents` uses array of arrays for chunk-level embeddings
- Other tables use single embedding array per record

## Naming Conventions

- **Database**: snake_case (e.g., `ai_knowledge`, `job_id`)
- **PHP Classes**: PascalCase (e.g., `AiKnowledge`, `RAGService`)
- **PHP Methods**: camelCase (e.g., `searchGeneral`, `getContactInfo`)
- **Routes**: kebab-case (e.g., `/job-details`, `/single-blog`)
- **Views**: kebab-case with dots (e.g., `admin.ai-knowledge`, `pages.job-details`)

## File Permissions

Writable directories (development):
```bash
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

Production permissions:
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```
