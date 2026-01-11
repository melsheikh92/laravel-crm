# Compliance API Documentation
## Advanced Compliance Features for GDPR, HIPAA, and SOC 2

---

## 📖 Overview

This document provides comprehensive documentation for all compliance-related API endpoints. These APIs enable programmatic access to consent management, data retention policies, right-to-erasure requests, and compliance reporting features.

### Base URL
```
https://your-domain.com/api
```

### Authentication
All API endpoints require authentication using Laravel Sanctum or API tokens. Include your API token in the request header:

```http
Authorization: Bearer YOUR_API_TOKEN
```

### Response Format
All responses follow a consistent JSON structure:

**Success Response:**
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { ... }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

### Common HTTP Status Codes
- `200` - OK - Request successful
- `201` - Created - Resource created successfully
- `400` - Bad Request - Invalid request parameters
- `401` - Unauthorized - Authentication required
- `404` - Not Found - Resource not found
- `422` - Unprocessable Entity - Validation failed
- `500` - Internal Server Error - Server error occurred

---

## 🔐 Consent Management API

Manage GDPR-compliant consent records for users.

### 1. Get All Consent Records

**Endpoint:** `GET /api/consent`

**Description:** Retrieve all consent records for the authenticated user.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `type` | string | No | Filter by consent type |
| `active_only` | boolean | No | Return only active consents (default: false) |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/consent?active_only=true" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "consent_type": "marketing",
      "purpose": "Email marketing communications",
      "given_at": "2024-01-15T10:30:00.000000Z",
      "withdrawn_at": null,
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "metadata": {},
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    }
  ]
}
```

---

### 2. Record Consent

**Endpoint:** `POST /api/consent`

**Description:** Record a new consent for the authenticated user.

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `consent_type` | string | Yes | Type of consent (e.g., 'marketing', 'analytics') |
| `purpose` | string | No | Purpose of the consent (max 1000 chars) |
| `metadata` | object | No | Additional metadata |

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/consent" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "consent_type": "marketing",
    "purpose": "Email marketing communications",
    "metadata": {
      "source": "mobile_app",
      "version": "2.0"
    }
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Consent recorded successfully",
  "data": {
    "id": 1,
    "user_id": 123,
    "consent_type": "marketing",
    "purpose": "Email marketing communications",
    "given_at": "2024-01-15T10:30:00.000000Z",
    "withdrawn_at": null,
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "metadata": {
      "source": "mobile_app",
      "version": "2.0"
    },
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

---

### 3. Record Multiple Consents

**Endpoint:** `POST /api/consent/multiple`

**Description:** Record multiple consents at once for the authenticated user.

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `consent_types` | array | Yes | Array of consent types to record |
| `metadata` | object | No | Shared metadata for all consents |

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/consent/multiple" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "consent_types": ["marketing", "analytics", "necessary"],
    "metadata": {
      "onboarding": true
    }
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Consents recorded successfully",
  "data": [
    {
      "id": 1,
      "consent_type": "marketing",
      "given_at": "2024-01-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "consent_type": "analytics",
      "given_at": "2024-01-15T10:30:00.000000Z"
    }
  ]
}
```

---

### 4. Withdraw Consent

**Endpoint:** `DELETE /api/consent/{consentType}`

**Description:** Withdraw a specific consent for the authenticated user.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `consentType` | string | Yes | Type of consent to withdraw |

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `metadata` | object | No | Additional metadata for withdrawal |

**Example Request:**
```bash
curl -X DELETE "https://your-domain.com/api/consent/marketing" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "metadata": {
      "reason": "User requested via settings"
    }
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Consent withdrawn successfully"
}
```

---

### 5. Withdraw All Consents

**Endpoint:** `DELETE /api/consent`

**Description:** Withdraw all active consents for the authenticated user.

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `metadata` | object | No | Additional metadata for withdrawal |

**Example Request:**
```bash
curl -X DELETE "https://your-domain.com/api/consent" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "metadata": {
      "reason": "Account closure request"
    }
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Successfully withdrew 3 consent(s)",
  "count": 3
}
```

---

### 6. Get Active Consents

**Endpoint:** `GET /api/consent/active`

