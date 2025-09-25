# DeinDJ Newsletter System

A Symfony-based newsletter management system with click tracking, subscriber management, and admin notifications.

## Features

- 📧 Newsletter subscription management
- 🔗 Click tracking for newsletter links
- 📊 Admin dashboard with metrics
- 🔔 Admin notifications for new subscribers
- 🚫 One-click unsubscribe functionality
- 📱 Responsive email templates

## Requirements

- PHP 8.2 or higher
- Composer
- Symfony CLI
- MySQL/MariaDB or PostgreSQL
- Node.js (for asset management)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/cvtmal/deindjnews-symfony.git
cd deindjnews-symfony
```

2. Install PHP dependencies:
```bash
composer install
```

3. Create `.env.local` file with your local configuration:
```bash
cp .env .env.local
```

4. Configure your database and email settings in `.env.local`:
```env
# Database
DATABASE_URL="mysql://user:password@127.0.0.1:3306/your_database"

# Mailer
MAILER_DSN=smtp://user:pass@smtp.example.com:587

# Admin emails for notifications
ADMIN_EMAILS="admin@example.com"

# App Secret (generate a new one)
APP_SECRET=your_secret_here
```

5. Generate a new APP_SECRET:
```bash
symfony console secrets:generate-keys
```

6. Create the database and run migrations:
```bash
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```

7. Install frontend dependencies:
```bash
symfony console importmap:install
```

8. Start the development server:
```bash
symfony server:start
```

## Usage

### Development Server

Start the Symfony development server:
```bash
symfony server:start
```

Access the application at `https://localhost:8000`

### Available Routes

- `/dashboard` - Admin dashboard (⚠️ Currently no authentication)
- `/newsletter/preview` - Preview newsletter template
- `/track/{email}/{linkName}/{url}` - Click tracking endpoint
- `/unsubscribe/{email}` - Unsubscribe endpoint

### Console Commands

Send a test newsletter:
```bash
symfony console newsletter:test recipient@example.com
```

Send newsletter to a single subscriber:
```bash
symfony console newsletter:send:one recipient@example.com
```

### Database Management

Create a new migration:
```bash
symfony console doctrine:migrations:diff
```

Run migrations:
```bash
symfony console doctrine:migrations:migrate
```

## Project Structure

```
├── config/           # Configuration files
├── migrations/       # Database migrations
├── public/          # Web root
├── src/
│   ├── Command/     # Console commands
│   ├── Controller/  # HTTP controllers
│   ├── Entity/      # Doctrine entities
│   ├── Repository/  # Database repositories
│   └── Service/     # Business logic services
├── templates/       # Twig templates
├── tests/          # Test files
└── translations/   # Translation files
```

## Security Considerations

⚠️ **Important Security Notes:**

1. **Dashboard Authentication**: The admin dashboard currently lacks authentication. Implement authentication before deploying to production.

2. **Unsubscribe Links**: Currently using base64 encoding. Consider implementing signed URLs for better security.

3. **Environment Variables**: Never commit `.env.local` or any file containing real secrets to version control.

4. **CSRF Protection**: Implement CSRF tokens for all state-changing operations.

## Testing

Run the test suite:
```bash
php bin/phpunit
```

Run specific tests:
```bash
php bin/phpunit tests/Controller/DashboardControllerTest.php
```

## Development

### Code Style

This project follows Symfony coding standards. Before committing:

```bash
# Check code style
php vendor/bin/php-cs-fixer fix --dry-run

# Fix code style
php vendor/bin/php-cs-fixer fix
```

### Debugging

Show all services:
```bash
symfony console debug:container
```

Show routes:
```bash
symfony console debug:router
```

## Deployment

For production deployment:

1. Set environment to production in `.env.local`:
```env
APP_ENV=prod
```

2. Install dependencies without dev packages:
```bash
composer install --no-dev --optimize-autoloader
```

3. Clear and warm up cache:
```bash
symfony console cache:clear --env=prod
symfony console cache:warmup --env=prod
```

4. Compile assets:
```bash
symfony console asset-map:compile
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is proprietary software. All rights reserved.

## Support

For support and questions, please contact the development team.

---

**Note**: This application is under active development. Some features may be incomplete or require additional security hardening before production use.