# Extension Marketplace API Documentation

This directory contains comprehensive API documentation for the Laravel CRM Extension Marketplace.

## Overview

The Extension Marketplace API enables developers to publish, manage, and monetize extensions while allowing users to discover, install, and review extensions. The marketplace supports plugins, themes, and integrations with built-in security review, payment processing, and automatic update management.

## Documentation Guides

### For System Administrators

If you're looking to **set up and configure the marketplace**, please see the [Setup Guide](./SETUP-GUIDE.md). It contains comprehensive documentation on:

- Installation and prerequisites
- Database setup
- Payment gateway configuration (Stripe)
- Storage setup (Local/S3/Spaces)
- Scheduler configuration for update checks
- Permissions and ACL
- Production deployment
- Troubleshooting

### For Extension Users

If you're looking to **browse, install, and manage extensions**, please see the [User Guide](./USER-GUIDE.md). It contains comprehensive documentation on:

- Browsing and searching the marketplace
- Installing and uninstalling extensions
- Updating extensions (manual and automatic)
- Writing and managing reviews
- Managing your installed extensions
- Payment and billing information
- Troubleshooting common issues

### For Extension Developers

If you're looking to **create your own extension**, please see the [Developer Guide](./DEVELOPER-GUIDE.md). It contains comprehensive documentation on:

- Setting up your development environment
- Package structure and requirements
- Creating plugins, themes, and integrations
- Testing your extension
- Submission and review process
- Security best practices
- Code examples and troubleshooting

### For API Integration

This README focuses on the **Marketplace API** for interacting with extensions programmatically.

## Documentation Files

- **SETUP-GUIDE.md** - Complete installation and configuration guide
- **USER-GUIDE.md** - Comprehensive guide for extension users
- **DEVELOPER-GUIDE.md** - Comprehensive guide for creating extensions
- **README.md** - This file (API documentation)
- **openapi.yaml** - OpenAPI 3.0 specification with complete API documentation
- **index.html** - Interactive API documentation viewer (Swagger UI)
- **postman-collection.json** - Postman collection for API testing

## Viewing the Documentation

### Method 1: Swagger UI (Recommended)

Open `index.html` in your web browser to view the interactive API documentation. This provides:
- Full endpoint documentation
- Request/response examples
- Try-it-out functionality
- Schema definitions

### Method 2: Online Swagger Editor

1. Go to [Swagger Editor](https://editor.swagger.io/)
2. Load the `openapi.yaml` file
3. View and test the API documentation

### Method 3: Postman

Import the OpenAPI specification directly into Postman:
1. Open Postman
2. Click "Import" → "Upload Files"
3. Select `openapi.yaml`
4. Postman will create a collection with all endpoints

### Method 4: VS Code Extension

Install the "OpenAPI (Swagger) Editor" extension in VS Code for inline documentation viewing and validation.

## API Endpoints Overview

### Public Endpoints (No Authentication Required)

#### Extensions
- `GET /marketplace/extensions` - List all extensions
- `GET /marketplace/extensions/{id}` - Get extension details
- `GET /marketplace/extensions/slug/{slug}` - Get extension by slug
- `GET /marketplace/extensions/{id}/versions` - Get extension versions
- `GET /marketplace/extensions/{id}/reviews` - Get extension reviews
- `GET /marketplace/extensions/{id}/stats` - Get extension statistics
- `GET /marketplace/extensions/featured` - Get featured extensions
- `GET /marketplace/extensions/popular` - Get popular extensions

#### Versions
- `GET /marketplace/versions/{id}` - Get version details
- `GET /marketplace/versions/{id}/compatibility` - Check version compatibility
- `GET /marketplace/versions/{id}/changelog` - Get version changelog

### Authenticated Endpoints (Require Authentication)

#### Extension Management
- `POST /marketplace/extensions` - Create new extension
- `PUT /marketplace/extensions/{id}` - Update extension
- `DELETE /marketplace/extensions/{id}` - Delete extension

#### Version Management
- `POST /marketplace/versions/extension/{extension_id}` - Create new version
- `PUT /marketplace/versions/{id}` - Update version
- `DELETE /marketplace/versions/{id}` - Delete version

#### Installations
- `GET /marketplace/installations` - List user installations
- `POST /marketplace/installations` - Install extension
- `GET /marketplace/installations/{id}` - Get installation details
- `PUT /marketplace/installations/{id}` - Update installation
- `DELETE /marketplace/installations/{id}` - Uninstall extension
- `POST /marketplace/installations/{id}/enable` - Enable extension
- `POST /marketplace/installations/{id}/disable` - Disable extension
- `POST /marketplace/installations/{id}/toggle-auto-update` - Toggle auto-update
- `GET /marketplace/installations/check-updates` - Check for updates
- `GET /marketplace/installations/updates-available` - Get available updates

#### Reviews
- `POST /marketplace/reviews/extension/{extension_id}` - Create review
- `GET /marketplace/reviews/{id}` - Get review details
- `PUT /marketplace/reviews/{id}` - Update review
- `DELETE /marketplace/reviews/{id}` - Delete review
- `POST /marketplace/reviews/{id}/helpful` - Mark review as helpful
- `POST /marketplace/reviews/{id}/report` - Report review
- `GET /marketplace/reviews/my-reviews` - Get my reviews

## Authentication

The API supports two authentication methods:

### 1. Bearer Token (API Tokens)

Include the token in the Authorization header:

```bash
curl -H "Authorization: Bearer {your-token}" \
     https://api.example.com/api/marketplace/installations
```

### 2. Session Authentication (Cookie-based)

For web applications, use session-based authentication with CSRF protection:

```javascript
fetch('/api/marketplace/installations', {
  credentials: 'same-origin',
  headers: {
    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')
  }
})
```

## Request Examples

### List Extensions with Filtering

```bash
curl -X GET "https://api.example.com/api/marketplace/extensions?type=plugin&category_id=3&price_filter=paid&sort=downloads_count&order=desc&per_page=20"
```

### Get Extension Details

```bash
curl -X GET "https://api.example.com/api/marketplace/extensions/1"
```

### Create New Extension (Authenticated)

```bash
curl -X POST "https://api.example.com/api/marketplace/extensions" \
     -H "Authorization: Bearer {your-token}" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Payment Gateway Integration",
       "description": "Integrate popular payment gateways with your CRM",
       "type": "integration",
       "price": 49.99,
       "tags": ["payment", "gateway", "stripe"]
     }'
```

### Install Extension (Authenticated)

```bash
curl -X POST "https://api.example.com/api/marketplace/installations" \
     -H "Authorization: Bearer {your-token}" \
     -H "Content-Type: application/json" \
     -d '{
       "extension_id": 1,
       "version_id": 3,
       "auto_update_enabled": true
     }'
```

### Submit Review (Authenticated)

```bash
curl -X POST "https://api.example.com/api/marketplace/reviews/extension/1" \
     -H "Authorization: Bearer {your-token}" \
     -H "Content-Type: application/json" \
     -d '{
       "title": "Great extension!",
       "review_text": "This extension works perfectly and has great support.",
       "rating": 5
     }'
```

### Check for Updates (Authenticated)

```bash
curl -X GET "https://api.example.com/api/marketplace/installations/check-updates" \
     -H "Authorization: Bearer {your-token}"
```

## Response Format

All API responses follow a consistent format:

### Success Response

```json
{
  "data": {
    "id": 1,
    "name": "Extension Name",
    "...": "..."
  },
  "message": "Operation completed successfully"
}
```

### Paginated Response

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  },
  "links": {
    "first": "https://api.example.com/api/marketplace/extensions?page=1",
    "last": "https://api.example.com/api/marketplace/extensions?page=10",
    "prev": null,
    "next": "https://api.example.com/api/marketplace/extensions?page=2"
  }
}
```

### Error Response

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be at least 0."]
  }
}
```