**Description:** Retrieve all currently active consents for the authenticated user.

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/consent/active" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "consent_type": "necessary",
      "purpose": "Essential website functionality",
      "given_at": "2024-01-15T10:30:00.000000Z"
    },
    {
      "id": 3,
      "consent_type": "analytics",
      "purpose": "Website usage analytics",
      "given_at": "2024-01-15T10:35:00.000000Z"
    }
  ]
}
```

---

### 7. Check Specific Consent

**Endpoint:** `GET /api/consent/check/{consentType}`

**Description:** Check if the authenticated user has given consent for a specific type.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `consentType` | string | Yes | Type of consent to check |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/consent/check/marketing" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "has_consent": true,
  "consent_type": "marketing"
}
```

---

### 8. Check Required Consents

**Endpoint:** `GET /api/consent/check-required`

**Description:** Check if the authenticated user has all required consents.

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/consent/check-required" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "has_required_consents": false,
  "missing_consents": ["necessary", "privacy_policy"]
}
```

---

### 9. Get Available Consent Types

**Endpoint:** `GET /api/consent/types`

**Description:** Retrieve all available consent types and their configuration.

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/consent/types" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "type": "necessary",
      "required": true,
      "description": "Essential functionality",
      "purpose": "Required for basic website operation"
    },
    {
      "type": "marketing",
      "required": false,
      "description": "Marketing communications",
      "purpose": "Email and promotional content"
    },
    {
      "type": "analytics",
      "required": false,
      "description": "Website analytics",
      "purpose": "Understand user behavior and improve services"
    }
  ]
}
```

---

## 📊 Data Retention Policy API

Manage data retention policies and monitor expired records.

### 1. Get All Retention Policies

**Endpoint:** `GET /api/retention-policies`

**Description:** Retrieve all data retention policies.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `model_type` | string | No | Filter by model type |
| `active_only` | boolean | No | Return only active policies (default: false) |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/retention-policies?active_only=true" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "model_type": "App\\Models\\AuditLog",
      "retention_period_days": 365,
      "delete_after_days": 730,
      "conditions": {},
      "is_active": true,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

### 2. Get Specific Retention Policy

**Endpoint:** `GET /api/retention-policies/{id}`

**Description:** Retrieve a specific retention policy.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Policy ID |

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `include_stats` | boolean | No | Include policy statistics |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/retention-policies/1?include_stats=true" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "model_type": "App\\Models\\AuditLog",
    "retention_period_days": 365,
    "delete_after_days": 730,
    "conditions": {},
    "is_active": true,
    "statistics": {
      "total_records": 15234,
      "expired_records": 342,
      "deletable_records": 125
    }
  }
}
```

---

### 3. Create Retention Policy

**Endpoint:** `POST /api/retention-policies`

**Description:** Create a new data retention policy.

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `model_type` | string | Yes | Fully qualified model class name |
| `retention_period_days` | integer | Yes | Days before records are considered expired (min: 1) |
| `delete_after_days` | integer | Yes | Days before records should be deleted (min: 1) |
| `conditions` | object | No | Conditions for policy application |
| `is_active` | boolean | No | Whether policy is active (default: true) |

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/retention-policies" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "model_type": "App\\Models\\AuditLog",
    "retention_period_days": 365,
    "delete_after_days": 730,
    "conditions": {},
    "is_active": true
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Retention policy created successfully",
  "data": {
    "id": 2,
    "model_type": "App\\Models\\AuditLog",
    "retention_period_days": 365,
    "delete_after_days": 730,
    "conditions": {},
    "is_active": true,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

---

### 4. Update Retention Policy

**Endpoint:** `PUT /api/retention-policies/{id}`

**Description:** Update an existing retention policy.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Policy ID |

**Request Body:** Same as Create Retention Policy, but all fields are optional.

**Example Request:**
```bash
curl -X PUT "https://your-domain.com/api/retention-policies/1" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "retention_period_days": 180,
    "is_active": false
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Retention policy updated successfully",
  "data": {
    "id": 1,
    "model_type": "App\\Models\\AuditLog",
    "retention_period_days": 180,
    "delete_after_days": 730,
    "is_active": false,
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

---

### 5. Delete Retention Policy

**Endpoint:** `DELETE /api/retention-policies/{id}`

**Description:** Delete a retention policy.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Policy ID |

**Example Request:**
```bash
curl -X DELETE "https://your-domain.com/api/retention-policies/1" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Retention policy deleted successfully"
}
```

---

### 6. Activate Retention Policy

**Endpoint:** `POST /api/retention-policies/{id}/activate`

**Description:** Activate a retention policy.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Policy ID |

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/retention-policies/1/activate" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Retention policy activated successfully",
  "data": {
    "id": 1,
    "is_active": true
  }
}
```

---

### 7. Deactivate Retention Policy

**Endpoint:** `POST /api/retention-policies/{id}/deactivate`

**Description:** Deactivate a retention policy.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Policy ID |

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/retention-policies/1/deactivate" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Retention policy deactivated successfully",
  "data": {
    "id": 1,
    "is_active": false
  }
}
```

