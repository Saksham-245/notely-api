# Notely API

A modern REST API for managing notes, built with Laravel. This API provides secure endpoints for user authentication and note management.

## Features

- **User Authentication**
  - Register new users
  - Login with secure token-based authentication
  - Update user profile
  - Upload profile pictures
  - Secure logout

- **Note Management**
  - Create, read, update, and delete notes
  - Search functionality for notes
  - Each note contains a title and content
  - Notes are private and user-specific

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login and receive authentication token
- `POST /api/auth/logout` - Logout and invalidate token

### User Management
- `PUT /api/user/update` - Update user profile
- `POST /api/user/upload` - Upload profile picture

### Notes
- `GET /api/notes` - Get all notes for authenticated user
- `POST /api/notes` - Create a new note
- `GET /api/notes/{id}` - Get a specific note
- `PUT /api/notes/{id}` - Update a note
- `DELETE /api/notes/{id}` - Delete a note
- `GET /api/notes/search` - Search through notes

## Security
- All endpoints (except login and register) require authentication
- Uses Laravel Sanctum for token-based authentication
- Secure password hashing
- User-specific data isolation

## Getting Started

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy `.env.example` to `.env` and configure your database
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Run migrations:
   ```bash
   php artisan migrate
   ```
6. Start the development server:
   ```bash
   php artisan serve
   ```