## HTTP Status Codes

- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `204 No Content` - Request successful with no response body
- `400 Bad Request` - Invalid request format
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation error
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server error

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Authenticated requests**: 60 requests per minute
- **Public endpoints**: 30 requests per minute

Rate limit headers are included in responses:
- `X-RateLimit-Limit` - Maximum requests allowed
- `X-RateLimit-Remaining` - Remaining requests
- `X-RateLimit-Reset` - Time when limit resets (Unix timestamp)

## Versioning

The API uses URL versioning. The current version is `v1`:

```
https://api.example.com/api/v1/marketplace/extensions
```

When breaking changes are introduced, a new version will be released while maintaining backward compatibility for previous versions.

## Pagination

List endpoints support pagination with the following query parameters:

- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)

## Filtering and Sorting

Many list endpoints support filtering and sorting:

### Filtering Parameters
- `type` - Extension type (plugin, theme, integration)
- `category_id` - Category ID
- `price_filter` - Price filter (free, paid, all)
- `featured` - Show only featured extensions (boolean)
- `search` - Search term

### Sorting Parameters
- `sort` - Field to sort by (name, price, downloads_count, average_rating, created_at)
- `order` - Sort order (asc, desc)

## Error Handling

Always check the HTTP status code before processing the response. Error responses include:

```json
{
  "message": "Human-readable error message",
  "errors": {
    "field_name": ["Specific validation error"]
  }
}
```

## Testing the API

### Using cURL

```bash
# Get API token first (authentication endpoint)
TOKEN=$(curl -X POST "https://api.example.com/api/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"developer@example.com","password":"password"}' \
  | jq -r '.token')

# Use the token in subsequent requests
curl -H "Authorization: Bearer $TOKEN" \
     "https://api.example.com/api/marketplace/installations"
```

### Using JavaScript/Fetch

```javascript
// Authenticate
const response = await fetch('/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'developer@example.com',
    password: 'password'
  })
});

const { token } = await response.json();

// Make authenticated requests
const extensions = await fetch('/api/marketplace/extensions', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

### Using Postman

1. Import the OpenAPI specification
2. Set up environment variables for base URL and token
3. Use the pre-request script to set authentication headers
4. Test endpoints individually or use collection runner

## Support

For API support or questions:
- Email: api-support@example.com
- Documentation: https://docs.example.com/marketplace-api
- GitHub Issues: https://github.com/example/laravel-crm/issues

## Changelog

### Version 1.0.0 (2024-01-11)
- Initial API release
- Extension CRUD operations
- Version management
- Installation management
- Review system
- Payment integration
- Developer portal endpoints

## License

This API documentation is provided under the MIT License.