---

### 8. Get Retention Statistics

**Endpoint:** `GET /api/retention-policies/statistics`

**Description:** Get statistics for retention policies.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `model_type` | string | No | Filter by model type |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/retention-policies/statistics" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "total_policies": 4,
    "active_policies": 3,
    "total_expired_records": 456,
    "total_deletable_records": 234,
    "by_model": [
      {
        "model_type": "App\\Models\\AuditLog",
        "expired": 342,
        "deletable": 125
      }
    ]
  }
}
```

---

### 9. Get Expired Records

**Endpoint:** `GET /api/retention-policies/expired-records`

**Description:** Get a summary of expired records according to retention policies.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `model_type` | string | No | Filter by model type |
| `deletable_only` | boolean | No | Return only deletable records (default: false) |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/retention-policies/expired-records?deletable_only=true" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "policy_id": 1,
      "model_type": "App\\Models\\AuditLog",
      "retention_period_days": 365,
      "delete_after_days": 730,
      "record_count": 125
    }
  ]
}
```

---

### 10. Apply Retention Policies

**Endpoint:** `POST /api/retention-policies/apply`

**Description:** Apply retention policies to delete expired data.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `dry_run` | boolean | No | Preview without deleting (default: true) |
| `model_type` | string | No | Filter by model type |

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/retention-policies/apply?dry_run=false" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "dry_run": false,
    "policies_applied": 3,
    "total_deleted": 125,
    "total_anonymized": 45,
    "results": [
      {
        "policy_id": 1,
        "model_type": "App\\Models\\AuditLog",
        "deleted": 125,
        "anonymized": 0
      }
    ]
  }
}
```

---

## 🗑️ Data Deletion Request API

Manage GDPR right-to-erasure requests and data exports.

### 1. Get All Deletion Requests

**Endpoint:** `GET /api/deletion-requests`

**Description:** Retrieve all data deletion requests.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | No | Filter by status (pending, processing, completed, failed, cancelled) |
| `user_id` | integer | No | Filter by user ID |
| `email` | string | No | Filter by email |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/deletion-requests?status=pending" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "email": "user@example.com",
      "requested_at": "2024-01-15T10:30:00.000000Z",
      "processed_at": null,
      "status": "pending",
      "notes": "User requested data deletion",
      "processed_by": null,
      "user": {
        "id": 123,
        "name": "John Doe"
      },
      "processedBy": null
    }
  ]
}
```

---

### 2. Get Specific Deletion Request

**Endpoint:** `GET /api/deletion-requests/{id}`

**Description:** Retrieve a specific deletion request.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Request ID |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/deletion-requests/1" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 123,
    "email": "user@example.com",
    "requested_at": "2024-01-15T10:30:00.000000Z",
    "processed_at": "2024-01-16T14:20:00.000000Z",
    "status": "completed",
    "notes": "User data successfully deleted",
    "processed_by": 456,
    "user": {
      "id": 123,
      "name": "John Doe"
    },
    "processedBy": {
      "id": 456,
      "name": "Admin User"
    }
  }
}
```

---

### 3. Create Deletion Request

**Endpoint:** `POST /api/deletion-requests`

**Description:** Create a new data deletion request.

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `user_id` | integer | No | User ID (defaults to authenticated user) |
| `email` | string | No | Email address for the request |
| `notes` | string | No | Additional notes (max 1000 chars) |

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/deletion-requests" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "notes": "User requested data deletion via mobile app"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Deletion request created successfully",
  "data": {
    "id": 2,
    "user_id": 123,
    "email": "user@example.com",
    "requested_at": "2024-01-15T10:30:00.000000Z",
    "status": "pending",
    "notes": "User requested data deletion via mobile app"
  }
}
```

---

### 4. Process Deletion Request

