# PropQuent API Documentation

## Overview
This API provides comprehensive access to the PropQuent property management system with JWT-based authentication and standardized response formats.

## Base URL
```
http://your-domain.com/api
```

## Authentication
The API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header:

```
Authorization: Bearer {your-jwt-token}
```

## Response Format
All API responses follow a standardized format:

### Success Response
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {...},
    "timestamp": "2025-09-19T18:45:00.000Z",
    "meta": {...} // Optional metadata
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "timestamp": "2025-09-19T18:45:00.000Z",
    "errors": {...} // Optional validation errors
}
```

### Paginated Response
```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [...],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75,
        "from": 1,
        "to": 15,
        "has_more_pages": true,
        "links": {
            "first": "...",
            "last": "...",
            "prev": null,
            "next": "..."
        }
    },
    "timestamp": "2025-09-19T18:45:00.000Z"
}
```

## Authentication Endpoints

### POST /auth/login
Login with email and password.

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password123",
    "guard": "web" // Optional: "web" or "admins"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600,
        "user": {
            "id": 1,
            "full_name": "John Doe",
            "email": "user@example.com",
            ...
        }
    }
}
```

### POST /auth/register
Register a new user account.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890"
}
```

### POST /auth/logout
Logout and blacklist the current token.

**Headers:** `Authorization: Bearer {token}`

### POST /auth/refresh
Refresh the current token.

**Headers:** `Authorization: Bearer {token}`

### GET /auth/me
Get current authenticated user information.

**Headers:** `Authorization: Bearer {token}`

### POST /auth/change-password
Change user password.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
    "current_password": "oldpassword",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

## User Management Endpoints

### GET /users
Get paginated list of users with filtering and sorting.

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)
- `search` - Search in name, email, custom_id, phone
- `is_active` - Filter by active status (true/false)
- `email_verified` - Filter by email verification (true/false)
- `country` - Filter by country
- `sort_by` - Sort field (id, full_name, email, created_at, etc.)
- `sort_direction` - Sort direction (asc/desc)

### GET /users/{id}
Get specific user by ID.

### POST /users
Create a new user.

**Request Body:**
```json
{
    "full_name": "John Doe",
    "email": "user@example.com",
    "password_hash": "password123",
    "phone_number": "+1234567890",
    "country": "USA",
    "is_active": true,
    "email_verified": false
}
```

### PUT /users/{id}
Update existing user.

### DELETE /users/{id}
Delete user.

## Project Management Endpoints

### GET /projects
Get paginated list of projects with filtering and sorting.

**Query Parameters:**
- `page` - Page number
- `per_page` - Items per page
- `search` - Search in project_key, title, description, unit, compound
- `status` - Filter by status
- `stage` - Filter by stage
- `type` - Filter by type
- `investment_type` - Filter by investment type
- `developer_id` - Filter by developer
- `sort_by` - Sort field
- `sort_direction` - Sort direction

### GET /projects/{id}
Get specific project by ID.

### POST /projects
Create a new project.

**Request Body:**
```json
{
    "project_key": "PROJ001",
    "title": "Luxury Apartment Complex",
    "description": "Modern luxury apartments...",
    "unit": "Apartment 101",
    "area": 120.5,
    "garden_area": 25.0,
    "compound": "Green Valley",
    "status": "active",
    "stage": "construction",
    "type": "residential",
    "investment_type": "buy_to_let",
    "reservation_date": "2025-01-15",
    "contract_date": "2025-02-01",
    "total_contract_value": 250000.00,
    "years": 5,
    "developer_id": 1,
    "notes": "Premium location..."
}
```

### PUT /projects/{id}
Update existing project.

### DELETE /projects/{id}
Delete project.

## System Endpoints

### GET /health
Public health check endpoint.

### GET /system/info
Get system information (requires authentication).

## Error Codes

- `200` - Success
- `201` - Created
- `204` - No Content
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Rate Limiting
API requests are rate-limited to prevent abuse. Current limits:
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated requests

## Security Features

### JWT Token Blacklist
- Tokens are blacklisted on logout for security
- Expired tokens are automatically cleaned up
- Use `php artisan jwt:cleanup-blacklist` to manually clean expired tokens

### Password Security
- Passwords are hashed using bcrypt
- Minimum 8 characters required
- Password confirmation required for changes

### Input Validation
- All inputs are validated and sanitized
- SQL injection protection
- XSS protection

## Development Tools

### Cleanup Command
```bash
php artisan jwt:cleanup-blacklist
```

### Testing Authentication
```bash
# Login
curl -X POST http://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Use token
curl -X GET http://your-domain.com/api/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Support
For API support, please contact the development team or refer to the system documentation.
