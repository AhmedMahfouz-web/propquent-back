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

## Authentication Endpoints

### 1. Register User
**POST** `/api/auth/register`

Register a new user account.

**Request Body:**
```json
{
    "full_name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "country": "United States",
    "phone_number": "+1234567890"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "user": {
            "id": 1,
            "full_name": "John Doe",
            "email": "john@example.com",
            "custom_id": "inv-1",
            "phone_number": "+1234567890",
            "country": "United States",
            "is_active": true,
            "email_verified": false,
            "created_at": "2024-10-04T06:56:24.000000Z"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

### 2. Login User
**POST** `/api/auth/login`

Authenticate user and get access token.

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "full_name": "John Doe",
            "email": "john@example.com",
            "custom_id": "inv-1",
            "last_login_at": "2024-10-04T06:56:24.000000Z"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

### 3. Forgot Password
**POST** `/api/auth/forgot-password`

Request password reset token.

**Request Body:**
```json
{
    "email": "john@example.com"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Password reset token generated",
    "data": {
        "reset_token": "a1b2c3d4e5f6...",
        "expires_at": "2024-10-04T07:56:24.000000Z"
    }
}
```

### 4. Reset Password
**POST** `/api/auth/reset-password/{token}`

Reset password using the token from forgot password. The token is passed as a URL parameter.

**URL Parameters:**
- `token` (string, required): The password reset token received from forgot password

**Request Body:**
```json
{
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Example URL:**
```
POST /api/auth/reset-password/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
```

**Response:**
```json
{
    "success": true,
    "message": "Password reset successful"
}
```

### 5. Logout (Protected)
**POST** `/api/auth/logout`

Logout user and blacklist the JWT token for security.

**Headers:**
```
Authorization: Bearer {your-jwt-token}
```

**Response:**
```json
{
    "success": true,
    "message": "Logout successful - token has been blacklisted"
}
```

**Security Note:** The token is immediately blacklisted and cannot be used again, preventing unauthorized access even if the token is compromised.

## Dashboard Endpoints

### 6. Get Home Dashboard (Protected)
**GET** `/api/home/dashboard`

Get comprehensive financial dashboard data for the authenticated user.

**Headers:**
```
Authorization: Bearer {your-jwt-token}
```

**Query Parameters (Optional):**
- `start_date` (string): Start date for historical data only (format: YYYY-MM-DD)
- `end_date` (string): End date for historical data only (format: YYYY-MM-DD)
- `per_page` (integer): Number of transactions per page (default: 10)
- `transactions_page` (integer): Page number for transactions (default: 1)

**Note:** The main financial summary always shows current month data. Date parameters only affect historical data for charts.

**Example Request:**
```
GET /api/home/dashboard?start_date=2023-01-01&end_date=2024-10-04&per_page=10&transactions_page=1
```

**Response:**
```json
{
    "success": true,
    "message": "Dashboard data retrieved successfully",
    "data": {
        "user": {
            "id": 1,
            "full_name": "John Doe",
            "custom_id": "inv-1",
            "email": "john@example.com"
        },
        "financial_summary": {
            "capital_investment": {
                "amount": 50000.00,
                "change_percent": 15.5,
                "currency": "USD"
            },
            "profit": {
                "total": 8500.00,
                "this_month": 1200.00,
                "asset_profit": 5200.00,
                "operation_profit": 3300.00,
                "currency": "USD"
            },
            "roi": {
                "percentage": 17.0,
                "description": "Return on Investment"
            },
            "deposits_withdrawals": {
                "net_deposit": 45000.00,
                "total_deposits": 50000.00,
                "total_withdrawals": 5000.00,
                "currency": "USD"
            }
        },
        "historical_data": [
            {
                "month": "2024-01",
                "month_name": "Jan 2024",
                "capital_investment": 30000.00,
                "profit": 2500.00
            },
            {
                "month": "2024-02",
                "month_name": "Feb 2024",
                "capital_investment": 35000.00,
                "profit": 4200.00
            }
        ],
        "recent_transactions": {
            "data": [
                {
                    "id": 1,
                    "transaction_type": "deposit",
                    "amount": 10000.00,
                    "method": "bank_transfer",
                    "status": "completed",
                    "transaction_date": "2024-10-01",
                    "reference_no": "TXN001",
                    "note": "Initial investment",
                    "created_at": "2024-10-01T10:00:00.000000Z"
                }
            ],
            "pagination": {
                "current_page": 1,
                "per_page": 10,
                "total": 25,
                "has_more": true,
                "next_page": 2
            }
        },
        "date_range": {
            "start_date": "2023-01-01",
            "end_date": "2024-10-04",
            "months_count": 21
        }
    }
}
```

**Financial Metrics Explained:**
- **Capital Investment**: Total deposits made by the user with month-over-month change percentage (always current month)
- **Total Profit**: Previous month user equity × Current month total projects profit
- **This Month Profit**: Same as total profit (current month calculation)
- **Asset Profit**: Previous month user equity × Current month asset projects profit
- **Operation Profit**: Previous month user equity × Current month operation projects profit
- **ROI**: Return on Investment percentage (profit / investment * 100)
- **Net Deposit**: Total deposits minus total withdrawals (all-time)
- **Historical Data**: Monthly capital investment and profit for last 12 months (or custom date range)
- **Recent Transactions**: Last 10 transactions with pagination support

**Profit Calculation Formula:**
- **User Equity**: User's deposits ÷ Total deposits from all users
- **Projects Profit**: Project revenue - Project expenses (from ProjectTransaction table)
- **Asset/Operation Split**: Based on 'serving' field in project transactions

**Important Notes:**
- Main financial summary always reflects current month data (no date filtering)
- Only historical data respects the start_date and end_date parameters
- Historical data shows capital and profit progression over time for charts

### 7. Get Projects List with Financial Data (Protected)
**GET** `/api/projects-list`

Get comprehensive projects list with user's financial data for each project.

**Headers:**
```
Authorization: Bearer {your-jwt-token}
```

**Example Request:**
```
GET /api/projects-list
```

**Response:**
```json
{
    "success": true,
    "message": "Projects list retrieved successfully",
    "data": {
        "user": {
            "id": 1,
            "full_name": "John Doe",
            "custom_id": "inv-1",
            "equity_percentage": 25.5
        },
        "financial_summary": {
            "total_asset_value": 150000.00,
            "total_non_exited_projects_amount": 200000.00,
            "currency": "USD"
        },
        "projects": [
            {
                "id": 1,
                "key": "proj-001",
                "title": "Downtown Apartment Complex",
                "description": "Luxury apartment complex in downtown area",
                "unit": "A-101",
                "area": 120.5,
                "garden_area": 25.0,
                "compound": "Green Valley",
                "status": "active",
                "stage": "construction",
                "type": "residential",
                "investment_type": "asset",
                "reservation_date": "2024-01-15",
                "contract_date": "2024-02-01",
                "total_contract_value": 250000.00,
                "years": 5,
                "notes": "Premium location project",
                "developer": {
                    "id": 1,
                    "name": "ABC Development"
                },
                "financial_data": {
                    "project_revenue": 300000.00,
                    "project_expenses": 180000.00,
                    "project_net_revenue": 120000.00,
                    "project_total_profit": 120000.00,
                    "user_invested_amount": 30600.00,
                    "user_profit_from_project": 30600.00,
                    "currency": "USD"
                },
                "created_at": "2024-01-01T10:00:00.000000Z",
                "updated_at": "2024-10-04T07:30:00.000000Z"
            }
        ],
        "projects_count": 1
    }
}
```

**Financial Calculations Explained:**
- **User Equity Percentage**: User's total deposits ÷ Total deposits from all users × 100
- **User Invested Amount**: User's equity percentage × Project's net revenue
- **User Profit from Project**: User's equity percentage × Project's total profit
- **Total Asset Value**: Sum of (asset revenue - asset expenses) from all projects
- **Total Non-Exited Projects Amount**: Sum of net revenue from all non-exited projects

**Project Financial Data:**
- **Project Revenue**: Sum of all revenue transactions for the project
- **Project Expenses**: Sum of all expense transactions for the project
- **Project Net Revenue**: Project revenue minus project expenses
- **Project Total Profit**: Currently same as net revenue (can be customized)

**Important Note:**
Projects can only be created, updated, or deleted through the Filament admin panel. The API only supports reading project data. Any attempts to POST, PUT, PATCH, or DELETE projects through the API will return a 405 Method Not Allowed error.

### 8. Get User Profile (Protected)
**GET** `/api/profile`

Get authenticated user's profile with financial metrics and recent transactions.

**Note:** Users can only be created through the registration endpoint. The POST `/api/users` endpoint is disabled.

**Headers:**
```
Authorization: Bearer {your-jwt-token}
```

**Query Parameters (Optional):**
- `per_page` (integer): Number of transactions per page (default: 10)
- `transactions_page` (integer): Page number for transactions (default: 1)

**Example Request:**
```
GET /api/profile?per_page=10&transactions_page=1
```

**Response:**
```json
{
    "success": true,
    "message": "Profile data retrieved successfully",
    "data": {
        "user": {
            "id": 1,
            "full_name": "John Doe",
            "custom_id": "inv-1",
            "email": "john@example.com",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-10-04T08:00:00.000000Z"
        },
        "financial_metrics": {
            "deposits": 50000.00,
            "withdrawals": 5000.00,
            "equity": 45000.00,
            "equity_percentage": 25.5,
            "total_profit": 8500.00,
            "profit_asset": 5200.00,
            "profit_operation": 3300.00,
            "currency": "USD"
        },
        "recent_transactions": {
            "data": [
                {
                    "id": 1,
                    "transaction_type": "deposit",
                    "amount": 10000.00,
                    "method": "bank_transfer",
                    "status": "completed",
                    "transaction_date": "2024-10-01",
                    "reference_no": "TXN001",
                    "note": "Initial investment",
                    "created_at": "2024-10-01T10:00:00.000000Z"
                }
            ],
            "pagination": {
                "current_page": 1,
                "per_page": 10,
                "total": 25,
                "has_more": true,
                "next_page": 2
            }
        }
    }
}
```

**Financial Metrics Explained:**
- **Deposits**: Total completed deposit transactions
- **Withdrawals**: Total completed withdrawal transactions
- **Equity**: Net investment (deposits - withdrawals)
- **Equity Percentage**: User's ownership percentage in the company
- **Total Profit**: Previous month equity × Current month total projects profit
- **Profit Asset**: Previous month equity × Current month asset projects profit
- **Profit Operation**: Previous month equity × Current month operation projects profit

**Transactions Pagination:**
- Use `per_page` to control number of transactions returned
- Use `transactions_page` to load more transactions
- Check `has_more` to determine if more transactions exist
- Use `next_page` value for the next request

### 9. Update User Profile (Protected)
**PUT/PATCH** `/api/users/{id}`

Update authenticated user's profile. Users can only update their own profile.

**Headers:**
```
Authorization: Bearer {your-jwt-token}
```

**Request Body:**
```json
{
    "full_name": "John Doe Updated",
    "email": "john.updated@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123",
    "phone_number": "+1234567890",
    "country": "USA",
    "profile_picture_url": "https://example.com/avatar.jpg",
    "theme_color": "blue",
    "custom_theme_color": "#3B82F6"
}
```

**All fields are optional. Only include fields you want to update.**

**Response:**
```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "id": 1,
        "full_name": "John Doe Updated",
        "email": "john.updated@example.com",
        "custom_id": "inv-1",
        "phone_number": "+1234567890",
        "country": "USA",
        "profile_picture_url": "https://example.com/avatar.jpg",
        "theme_color": "blue",
        "custom_theme_color": "#3B82F6",
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-10-04T08:15:00.000000Z"
    }
}
```

**Security:**
- Users can only update their own profile (user_id must match authenticated user)
- Password must be confirmed with `password_confirmation` field
- Email must be unique (cannot use another user's email)

**Error Response (Unauthorized):**
```json
{
    "success": false,
    "message": "You can only update your own profile.",
    "error": "Unauthorized"
}
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