**Endpoint:** `POST /api/deletion-requests/{id}/process`

**Description:** Process a pending deletion request.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Request ID |

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `force` | boolean | No | Force deletion instead of anonymization (default: false) |

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/deletion-requests/1/process" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "force": false
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Deletion request processed successfully",
  "data": {
    "request_id": 1,
    "status": "completed",
    "method": "anonymized",
    "processed_at": "2024-01-16T14:20:00.000000Z",
    "affected_models": {
      "user": 1,
      "consents": 3,
      "tickets": 5
    }
  }
}
```

---

### 5. Cancel Deletion Request

**Endpoint:** `POST /api/deletion-requests/{id}/cancel`

**Description:** Cancel a pending or processing deletion request.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Request ID |

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `notes` | string | No | Cancellation reason (max 1000 chars) |

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/deletion-requests/1/cancel" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "notes": "User changed their mind"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Deletion request cancelled successfully",
  "data": {
    "id": 1,
    "status": "cancelled",
    "notes": "User changed their mind"
  }
}
```

---

### 6. Export User Data

**Endpoint:** `POST /api/deletion-requests/export`

**Description:** Export user data for GDPR data portability.

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `user_id` | integer | No | User ID (defaults to authenticated user) |
| `format` | string | No | Export format: json, csv, pdf (default: json) |
| `include_audit_logs` | boolean | No | Include audit logs in export (default: false) |
| `async` | boolean | No | Queue export job asynchronously (default: false) |

**Example Request (Synchronous):**
```bash
curl -X POST "https://your-domain.com/api/deletion-requests/export" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "format": "json",
    "include_audit_logs": true,
    "async": false
  }'
```

**Example Response (Synchronous):**
```json
{
  "success": true,
  "message": "User data exported successfully",
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "user@example.com"
    },
    "consents": [
      {
        "type": "marketing",
        "given_at": "2024-01-15T10:30:00.000000Z"
      }
    ],
    "tickets": [
      {
        "id": 1,
        "subject": "Support Request"
      }
    ],
    "audit_logs": [
      {
        "event": "created",
        "created_at": "2024-01-15T10:30:00.000000Z"
      }
    ]
  }
}
```

**Example Request (Asynchronous):**
```bash
curl -X POST "https://your-domain.com/api/deletion-requests/export" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "format": "pdf",
    "async": true
  }'
```

**Example Response (Asynchronous):**
```json
{
  "success": true,
  "message": "Data export has been queued. You will be notified when it is ready."
}
```

---

### 7. Get Deletion Request Statistics

**Endpoint:** `GET /api/deletion-requests/statistics`

**Description:** Get statistics for data deletion requests.

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/deletion-requests/statistics" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "by_status": {
      "pending": 5,
      "processing": 2,
      "completed": 123,
      "failed": 3,
      "cancelled": 8
    },
    "overdue": {
      "count": 2,
      "requests": [
        {
          "id": 1,
          "requested_at": "2024-01-01T10:00:00.000000Z",
          "days_pending": 45
        }
      ]
    }
  }
}
```

---

### 8. Get Overdue Deletion Requests

**Endpoint:** `GET /api/deletion-requests/overdue`

**Description:** Get deletion requests that are overdue for processing.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `days` | integer | No | Consider overdue after N days (default: 30) |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/deletion-requests/overdue?days=30" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "count": 2,
    "requests": [
      {
        "id": 1,
        "user_id": 123,
        "requested_at": "2024-01-01T10:00:00.000000Z",
        "status": "pending",
        "days_pending": 45
      }
    ]
  }
}
```

---

## 📈 Compliance Reporting API

Access compliance metrics, audit reports, and compliance status.

### 1. Get Compliance Overview

**Endpoint:** `GET /api/compliance/metrics/overview`

