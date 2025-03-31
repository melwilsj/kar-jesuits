# Jesuit ERP System

A comprehensive Enterprise Resource Planning (ERP) system designed specifically for Jesuit institutions, featuring both web and mobile interfaces for efficient member management and reporting.

## 🌟 Features

- **Multi-Platform Support**
  - Web interface for administrators and users
  - Mobile apps for Android and iOS
  - RESTful API backend

- **Authentication & Security**
  - Google Sign-in integration
  - Phone number authentication via Firebase
  - Role-Based Access Control (RBAC)
  - Secure API endpoints

- **Core Functionalities**
  - Member profile management
  - Community assignment tracking
  - Comprehensive reporting system
  - Annual catalogue/prospectus generation (PDF)
  - File storage and management

## 🛠️ Technology Stack

### Backend
- **Framework:** Laravel
- **Database:** PostgreSQL
- **Storage:** Cloudflare R2
- **Authentication:** 
  - Google OAuth
  - Firebase Phone Auth
  - Laravel Sanctum

### Frontend
- **Web:** Laravel Blade + Livewire
- **Mobile:** React Native

### Infrastructure
- **Hosting:** Railway
- **Storage:** Cloudflare R2
- **Version Control:** Git (Single repository with orphan branches)

## 🏗️ Repository Structure

The project uses a single repository with two main branches:

### Web Platform (`main`, `main-dev`)
- Laravel API Server
- Admin Interface
- User Interface
- PDF Generation
- Database Management

### Mobile Platform (`mobile`, `mobile-dev`)
- React Native Mobile Application
- API Integration
- User Interface
- Authentication

## 🚀 Getting Started

### Prerequisites
- PHP 8.1+
- Composer
- Node.js & npm
- React Native SDK
- PostgreSQL
- Git

### Installation

1. Clone the repository
```bash
git clone https://github.com/melwilsj/kar-jesuits
cd kar-jesuits
```

2. Web Platform Setup
```bash
# Switch to web branch
git checkout main

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate
```

3. Mobile Platform Setup
```bash
# Switch to mobile branch
git checkout mobile

# Install dependencies
npm install

# Configure environment
cp .env.example .env
```

## 📱 Mobile Development
```bash
npx expo start
```

## 🌐 Web Development
```bash
# Start Laravel server
php artisan serve

# Watch for asset changes
npm run dev
```

## 📄 Documentation

Detailed documentation for API endpoints, database schema, and deployment procedures can be found in the `/docs` directory.

## 🔐 Environment Configuration

Required environment variables:

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

# Cloudflare R2
CLOUDFLARE_ACCESS_KEY_ID=
CLOUDFLARE_SECRET_ACCESS_KEY=
CLOUDFLARE_DEFAULT_REGION=
CLOUDFLARE_BUCKET=

# Authentication
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
FIREBASE_API_KEY=
```

## 🤝 Contributing

1. Create a new branch from `main-dev` or `mobile-dev`
2. Make your changes
3. Submit a pull request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Team

- [Melwils SJ - Project Lead](https://github.com/melwilsj)

## 📞 Support

For support, email [melwilsj@jesuits.net] or create an issue in the repository.