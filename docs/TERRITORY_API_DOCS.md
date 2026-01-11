# Territory Management API Documentation

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Territory Endpoints](#territory-endpoints)
4. [Territory Rule Endpoints](#territory-rule-endpoints)
5. [Territory Assignment Endpoints](#territory-assignment-endpoints)
6. [Response Formats](#response-formats)
7. [Error Handling](#error-handling)

---

## Overview

The Territory Management API provides comprehensive RESTful endpoints for managing territories, territory rules, and territory assignments. This API enables:

- CRUD operations for territories with hierarchical support
- Territory rule management with priority-based evaluation
- Automated and manual territory assignments for leads, organizations, and persons
- Performance analytics and statistics

**Base URL**: `/api`

**Content-Type**: `application/json`

**Date Format**: ISO 8601 (e.g., `2024-01-15T10:30:00.000000Z`)

---

## Authentication

All API endpoints require authentication using Laravel's `auth:user` middleware. Include the appropriate authentication token in your requests.

**Header Example**:
```
Authorization: Bearer {your_access_token}
```

---

## Territory Endpoints

### 1. List Territories

Retrieve a paginated or filtered list of territories.

**Endpoint**: `GET /api/territories`

**Query Parameters**:
- `type` (optional): Filter by territory type (`geographic` or `account-based`)
- `status` (optional): Filter by status (`active` or `inactive`)
- `parent_id` (optional): Filter by parent territory ID (use `null` for root territories)
- `search` (optional): Search by name or code
- `sort_by` (optional): Field to sort by (default: `created_at`)
- `sort_order` (optional): Sort order (`asc` or `desc`, default: `desc`)
- `per_page` (optional): Items per page (default: 15, use `all` for all records)

**Request Example**:
```bash
GET /api/territories?type=geographic&status=active&per_page=10
```

**Response Example**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "North America",
      "code": "NA-001",
      "description": "North American territory covering USA and Canada",
      "type": "geographic",
      "status": "active",
      "parent_id": null,
      "user_id": 5,
      "boundaries": {
        "type": "Polygon",
        "coordinates": [[[-125.0, 50.0], [-125.0, 25.0], [-65.0, 25.0], [-65.0, 50.0], [-125.0, 50.0]]]
      },
      "created_at": "2024-01-10T10:00:00.000000Z",
      "updated_at": "2024-01-10T10:00:00.000000Z",
      "deleted_at": null,
      "parent": null,
      "owner": {
        "id": 5,
        "name": "John Smith",
        "email": "john.smith@example.com"
      },
      "children_count": 3,
      "has_children": true
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 10,
    "to": 10,
    "total": 50
  }
}
```

---

### 2. Get Territory Details

Retrieve detailed information about a specific territory including relationships.

**Endpoint**: `GET /api/territories/{id}`

**Request Example**:
```bash
GET /api/territories/1
```

**Response Example**:
```json
{
  "data": {
    "id": 1,
    "name": "North America",
    "code": "NA-001",
    "description": "North American territory covering USA and Canada",
    "type": "geographic",
    "status": "active",
    "parent_id": null,
    "user_id": 5,
    "boundaries": {
      "type": "Polygon",
      "coordinates": [[[-125.0, 50.0], [-125.0, 25.0], [-65.0, 25.0], [-65.0, 50.0], [-125.0, 50.0]]]
    },
    "created_at": "2024-01-10T10:00:00.000000Z",
    "updated_at": "2024-01-10T10:00:00.000000Z",
    "deleted_at": null,
    "owner": {
      "id": 5,
      "name": "John Smith",
      "email": "john.smith@example.com"
    },
    "users": [
      {
        "id": 5,
        "name": "John Smith",
        "email": "john.smith@example.com",
        "role": "owner"
      }
    ],
    "rules_count": 5,
    "active_rules_count": 4,
    "assignments_count": 120,
    "manual_assignments_count": 15,
    "automatic_assignments_count": 105
  }
}
```

---

### 3. Create Territory

Create a new territory.

**Endpoint**: `POST /api/territories`

**Request Body**:
```json
{
  "name": "Western Europe",
  "code": "WE-001",
  "description": "Western European region",
  "type": "geographic",
  "status": "active",
  "parent_id": null,
  "user_id": 7,
  "boundaries": "{\"type\":\"Polygon\",\"coordinates\":[[[-10.0,60.0],[-10.0,35.0],[30.0,35.0],[30.0,60.0],[-10.0,60.0]]]}"
}
```

**Validation Rules**:
- `name`: required, max 100 characters
- `code`: required, unique, max 50 characters
- `type`: required, must be `geographic` or `account-based`
- `status`: required, must be `active` or `inactive`
- `description`: optional, max 500 characters
- `parent_id`: optional, must exist in territories table
- `user_id`: required, must exist in users table
- `boundaries`: optional, must be valid JSON (required for geographic territories)

**Response Example** (201 Created):
```json
{
  "data": {
    "id": 10,
    "name": "Western Europe",
    "code": "WE-001",
    "description": "Western European region",
    "type": "geographic",
    "status": "active",
    "parent_id": null,
    "user_id": 7,
    "boundaries": {
      "type": "Polygon",
      "coordinates": [[[-10.0, 60.0], [-10.0, 35.0], [30.0, 35.0], [30.0, 60.0], [-10.0, 60.0]]]
    },
    "created_at": "2024-01-15T14:30:00.000000Z",
    "updated_at": "2024-01-15T14:30:00.000000Z",
    "deleted_at": null,
    "parent": null,
    "owner": {
      "id": 7,
      "name": "Jane Doe",
      "email": "jane.doe@example.com"
    }
  },
  "message": "Territory created successfully."
}
```

---

### 4. Update Territory

Update an existing territory.

**Endpoint**: `PUT /api/territories/{id}`

**Request Body**:
```json
{
  "name": "Western Europe (Updated)",
  "code": "WE-001",
  "description": "Western European region including UK",
  "type": "geographic",
  "status": "active",
  "parent_id": null,
  "user_id": 7,
  "boundaries": "{\"type\":\"Polygon\",\"coordinates\":[[[-10.0,60.0],[-10.0,35.0],[30.0,35.0],[30.0,60.0],[-10.0,60.0]]]}"
}
```

**Validation Rules**: Same as create, except:
- `code`: unique except for current territory

**Note**: The API prevents circular hierarchy (setting parent to self or descendant).

**Response Example**:
```json
{
  "data": {
    "id": 10,
    "name": "Western Europe (Updated)",
    "code": "WE-001",
    "description": "Western European region including UK",
    "type": "geographic",
    "status": "active",
    "parent_id": null,
    "user_id": 7,
    "boundaries": {
      "type": "Polygon",
      "coordinates": [[[-10.0, 60.0], [-10.0, 35.0], [30.0, 35.0], [30.0, 60.0], [-10.0, 60.0]]]
    },
    "created_at": "2024-01-15T14:30:00.000000Z",
    "updated_at": "2024-01-15T15:45:00.000000Z",
    "deleted_at": null,
    "owner": {
      "id": 7,
      "name": "Jane Doe",
      "email": "jane.doe@example.com"
    }
  },
  "message": "Territory updated successfully."
}
```

---

### 5. Delete Territory

Delete a territory.

**Endpoint**: `DELETE /api/territories/{id}`

**Request Example**:
```bash
DELETE /api/territories/10
```

**Response Example**:
```json
{
  "message": "Territory deleted successfully."
}
```

**Error Response** (400 Bad Request):
```json
{
  "message": "Territory could not be deleted."
}
```

---

### 6. Get Territory Hierarchy

Retrieve the complete territory hierarchy as a tree structure.

**Endpoint**: `GET /api/territories/hierarchy`

**Request Example**:
```bash
GET /api/territories/hierarchy
```

**Response Example**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Global",
      "code": "GLOBAL-001",
      "description": "Global territory",
      "type": "geographic",
      "status": "active",
      "parent_id": null,
      "user_id": 1,
      "boundaries": null,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z",
      "deleted_at": null,
      "children": [
        {
          "id": 2,
          "name": "North America",
          "code": "NA-001",
          "description": "North American region",
          "type": "geographic",
          "status": "active",
          "parent_id": 1,
          "user_id": 2,
          "boundaries": {...},
          "created_at": "2024-01-05T00:00:00.000000Z",
          "updated_at": "2024-01-05T00:00:00.000000Z",
          "deleted_at": null,
          "children": [
            {
              "id": 5,
              "name": "USA - West Coast",
              "code": "USA-WC-001",
              "description": "Western United States",
              "type": "geographic",
              "status": "active",
              "parent_id": 2,
              "user_id": 3,
              "boundaries": {...},
              "created_at": "2024-01-08T00:00:00.000000Z",
              "updated_at": "2024-01-08T00:00:00.000000Z",
              "deleted_at": null
            }
          ]
        }
      ]
    }
  ]
}
```

---

### 7. Get Root Territories

Retrieve territories without a parent (top-level territories).

**Endpoint**: `GET /api/territories/roots`

**Request Example**:
```bash
GET /api/territories/roots
```

**Response Example**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Global",
      "code": "GLOBAL-001",
      "description": "Global territory",
      "type": "geographic",
      "status": "active",
      "parent_id": null,
      "user_id": 1,
      "boundaries": null,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z",
      "deleted_at": null
    }
  ]
}
```

---

### 8. Get Territory Children

Retrieve direct children of a specific territory.

**Endpoint**: `GET /api/territories/{id}/children`

**Request Example**:
```bash
GET /api/territories/1/children
```

**Response Example**:
```json
{
  "data": [
    {
      "id": 2,
      "name": "North America",
      "code": "NA-001",
      "description": "North American region",
      "type": "geographic",
      "status": "active",
      "parent_id": 1,
      "user_id": 2,
      "boundaries": {...},
      "created_at": "2024-01-05T00:00:00.000000Z",
      "updated_at": "2024-01-05T00:00:00.000000Z",
      "deleted_at": null
    },
    {
      "id": 3,
      "name": "Europe",
      "code": "EU-001",
      "description": "European region",
      "type": "geographic",
      "status": "active",
      "parent_id": 1,
      "user_id": 4,
      "boundaries": {...},
      "created_at": "2024-01-05T00:00:00.000000Z",
      "updated_at": "2024-01-05T00:00:00.000000Z",
      "deleted_at": null
    }
  ]
}
```

---

### 9. Get Territory Descendants

Retrieve all descendants of a specific territory (children, grandchildren, etc.).

**Endpoint**: `GET /api/territories/{id}/descendants`

**Request Example**:
```bash
GET /api/territories/1/descendants
```

**Response Example**:
```json
{
  "data": [
    {
      "id": 2,
      "name": "North America",
      "code": "NA-001",
      "parent_id": 1,
      "...": "..."
    },
    {
      "id": 5,
      "name": "USA - West Coast",
      "code": "USA-WC-001",
      "parent_id": 2,
      "...": "..."
    },
    {
      "id": 6,
      "name": "USA - East Coast",
      "code": "USA-EC-001",
      "parent_id": 2,
      "...": "..."
    }
  ]
}
```

---

### 10. Get Territory Rules

Retrieve all rules for a specific territory.

**Endpoint**: `GET /api/territories/{id}/rules`

**Request Example**:
```bash
GET /api/territories/1/rules
```

**Response Example**:
```json
{
  "data": [
    {
      "id": 1,
      "territory_id": 1,
      "rule_type": "geographic",
      "field_name": "state",
      "operator": "in",
      "value": ["CA", "OR", "WA"],
      "priority": 100,
      "is_active": true,
      "created_at": "2024-01-10T10:00:00.000000Z",
      "updated_at": "2024-01-10T10:00:00.000000Z"
    },
    {
      "id": 2,
      "territory_id": 1,
      "rule_type": "industry",
      "field_name": "industry",
      "operator": "=",
      "value": "Technology",
      "priority": 90,
      "is_active": true,
      "created_at": "2024-01-10T10:00:00.000000Z",
      "updated_at": "2024-01-10T10:00:00.000000Z"
    }
  ]
}
```

---

### 11. Get Territory Assignments

Retrieve all assignments for a specific territory.

**Endpoint**: `GET /api/territories/{id}/assignments`

**Request Example**:
```bash
GET /api/territories/1/assignments
```

**Response Example**:
```json
{
  "data": [
    {
      "id": 1,
      "territory_id": 1,
      "assignable_type": "Webkul\\Lead\\Models\\Lead",
      "assignable_id": 42,
      "assignable": {
        "id": 42,
        "name": "Acme Corp Lead",
        "type": "Lead"
      },
      "assigned_by": 5,
      "assigned_by_user": {
        "id": 5,
        "name": "John Smith"
      },
      "assignment_type": "automatic",
      "assigned_at": "2024-01-15T10:30:00.000000Z",
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    }
  ]
}
```

---

### 12. Get Territory Statistics

Retrieve statistics for a specific territory.

**Endpoint**: `GET /api/territories/{id}/statistics`

**Request Example**:
```bash
GET /api/territories/1/statistics
```

**Response Example**:
```json
{
  "data": {
    "total_rules": 5,
    "active_rules": 4,
    "total_assignments": 120,
    "manual_assignments": 15,
    "automatic_assignments": 105,
    "children_count": 3,
    "has_children": true,
    "is_active": true
  }
}
```

---

## Territory Rule Endpoints

### 1. List Territory Rules

Retrieve a paginated or filtered list of territory rules.

**Endpoint**: `GET /api/territory-rules`

**Query Parameters**:
- `territory_id` (optional): Filter by territory ID
- `rule_type` (optional): Filter by rule type (`geographic`, `industry`, `account_size`, `custom`)
- `is_active` (optional): Filter by active status (boolean)
- `sort_by` (optional): Field to sort by (default: `priority`)
- `sort_order` (optional): Sort order (`asc` or `desc`, default: `desc`)
- `per_page` (optional): Items per page (default: 15, use `all` for all records)

**Request Example**:
```bash
GET /api/territory-rules?territory_id=1&is_active=true&per_page=20
```

**Response Example**:
```json
{
  "data": [
    {
      "id": 1,
      "territory_id": 1,
      "rule_type": "geographic",
      "field_name": "state",
      "operator": "in",
      "value": ["CA", "OR", "WA"],
      "priority": 100,
      "is_active": true,
      "created_at": "2024-01-10T10:00:00.000000Z",
      "updated_at": "2024-01-10T10:00:00.000000Z",
      "territory": {
        "id": 1,
        "name": "West Coast",
        "code": "WC-001",
        "type": "geographic"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 2,
    "per_page": 20,
    "to": 20,
    "total": 35
  }
}
```

---

### 2. Get Territory Rule Details

Retrieve detailed information about a specific territory rule.

**Endpoint**: `GET /api/territory-rules/{id}`

**Request Example**:
```bash
GET /api/territory-rules/1
```

**Response Example**:
```json
{
  "data": {
    "id": 1,
    "territory_id": 1,
    "rule_type": "geographic",
    "field_name": "state",
    "operator": "in",
    "value": ["CA", "OR", "WA"],
    "priority": 100,
    "is_active": true,
    "created_at": "2024-01-10T10:00:00.000000Z",
    "updated_at": "2024-01-10T10:00:00.000000Z",
    "territory": {
      "id": 1,
      "name": "West Coast",
      "code": "WC-001",
      "type": "geographic"
    }
  }
}
```

---

### 3. Create Territory Rule

Create a new territory rule.

**Endpoint**: `POST /api/territory-rules`

**Request Body**:
```json
{
  "territory_id": 1,
  "rule_type": "industry",
  "field_name": "industry",
  "operator": "in",
  "value": "[\"Technology\", \"Software\", \"SaaS\"]",
  "priority": 95,
  "is_active": true
}
```

**Validation Rules**:
- `territory_id`: required, must exist in territories table
- `rule_type`: required, must be `geographic`, `industry`, `account_size`, or `custom`
- `field_name`: required, max 100 characters
- `operator`: required, must be one of: `=`, `!=`, `>`, `>=`, `<`, `<=`, `in`, `not_in`, `contains`, `not_contains`, `starts_with`, `ends_with`, `is_null`, `is_not_null`, `between`
- `value`: optional, must be valid JSON
- `priority`: optional, integer, min 0
- `is_active`: optional, boolean

**Response Example** (201 Created):
```json
{
  "data": {
    "id": 15,
    "territory_id": 1,
    "rule_type": "industry",
    "field_name": "industry",
    "operator": "in",
    "value": ["Technology", "Software", "SaaS"],
    "priority": 95,
    "is_active": true,
    "created_at": "2024-01-15T16:00:00.000000Z",
    "updated_at": "2024-01-15T16:00:00.000000Z",
    "territory": {
      "id": 1,
      "name": "West Coast",
      "code": "WC-001",
      "type": "geographic"
    }
  },
  "message": "Territory rule created successfully."
}
```

---

### 4. Update Territory Rule

Update an existing territory rule.

**Endpoint**: `PUT /api/territory-rules/{id}`

**Request Body**:
```json
{
  "territory_id": 1,
  "rule_type": "industry",
  "field_name": "industry",
  "operator": "in",
  "value": "[\"Technology\", \"Software\", \"SaaS\", \"AI\"]",
  "priority": 98,
  "is_active": true
}
```

**Response Example**:
```json
{
  "data": {
    "id": 15,
    "territory_id": 1,
    "rule_type": "industry",
    "field_name": "industry",
    "operator": "in",
    "value": ["Technology", "Software", "SaaS", "AI"],
    "priority": 98,
    "is_active": true,
    "created_at": "2024-01-15T16:00:00.000000Z",
    "updated_at": "2024-01-15T16:30:00.000000Z",
    "territory": {
      "id": 1,
      "name": "West Coast",
      "code": "WC-001",
      "type": "geographic"
    }
  },
  "message": "Territory rule updated successfully."
}
```

---

### 5. Delete Territory Rule

Delete a territory rule.

**Endpoint**: `DELETE /api/territory-rules/{id}`

**Request Example**:
```bash
DELETE /api/territory-rules/15
```

**Response Example**:
```json
{
  "message": "Territory rule deleted successfully."
}
```

---

### 6. Toggle Rule Active Status

Toggle the active status of a territory rule.

**Endpoint**: `PATCH /api/territory-rules/{id}/toggle-status`

**Request Example**:
```bash
PATCH /api/territory-rules/15/toggle-status
```

**Response Example**:
```json
{
  "data": {
    "id": 15,
    "territory_id": 1,
    "rule_type": "industry",
    "field_name": "industry",
    "operator": "in",
    "value": ["Technology", "Software", "SaaS", "AI"],
    "priority": 98,
    "is_active": false,
    "created_at": "2024-01-15T16:00:00.000000Z",
    "updated_at": "2024-01-15T17:00:00.000000Z"
  },
  "message": "Territory rule status toggled successfully."
}
```

---

### 7. Update Rule Priority

Update the priority of a territory rule.

**Endpoint**: `PATCH /api/territory-rules/{id}/priority`

**Request Body**:
```json
{
  "priority": 85
}
```

**Response Example**:
```json
{
  "data": {
    "id": 15,
    "territory_id": 1,
    "rule_type": "industry",
    "field_name": "industry",
    "operator": "in",
    "value": ["Technology", "Software", "SaaS", "AI"],
    "priority": 85,
    "is_active": true,
    "created_at": "2024-01-15T16:00:00.000000Z",
    "updated_at": "2024-01-15T17:15:00.000000Z"
  },
  "message": "Territory rule priority updated successfully."
}
```

---

### 8. Bulk Update Rule Priorities

Update priorities of multiple rules in a single request.

**Endpoint**: `POST /api/territory-rules/bulk-priorities`

**Request Body**:
```json
{
  "priorities": [
    {
      "id": 1,
      "priority": 100
    },
    {
      "id": 2,
      "priority": 90
    },
    {
      "id": 3,
      "priority": 80
    }
  ]
}
```

**Validation Rules**:
- `priorities`: required, must be an array
- `priorities.*.id`: required, must exist in territory_rules table
- `priorities.*.priority`: required, integer, min 0

**Response Example**:
```json
{
  "message": "Territory rule priorities updated successfully."
}
```

---

## Territory Assignment Endpoints

### 1. List Territory Assignments

Retrieve a paginated or filtered list of territory assignments.

**Endpoint**: `GET /api/territory-assignments`

**Query Parameters**:
- `territory_id` (optional): Filter by territory ID
- `assignable_type` (optional): Filter by entity type (full class name)
- `assignment_type` (optional): Filter by assignment type (`manual` or `automatic`)
- `assigned_by` (optional): Filter by user ID who made the assignment
- `sort_by` (optional): Field to sort by (default: `assigned_at`)
- `sort_order` (optional): Sort order (`asc` or `desc`, default: `desc`)
- `per_page` (optional): Items per page (default: 15, use `all` for all records)

**Request Example**:
```bash
GET /api/territory-assignments?territory_id=1&assignment_type=automatic&per_page=25
```

**Response Example**:
```json
{
  "data": [
    {
      "id": 1,
      "territory_id": 1,
      "assignable_type": "Webkul\\Lead\\Models\\Lead",
      "assignable_id": 42,
      "assigned_by": 5,
      "assignment_type": "automatic",
      "assigned_at": "2024-01-15T10:30:00.000000Z",
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z",
      "territory": {
        "id": 1,
        "name": "West Coast",
        "code": "WC-001",
        "type": "geographic"
      },
      "assignable": {
        "id": 42,
        "name": "Acme Corp Lead",
        "type": "Lead"
      },
      "assigned_by_user": {
        "id": 5,
        "name": "John Smith",
        "email": "john.smith@example.com"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 25,
    "to": 25,
    "total": 120
  }
}
```

---

### 2. Get Territory Assignment Details

Retrieve detailed information about a specific territory assignment.

**Endpoint**: `GET /api/territory-assignments/{id}`

**Request Example**:
```bash
GET /api/territory-assignments/1
```

**Response Example**:
```json
{
  "data": {
    "id": 1,
    "territory_id": 1,
    "assignable_type": "Webkul\\Lead\\Models\\Lead",
    "assignable_id": 42,
    "assigned_by": 5,
    "assignment_type": "automatic",
    "assigned_at": "2024-01-15T10:30:00.000000Z",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z",
    "territory": {
      "id": 1,
      "name": "West Coast",
      "code": "WC-001",
      "type": "geographic"
    },
    "assignable": {
      "id": 42,
      "name": "Acme Corp Lead",
      "type": "Lead"
    },
    "assigned_by_user": {
      "id": 5,
      "name": "John Smith",
      "email": "john.smith@example.com"
    }
  }
}
```

---

### 3. Create Manual Assignment

Create a new manual territory assignment.

**Endpoint**: `POST /api/territory-assignments`

**Request Body**:
```json
{
  "territory_id": 1,
  "assignable_type": "lead",
  "assignable_id": 42,
  "transfer_ownership": true
}
```

**Validation Rules**:
- `territory_id`: required, must exist in territories table
- `assignable_type`: required, must be `lead`, `organization`, or `person`
- `assignable_id`: required, integer, min 1
- `transfer_ownership`: optional, boolean (default: true)

**Response Example** (201 Created):
```json
{
  "data": {
    "id": 150,
    "territory_id": 1,
    "assignable_type": "Webkul\\Lead\\Models\\Lead",
    "assignable_id": 42,
    "assigned_by": 5,
    "assignment_type": "manual",
    "assigned_at": "2024-01-15T18:00:00.000000Z",
    "created_at": "2024-01-15T18:00:00.000000Z",
    "updated_at": "2024-01-15T18:00:00.000000Z",
    "territory": {
      "id": 1,
      "name": "West Coast",
      "code": "WC-001",
      "type": "geographic"
    },
    "assigned_by_user": {
      "id": 5,
      "name": "John Smith",
      "email": "john.smith@example.com"
    }
  },
  "message": "Territory assignment created successfully."
}
```

**Error Response** (404 Not Found):
```json
{
  "message": "The specified entity was not found."
}
```

---

### 4. Delete Territory Assignment

Delete a territory assignment.

**Endpoint**: `DELETE /api/territory-assignments/{id}`

**Request Example**:
```bash
DELETE /api/territory-assignments/150
```

**Response Example**:
```json
{
  "message": "Territory assignment deleted successfully."
}
```

---

### 5. Reassign Entity to Different Territory

Reassign an entity from its current territory to a different territory.

**Endpoint**: `POST /api/territory-assignments/reassign`

**Request Body**:
```json
{
  "assignable_type": "lead",
  "assignable_id": 42,
  "territory_id": 3,
  "transfer_ownership": true
}
```

**Validation Rules**:
- `assignable_type`: required, must be `lead`, `organization`, or `person`
- `assignable_id`: required, integer, min 1
- `territory_id`: required, must exist in territories table
- `transfer_ownership`: optional, boolean (default: true)

**Response Example**:
```json
{
  "data": {
    "id": 151,
    "territory_id": 3,
    "assignable_type": "Webkul\\Lead\\Models\\Lead",
    "assignable_id": 42,
    "assigned_by": 5,
    "assignment_type": "manual",
    "assigned_at": "2024-01-15T19:00:00.000000Z",
    "created_at": "2024-01-15T19:00:00.000000Z",
    "updated_at": "2024-01-15T19:00:00.000000Z",
    "territory": {
      "id": 3,
      "name": "East Coast",
      "code": "EC-001",
      "type": "geographic"
    },
    "assigned_by_user": {
      "id": 5,
      "name": "John Smith",
      "email": "john.smith@example.com"
    }
  },
  "message": "Entity reassigned to territory successfully."
}
```

---

### 6. Bulk Reassign Entities

Reassign multiple entities to a different territory in a single request.

**Endpoint**: `POST /api/territory-assignments/bulk-reassign`

**Request Body**:
```json
{
  "assignment_ids": [1, 2, 3, 4, 5],
  "territory_id": 3,
  "transfer_ownership": true
}
```

**Validation Rules**:
- `assignment_ids`: required, must be an array with at least 1 element
- `assignment_ids.*`: must exist in territory_assignments table
- `territory_id`: required, must exist in territories table
- `transfer_ownership`: optional, boolean (default: true)

**Response Example**:
```json
{
  "message": "Entities reassigned to territory successfully."
}
```

**Error Response** (400 Bad Request):
```json
{
  "message": "Bulk reassignment failed. Please try again."
}
```

---

### 7. Get Assignment History for Entity

Retrieve the complete assignment history for a specific entity.

**Endpoint**: `GET /api/territory-assignments/history`

**Query Parameters**:
- `assignable_type`: required, must be `lead`, `organization`, or `person`
- `assignable_id`: required, integer

**Request Example**:
```bash
GET /api/territory-assignments/history?assignable_type=lead&assignable_id=42
```

**Response Example**:
```json
{
  "data": [
    {
      "id": 1,
      "territory_id": 1,
      "assignable_type": "Webkul\\Lead\\Models\\Lead",
      "assignable_id": 42,
      "assigned_by": 5,
      "assignment_type": "automatic",
      "assigned_at": "2024-01-10T10:00:00.000000Z",
      "created_at": "2024-01-10T10:00:00.000000Z",
      "updated_at": "2024-01-10T10:00:00.000000Z",
      "territory": {
        "id": 1,
        "name": "West Coast",
        "code": "WC-001",
        "type": "geographic"
      },
      "assigned_by_user": {
        "id": 5,
        "name": "John Smith",
        "email": "john.smith@example.com"
      }
    },
    {
      "id": 151,
      "territory_id": 3,
      "assignable_type": "Webkul\\Lead\\Models\\Lead",
      "assignable_id": 42,
      "assigned_by": 5,
      "assignment_type": "manual",
      "assigned_at": "2024-01-15T19:00:00.000000Z",
      "created_at": "2024-01-15T19:00:00.000000Z",
      "updated_at": "2024-01-15T19:00:00.000000Z",
      "territory": {
        "id": 3,
        "name": "East Coast",
        "code": "EC-001",
        "type": "geographic"
      },
      "assigned_by_user": {
        "id": 5,
        "name": "John Smith",
        "email": "john.smith@example.com"
      }
    }
  ]
}
```

---

### 8. Get Current Territory Assignment

Retrieve the current territory assignment for a specific entity.

**Endpoint**: `GET /api/territory-assignments/current`

**Query Parameters**:
- `assignable_type`: required, must be `lead`, `organization`, or `person`
- `assignable_id`: required, integer

**Request Example**:
```bash
GET /api/territory-assignments/current?assignable_type=lead&assignable_id=42
```

**Response Example**:
```json
{
  "data": {
    "id": 151,
    "territory_id": 3,
    "assignable_type": "Webkul\\Lead\\Models\\Lead",
    "assignable_id": 42,
    "assigned_by": 5,
    "assignment_type": "manual",
    "assigned_at": "2024-01-15T19:00:00.000000Z",
    "created_at": "2024-01-15T19:00:00.000000Z",
    "updated_at": "2024-01-15T19:00:00.000000Z",
    "territory": {
      "id": 3,
      "name": "East Coast",
      "code": "EC-001",
      "type": "geographic"
    },
    "assigned_by_user": {
      "id": 5,
      "name": "John Smith",
      "email": "john.smith@example.com"
    }
  }
}
```

**Response When No Assignment** (200 OK):
```json
{
  "data": null,
  "message": "No territory assignment found for this entity."
}
```

---

## Response Formats

### Success Response Structure

All successful responses follow this general structure:

```json
{
  "data": {},      // Single resource or array of resources
  "message": "",   // Optional success message
  "meta": {}       // Optional pagination metadata
}
```

### Pagination Metadata

For paginated endpoints, the `meta` object contains:

```json
{
  "meta": {
    "current_page": 1,    // Current page number
    "from": 1,            // First item number on current page
    "last_page": 5,       // Total number of pages
    "per_page": 15,       // Items per page
    "to": 15,             // Last item number on current page
    "total": 72           // Total number of items
  }
}
```

---

## Error Handling

### Error Response Structure

All error responses follow this structure:

```json
{
  "message": "Error description"
}
```

### Common HTTP Status Codes

- **200 OK**: Successful GET, PUT, PATCH, DELETE request
- **201 Created**: Successful POST request creating a new resource
- **400 Bad Request**: Invalid request data or operation failed
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation failed

### Validation Error Example

When validation fails, the response includes field-specific errors:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": [
      "The name field is required."
    ],
    "code": [
      "The code has already been taken."
    ],
    "type": [
      "The selected type is invalid."
    ]
  }
}
```

### Common Error Messages

**Territory Errors**:
- "Territory not found."
- "Territory could not be deleted."
- "Cannot set parent to self or descendant territory."

**Territory Rule Errors**:
- "Territory rule not found."
- "Territory rule could not be deleted."
- "Territory rule status toggle failed."
- "Priority update failed."

**Territory Assignment Errors**:
- "The specified entity was not found."
- "Territory assignment not found."
- "Territory assignment could not be deleted."
- "No territory assignment found for this entity."
- "Bulk reassignment failed. Please try again."

---

## Best Practices

### 1. Use Filtering and Pagination

For large datasets, always use pagination and filters:

```bash
GET /api/territories?status=active&per_page=25&sort_by=name&sort_order=asc
```

### 2. Include Relationships

Some endpoints automatically eager-load relationships. Check the response structure to see what data is included.

### 3. Handle Errors Gracefully

Always check the HTTP status code and handle errors appropriately in your application.

### 4. Use Bulk Operations

For multiple updates, use bulk endpoints when available:

```bash
POST /api/territory-rules/bulk-priorities
POST /api/territory-assignments/bulk-reassign
```

### 5. Transfer Ownership

When creating or reassigning territory assignments, consider whether ownership should be transferred:

```json
{
  "transfer_ownership": true  // Entity owner becomes territory owner
}
```

### 6. Rule Priority Management

Higher priority rules are evaluated first. Use priority to control rule evaluation order:

- 100 = Highest priority
- 0 = Lowest priority

---

## Complete Request Examples

### Example 1: Create Territory with Rules

**Step 1**: Create Territory
```bash
POST /api/territories
Content-Type: application/json

{
  "name": "California Tech",
  "code": "CA-TECH-001",
  "description": "California technology companies",
  "type": "geographic",
  "status": "active",
  "user_id": 5,
  "boundaries": "{\"type\":\"Polygon\",\"coordinates\":[[[-124.0,42.0],[-124.0,32.5],[-114.1,32.5],[-114.1,42.0],[-124.0,42.0]]]}"
}
```

**Step 2**: Create Geographic Rule
```bash
POST /api/territory-rules
Content-Type: application/json

{
  "territory_id": 1,
  "rule_type": "geographic",
  "field_name": "state",
  "operator": "=",
  "value": "\"CA\"",
  "priority": 100,
  "is_active": true
}
```

**Step 3**: Create Industry Rule
```bash
POST /api/territory-rules
Content-Type: application/json

{
  "territory_id": 1,
  "rule_type": "industry",
  "field_name": "industry",
  "operator": "in",
  "value": "[\"Technology\", \"Software\", \"SaaS\"]",
  "priority": 90,
  "is_active": true
}
```

---

### Example 2: Manually Assign Lead to Territory

```bash
POST /api/territory-assignments
Content-Type: application/json

{
  "territory_id": 1,
  "assignable_type": "lead",
  "assignable_id": 42,
  "transfer_ownership": true
}
```

---

### Example 3: Bulk Reassign Multiple Leads

**Step 1**: Get assignments to reassign
```bash
GET /api/territory-assignments?territory_id=1&assignable_type=Webkul\Lead\Models\Lead
```

**Step 2**: Bulk reassign
```bash
POST /api/territory-assignments/bulk-reassign
Content-Type: application/json

{
  "assignment_ids": [1, 2, 3, 4, 5],
  "territory_id": 3,
  "transfer_ownership": true
}
```

---

### Example 4: Get Territory Hierarchy and Statistics

**Step 1**: Get hierarchy
```bash
GET /api/territories/hierarchy
```

**Step 2**: Get statistics for each territory
```bash
GET /api/territories/1/statistics
GET /api/territories/2/statistics
```

---

## Changelog

### Version 1.0.0 (2024-01-15)
- Initial API release
- Territory CRUD operations
- Territory rule management
- Territory assignment management
- Hierarchy support
- Statistics and analytics endpoints

---

## Support

For questions or issues with the Territory Management API, please contact:
- Technical Support: support@example.com
- Documentation: https://docs.example.com/territory-api
- GitHub: https://github.com/example/laravel-crm

---

**Last Updated**: January 15, 2024
**API Version**: 1.0.0
**Laravel CRM Version**: 1.0.0