**Description:** Get comprehensive compliance metrics overview.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `start_date` | date | No | Filter from date (YYYY-MM-DD) |
| `end_date` | date | No | Filter to date (YYYY-MM-DD) |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/compliance/metrics/overview?start_date=2024-01-01&end_date=2024-01-31" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "period": {
      "start_date": "2024-01-01",
      "end_date": "2024-01-31"
    },
    "audit_logging": {
      "total_logs": 15234,
      "events": {
        "created": 8923,
        "updated": 4532,
        "deleted": 1779
      }
    },
    "consent_management": {
      "total_consents": 3456,
      "active_consents": 2890,
      "consent_rate": 83.6
    },
    "data_retention": {
      "active_policies": 4,
      "expired_records": 456,
      "deletable_records": 234
    },
    "encryption": {
      "encrypted_models": 2,
      "encrypted_fields": 4
    },
    "compliance_status": {
      "gdpr": "compliant",
      "hipaa": "compliant",
      "soc2": "compliant"
    }
  }
}
```

---

### 2. Get Specific Metrics

**Endpoint:** `GET /api/compliance/metrics/{type}`

**Description:** Get specific compliance metrics by type.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `type` | string | Yes | Metric type: audit_logging, consent, retention, encryption, status |

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `start_date` | date | No | Filter from date (YYYY-MM-DD) |
| `end_date` | date | No | Filter to date (YYYY-MM-DD) |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/compliance/metrics/audit_logging?start_date=2024-01-01" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "type": "audit_logging",
  "data": {
    "total_logs": 15234,
    "period": {
      "start_date": "2024-01-01",
      "end_date": "2024-01-31"
    },
    "by_event": {
      "created": 8923,
      "updated": 4532,
      "deleted": 1779,
      "login": 234,
      "logout": 189
    },
    "by_model": {
      "App\\Models\\User": 3456,
      "App\\Models\\SupportTicket": 8934,
      "App\\Models\\ConsentRecord": 2844
    },
    "by_user": {
      "123": 456,
      "456": 892
    }
  }
}
```

---

### 3. Get Compliance Status

**Endpoint:** `GET /api/compliance/status`

**Description:** Get overall compliance status with issues and warnings.

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/compliance/status" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "overall_status": "compliant",
    "frameworks": {
      "gdpr": {
        "status": "compliant",
        "issues": [],
        "warnings": []
      },
      "hipaa": {
        "status": "compliant",
        "issues": [],
        "warnings": ["Field encryption not enabled"]
      },
      "soc2": {
        "status": "non_compliant",
        "issues": ["Audit logging disabled"],
        "warnings": []
      }
    },
    "features": {
      "audit_logging": true,
      "consent_management": true,
      "data_retention": true,
      "field_encryption": false
    }
  }
}
```

---

### 4. Get Audit Report Summary

**Endpoint:** `GET /api/compliance/reports/audit/summary`

**Description:** Get summary statistics for audit logs with filtering.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `start_date` | date | No | Filter from date (YYYY-MM-DD) |
| `end_date` | date | No | Filter to date (YYYY-MM-DD) |
| `event` | string | No | Filter by event type |
| `model_type` | string | No | Filter by model type |
| `user_id` | integer | No | Filter by user ID |
| `ip_address` | string | No | Filter by IP address |
| `tags` | string | No | Filter by tags |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/compliance/reports/audit/summary?event=created&start_date=2024-01-01" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "total_records": 8923,
    "date_range": {
      "start": "2024-01-01",
      "end": "2024-01-31"
    },
    "filters_applied": {
      "event": "created"
    },
    "statistics": {
      "events": {
        "created": 8923
      },
      "models": {
        "App\\Models\\User": 234,
        "App\\Models\\SupportTicket": 8689
      },
      "users": {
        "123": 4521,
        "456": 4402
      }
    }
  }
}
```

---

### 5. Generate Audit Report

**Endpoint:** `POST /api/compliance/reports/audit/generate`

**Description:** Generate an audit report in the specified format.

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `format` | string | Yes | Report format: csv, json, pdf |
| `start_date` | date | No | Filter from date (YYYY-MM-DD) |
| `end_date` | date | No | Filter to date (YYYY-MM-DD) |
| `event` | string | No | Filter by event type |
| `model_type` | string | No | Filter by model type |
| `user_id` | integer | No | Filter by user ID |
| `ip_address` | string | No | Filter by IP address |
| `tags` | string | No | Filter by tags |
| `limit` | integer | No | Max records (1-10000) |
| `order_by` | string | No | Sort field: created_at, event, auditable_type, user_id |
| `order_direction` | string | No | Sort direction: asc, desc |
| `title` | string | No | Report title (max 255 chars) |
| `include_statistics` | boolean | No | Include summary statistics (default: false) |
| `pretty_print` | boolean | No | Pretty print JSON (default: false) |

**Example Request (JSON):**
```bash
curl -X POST "https://your-domain.com/api/compliance/reports/audit/generate" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "format": "json",
    "start_date": "2024-01-01",
    "end_date": "2024-01-31",
    "event": "created",
    "limit": 100,
    "include_statistics": true,
    "pretty_print": true
  }'
```

**Example Response (JSON):**
```json
{
  "success": true,
  "format": "json",
  "data": {
    "metadata": {
      "title": "Audit Report",
      "generated_at": "2024-01-15T10:30:00.000000Z",
      "filters": {
        "event": "created",
        "start_date": "2024-01-01",
        "end_date": "2024-01-31"
      }
    },
    "statistics": {
      "total_records": 100,
      "events": {
        "created": 100
      }
    },
    "records": [
      {
        "id": 1,
        "event": "created",
        "auditable_type": "App\\Models\\User",
        "auditable_id": 123,
        "user_id": 456,
        "created_at": "2024-01-15T10:30:00.000000Z"
      }
    ]
  }
}
```

**Example Request (CSV/PDF):**
```bash
curl -X POST "https://your-domain.com/api/compliance/reports/audit/generate" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "format": "csv",
    "start_date": "2024-01-01",
    "limit": 1000,
    "title": "Monthly Audit Report - January 2024"
  }' \
  --output audit_report.csv
```

**Response (CSV/PDF):** Binary file download with appropriate headers.

---

## 🔧 Configuration

### Environment Variables

Add these to your `.env` file to configure compliance features:

```bash
# Compliance Features
COMPLIANCE_ENABLED=true

# Audit Logging
AUDIT_LOGGING_ENABLED=true
AUDIT_LOG_RETENTION_DAYS=365

# Consent Management
CONSENT_MANAGEMENT_ENABLED=true
CONSENT_CAPTURE_IP=true
CONSENT_CAPTURE_USER_AGENT=true

# Data Retention
DATA_RETENTION_ENABLED=true
DATA_RETENTION_AUTO_DELETE=false
DATA_RETENTION_PREFER_ANONYMIZATION=true

# Field Encryption
FIELD_ENCRYPTION_ENABLED=true
FIELD_ENCRYPTION_AUTO_DECRYPT=true

# GDPR Right to Erasure
GDPR_ENABLED=true
GDPR_ANONYMIZE_INSTEAD_OF_DELETE=true
GDPR_SEND_CONFIRMATION_EMAIL=true

# Compliance Reporting
COMPLIANCE_REPORTING_ENABLED=true
```

---

## 🛡️ Security Best Practices

### 1. API Authentication
- Always use secure API tokens
- Rotate tokens regularly
- Use HTTPS for all API requests
- Never expose tokens in client-side code

### 2. Rate Limiting
API endpoints are subject to rate limiting. Default limits:
- 60 requests per minute per user
- 1000 requests per hour per user

### 3. Data Privacy
- Only authorized users can access their own consent records
- Admin privileges required for accessing other users' data
- Deletion requests are logged in audit trail
- Exported data should be transmitted securely

### 4. Audit Trail
All API operations are logged in the audit trail including:
- Consent recording and withdrawal
- Deletion request creation and processing
- Retention policy changes
- Report generation

---

## 🐛 Error Handling

### Common Errors

**401 Unauthorized**
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

**404 Not Found**
```json
{
  "success": false,
  "message": "Resource not found"
}
```

**422 Validation Error**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "consent_type": ["The consent type field is required."],
    "format": ["The selected format is invalid."]
  }
}
```

**500 Server Error**
```json
{
  "success": false,
  "message": "Failed to process request: [error details]"
}
```

---

## 📚 Additional Resources

- [GDPR Compliance Checklist](./gdpr-compliance-checklist.md)
- [Implementation Guide](./compliance-implementation-guide.md)
- [Compliance Configuration](../config/compliance.php)

---

## 📝 Changelog

**Version 1.0.0** - January 2024
- Initial release
- Consent Management API
- Data Retention Policy API
- Data Deletion Request API
- Compliance Reporting API
- Full GDPR, HIPAA, and SOC 2 support

---

## 💬 Support

For questions or issues with the Compliance API, please:
- Check the [Implementation Guide](./compliance-implementation-guide.md)
- Review the [GDPR Compliance Checklist](./gdpr-compliance-checklist.md)
- Contact your system administrator

---

**Last Updated:** January 2024
**API Version:** 1.0.0
