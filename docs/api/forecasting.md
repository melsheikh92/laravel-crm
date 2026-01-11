# Sales Forecasting & Deal Scoring API Documentation
## AI-Powered Sales Forecasting and Predictive Deal Scoring

---

## 📖 Overview

This document provides comprehensive documentation for all sales forecasting and deal scoring API endpoints. These APIs enable programmatic access to revenue forecasting, deal scoring, win probability calculation, forecast accuracy tracking, and analytics features.

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
  "message": "Operation completed successfully",
  "data": { ... }
}
```

**Error Response:**
```json
{
  "message": "Error description"
}
```

### Common HTTP Status Codes
- `200` - OK - Request successful
- `201` - Created - Resource created successfully
- `400` - Bad Request - Invalid request parameters
- `401` - Unauthorized - Authentication required
- `403` - Forbidden - Insufficient permissions
- `404` - Not Found - Resource not found
- `422` - Unprocessable Entity - Validation failed
- `500` - Internal Server Error - Server error occurred

---

## 📊 Forecast Management API

Manage sales forecasts with scenario modeling and accuracy tracking.

### 1. List Forecasts

**Endpoint:** `GET /api/forecasts`

**Description:** Retrieve a paginated list of forecasts with optional filtering.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | No | Filter by user ID |
| `team_id` | integer | No | Filter by team ID |
| `period_type` | string | No | Filter by period type (week, month, quarter) |
| `start_date` | date | No | Filter by period start date (YYYY-MM-DD) |
| `end_date` | date | No | Filter by period end date (YYYY-MM-DD) |
| `per_page` | integer | No | Results per page (default: 15) |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/forecasts?period_type=month&per_page=10" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "team_id": 5,
      "period_type": "month",
      "period_start": "2024-02-01",
      "period_end": "2024-02-29",
      "forecast_value": 150000.00,
      "weighted_forecast": 120000.00,
      "best_case": 180000.00,
      "worst_case": 90000.00,
      "confidence_score": 85.5,
      "metadata": {
        "total_leads": 25,
        "pipeline_coverage": 1.8
      },
      "created_at": "2024-02-01T10:30:00.000000Z",
      "updated_at": "2024-02-01T10:30:00.000000Z",
      "user": {
        "id": 123,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "latestActual": null
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 10,
    "to": 10,
    "total": 25
  }
}
```

---

### 2. Get Specific Forecast

**Endpoint:** `GET /api/forecasts/{id}`

**Description:** Retrieve a specific forecast by ID with all actuals.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Forecast ID |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/forecasts/1" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "user_id": 123,
    "team_id": 5,
    "period_type": "month",
    "period_start": "2024-01-01",
    "period_end": "2024-01-31",
    "forecast_value": 150000.00,
    "weighted_forecast": 120000.00,
    "best_case": 180000.00,
    "worst_case": 90000.00,
    "confidence_score": 85.5,
    "metadata": {
      "total_leads": 25,
      "pipeline_coverage": 1.8,
      "average_deal_size": 6000.00
    },
    "created_at": "2024-01-01T10:30:00.000000Z",
    "updated_at": "2024-01-01T10:30:00.000000Z",
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "actuals": [
      {
        "id": 1,
        "forecast_id": 1,
        "actual_value": 135000.00,
        "variance": 15000.00,
        "variance_percentage": 12.5,
        "closed_at": "2024-02-01T00:00:00.000000Z"
      }
    ]
  }
}
```

---

### 3. Generate Forecast

**Endpoint:** `POST /api/forecasts/generate`

**Description:** Generate a new forecast for a user based on current pipeline data.

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `user_id` | integer | Yes | User ID to generate forecast for |
| `period_type` | string | Yes | Period type: week, month, quarter |
| `period_start` | date | No | Custom period start date (YYYY-MM-DD) |
| `team_id` | integer | No | Team ID for team-level forecast |

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/forecasts/generate" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": 123,
    "period_type": "month",
    "period_start": "2024-02-01",
    "team_id": 5
  }'
```

**Example Response:**
```json
{
  "message": "Forecast generated successfully.",
  "data": {
    "id": 2,
    "user_id": 123,
    "team_id": 5,
    "period_type": "month",
    "period_start": "2024-02-01",
    "period_end": "2024-02-29",
    "forecast_value": 150000.00,
    "weighted_forecast": 120000.00,
    "best_case": 180000.00,
    "worst_case": 90000.00,
    "confidence_score": 85.5,
    "metadata": {
      "total_leads": 25,
      "pipeline_coverage": 1.8,
      "calculation_method": "weighted_probability",
      "generated_at": "2024-02-01T10:30:00.000000Z"
    },
    "created_at": "2024-02-01T10:30:00.000000Z",
    "updated_at": "2024-02-01T10:30:00.000000Z"
  }
}
```

---

### 4. Get Team Forecasts

**Endpoint:** `GET /api/forecasts/team/{teamId}`

**Description:** Retrieve all forecasts for a specific team with aggregated totals.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `teamId` | integer | Yes | Team ID |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/forecasts/team/5" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "period_type": "month",
      "forecast_value": 150000.00,
      "weighted_forecast": 120000.00,
      "best_case": 180000.00,
      "worst_case": 90000.00,
      "confidence_score": 85.5,
      "user": {
        "id": 123,
        "name": "John Doe"
      }
    },
    {
      "id": 2,
      "user_id": 456,
      "period_type": "month",
      "forecast_value": 200000.00,
      "weighted_forecast": 160000.00,
      "best_case": 240000.00,
      "worst_case": 120000.00,
      "confidence_score": 80.0,
      "user": {
        "id": 456,
        "name": "Jane Smith"
      }
    }
  ],
  "totals": {
    "forecast_value": 350000.00,
    "weighted_forecast": 280000.00,
    "best_case": 420000.00,
    "worst_case": 210000.00,
    "avg_confidence": 82.75
  }
}
```

---

### 5. Get Forecast Accuracy

**Endpoint:** `GET /api/forecasts/accuracy`

**Description:** Retrieve forecast accuracy metrics by comparing forecasts with actual results.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | No | Filter by user ID |
| `team_id` | integer | No | Filter by team ID |
| `period_type` | string | No | Filter by period type (week, month, quarter) |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/forecasts/accuracy?user_id=123" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "period_type": "month",
      "period_start": "2024-01-01",
      "forecast_value": 150000.00,
      "weighted_forecast": 120000.00,
      "latestActual": {
        "actual_value": 135000.00,
        "variance": 15000.00,
        "variance_percentage": 12.5
      }
    }
  ],
  "metrics": {
    "total_forecasts": 6,
    "average_accuracy": 87.5,
    "average_variance": 12500.00,
    "average_variance_pct": 12.5,
    "over_forecasted_count": 2,
    "under_forecasted_count": 3,
    "accurate_count": 4,
    "accuracy_rate": 66.67
  }
}
```

---

## 🎯 Deal Scoring API

AI-powered deal scoring and win probability prediction.

### 1. Get Lead Score

**Endpoint:** `GET /api/leads/{id}/score`

**Description:** Retrieve the latest deal score for a specific lead.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Lead ID |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/leads/42/score" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": {
    "id": 15,
    "lead_id": 42,
    "score": 78.5,
    "win_probability": 65.0,
    "velocity_score": 80.0,
    "engagement_score": 85.0,
    "value_score": 70.0,
    "historical_pattern_score": 75.0,
    "factors": {
      "engagement": {
        "score": 85.0,
        "weight": 0.30,
        "details": {
          "email_count": 12,
          "activity_count": 8,
          "last_contact": "2024-01-15"
        }
      },
      "velocity": {
        "score": 80.0,
        "weight": 0.25,
        "details": {
          "days_in_stage": 5,
          "total_days_in_pipeline": 15,
          "expected_close_date": "2024-02-15"
        }
      },
      "value": {
        "score": 70.0,
        "weight": 0.20,
        "details": {
          "lead_value": 50000.00,
          "average_deal_size": 45000.00
        }
      },
      "historical_pattern": {
        "score": 75.0,
        "weight": 0.15,
        "details": {
          "similar_deals_won": 8,
          "similar_deals_total": 10
        }
      },
      "stage_probability": {
        "score": 60.0,
        "weight": 0.10,
        "details": {
          "current_stage": "Proposal",
          "stage_probability": 60.0
        }
      }
    },
    "generated_at": "2024-01-20T10:30:00.000000Z",
    "created_at": "2024-01-20T10:30:00.000000Z"
  },
  "statistics": {
    "score_trend": "increasing",
    "score_change": 5.5,
    "previous_score": 73.0,
    "rank_percentile": 85
  }
}
```

**Error Response (404):**
```json
{
  "message": "No score found for this lead. Please calculate the score first.",
  "data": null
}
```

---

### 2. Calculate Lead Score

**Endpoint:** `POST /api/leads/{id}/score/calculate`

**Description:** Calculate and update the deal score for a specific lead.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Lead ID |

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/leads/42/score/calculate" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "message": "Lead score calculated successfully.",
  "data": {
    "id": 16,
    "lead_id": 42,
    "score": 82.0,
    "win_probability": 70.0,
    "velocity_score": 85.0,
    "engagement_score": 88.0,
    "value_score": 75.0,
    "historical_pattern_score": 78.0,
    "factors": {
      "engagement": {
        "score": 88.0,
        "weight": 0.30
      },
      "velocity": {
        "score": 85.0,
        "weight": 0.25
      },
      "value": {
        "score": 75.0,
        "weight": 0.20
      },
      "historical_pattern": {
        "score": 78.0,
        "weight": 0.15
      },
      "stage_probability": {
        "score": 60.0,
        "weight": 0.10
      }
    },
    "generated_at": "2024-01-20T11:00:00.000000Z"
  }
}
```

---

### 3. Get Top Scored Leads

**Endpoint:** `GET /api/leads/top-scored`

**Description:** Retrieve top-scored leads with optional filtering.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `limit` | integer | No | Number of results (default: 10, max: 100) |
| `user_id` | integer | No | Filter by user ID |
| `priority` | string | No | Filter by priority (high, medium, low) |
| `min_score` | float | No | Minimum score (0-100) |
| `min_win_probability` | float | No | Minimum win probability (0-100) |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/leads/top-scored?limit=5&min_score=75" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 20,
      "lead_id": 55,
      "score": 92.5,
      "win_probability": 85.0,
      "velocity_score": 95.0,
      "engagement_score": 90.0,
      "value_score": 92.0,
      "historical_pattern_score": 88.0,
      "generated_at": "2024-01-20T10:30:00.000000Z",
      "lead": {
        "id": 55,
        "title": "Enterprise Software License",
        "lead_value": 250000.00,
        "user_id": 123,
        "status": 1
      }
    },
    {
      "id": 18,
      "lead_id": 42,
      "score": 88.0,
      "win_probability": 75.0,
      "velocity_score": 85.0,
      "engagement_score": 92.0,
      "value_score": 85.0,
      "historical_pattern_score": 82.0,
      "generated_at": "2024-01-20T10:30:00.000000Z",
      "lead": {
        "id": 42,
        "title": "SaaS Subscription Renewal",
        "lead_value": 120000.00,
        "user_id": 123,
        "status": 1
      }
    }
  ],
  "statistics": {
    "total_scored_leads": 156,
    "average_score": 65.5,
    "average_win_probability": 55.0,
    "high_priority_count": 45,
    "medium_priority_count": 78,
    "low_priority_count": 33
  },
  "distribution": {
    "90-100": 12,
    "80-89": 28,
    "70-79": 45,
    "60-69": 38,
    "50-59": 22,
    "0-49": 11
  },
  "meta": {
    "limit": 5,
    "count": 5
  }
}
```

---

## 📈 Forecast Analytics API

Advanced analytics for forecast trends, scenarios, and comparisons.

### 1. Get Forecast Trends

**Endpoint:** `GET /api/forecasts/analytics/trends`

**Description:** Analyze forecast and performance trends over time.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | No | Filter by user ID |
| `pipeline_id` | integer | No | Filter by pipeline ID |
| `months` | integer | No | Number of months to analyze (1-24, default: 6) |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/forecasts/analytics/trends?user_id=123&months=6" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": {
    "trends": [
      {
        "period": "2023-08",
        "total_won_value": 125000.00,
        "total_lost_value": 45000.00,
        "win_rate": 73.5,
        "total_leads": 28,
        "won_leads": 12,
        "forecast_count": 1,
        "total_forecast": 140000.00,
        "total_weighted": 115000.00,
        "avg_confidence": 82.0
      },
      {
        "period": "2023-09",
        "total_won_value": 145000.00,
        "total_lost_value": 38000.00,
        "win_rate": 79.2,
        "total_leads": 32,
        "won_leads": 15,
        "forecast_count": 1,
        "total_forecast": 155000.00,
        "total_weighted": 130000.00,
        "avg_confidence": 85.5
      }
    ],
    "analysis": {
      "direction": "upward",
      "growth_rate": 16.0,
      "volatility": "low",
      "summary": "Performance is trending upward with 16.00% growth. Low volatility - consistent performance across periods.",
      "recent_periods_analyzed": 3
    },
    "period": {
      "months": 6,
      "start_date": "2023-08-01",
      "end_date": "2024-01-31"
    }
  }
}
```

---

### 2. Get Forecast Scenarios

**Endpoint:** `GET /api/forecasts/analytics/scenarios`

**Description:** Generate scenario modeling (best case, worst case, weighted) for current pipeline.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | Yes | User ID for scenario modeling |
| `period_type` | string | No | Period type (week, month, quarter, default: month) |
| `pipeline_id` | integer | No | Filter by pipeline ID |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/forecasts/analytics/scenarios?user_id=123&period_type=month" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": {
    "scenarios": {
      "weighted": {
        "value": 145000.00,
        "description": "Most likely outcome based on stage probabilities"
      },
      "best_case": {
        "value": 280000.00,
        "description": "All deals close successfully"
      },
      "worst_case": {
        "value": 85000.00,
        "description": "Pessimistic scenario based on historical rates"
      }
    },
    "scenario_comparison": {
      "upside_potential": 135000.00,
      "downside_risk": 60000.00,
      "total_spread": 195000.00,
      "upside_percentage": 93.1,
      "downside_percentage": 41.38,
      "risk_reward_ratio": 2.25
    },
    "lead_breakdown": {
      "total_leads": 18,
      "total_value": 280000.00,
      "average_value": 15555.56
    },
    "recommendations": [
      "High upside potential detected. Focus on closing high-value deals to maximize outcomes.",
      "You have 5 high-value leads. Focus efforts on these opportunities."
    ],
    "period_type": "month"
  }
}
```

---

### 3. Get Forecast vs Actual Comparison

**Endpoint:** `GET /api/forecasts/analytics/comparison`

**Description:** Compare forecasts with actual results to analyze accuracy.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | No | Filter by user ID |
| `team_id` | integer | No | Filter by team ID |
| `period_type` | string | No | Filter by period type (week, month, quarter) |
| `limit` | integer | No | Number of results (default: 10) |

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/forecasts/analytics/comparison?user_id=123&limit=5" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": {
    "forecasts": [
      {
        "id": 1,
        "period_type": "month",
        "period_start": "2023-12-01",
        "period_end": "2023-12-31",
        "forecast_value": 150000.00,
        "weighted_forecast": 125000.00,
        "best_case": 180000.00,
        "worst_case": 90000.00,
        "user": {
          "id": 123,
          "name": "John Doe"
        },
        "latestActual": {
          "id": 1,
          "actual_value": 135000.00,
          "variance": 15000.00,
          "variance_percentage": 12.5,
          "closed_at": "2024-01-01T00:00:00.000000Z"
        }
      }
    ],
    "comparison_metrics": {
      "total_forecasts": 5,
      "average_accuracy": 87.5,
      "total_variance": 62500.00,
      "average_variance_pct": 12.5,
      "over_forecasted_count": 2,
      "under_forecasted_count": 3,
      "within_10_pct_count": 3,
      "accuracy_rate": 60.0
    },
    "accuracy_insights": {
      "overall_trend": "Good forecasting accuracy - minor adjustments may improve predictions.",
      "recommendations": [
        "Balanced forecasting pattern - no significant bias detected."
      ],
      "accuracy_score": 87.5
    },
    "period_type": "month"
  }
}
```

---

## 🔧 Data Structures

### Forecast Object

```json
{
  "id": 1,
  "user_id": 123,
  "team_id": 5,
  "period_type": "month",
  "period_start": "2024-02-01",
  "period_end": "2024-02-29",
  "forecast_value": 150000.00,
  "weighted_forecast": 120000.00,
  "best_case": 180000.00,
  "worst_case": 90000.00,
  "confidence_score": 85.5,
  "metadata": {
    "total_leads": 25,
    "pipeline_coverage": 1.8,
    "calculation_method": "weighted_probability"
  },
  "created_at": "2024-02-01T10:30:00.000000Z",
  "updated_at": "2024-02-01T10:30:00.000000Z"
}
```

### Deal Score Object

```json
{
  "id": 15,
  "lead_id": 42,
  "score": 78.5,
  "win_probability": 65.0,
  "velocity_score": 80.0,
  "engagement_score": 85.0,
  "value_score": 70.0,
  "historical_pattern_score": 75.0,
  "factors": {
    "engagement": {
      "score": 85.0,
      "weight": 0.30
    },
    "velocity": {
      "score": 80.0,
      "weight": 0.25
    }
  },
  "generated_at": "2024-01-20T10:30:00.000000Z"
}
```

---

## 🎯 Scoring Methodology

### Deal Score Calculation

The deal score (0-100) is calculated using a weighted composite of multiple factors:

1. **Engagement Score (30%)**: Based on email count, activity count, and recency of contact
2. **Velocity Score (25%)**: Based on deal progression speed and time in current stage
3. **Value Score (20%)**: Based on deal value relative to average deal size
4. **Historical Pattern Score (15%)**: Based on success rate of similar deals
5. **Stage Probability (10%)**: Based on current pipeline stage probability

### Win Probability

Win probability combines:
- Current stage probability
- Historical conversion rate for similar deals
- Deal velocity and engagement metrics
- AI-powered pattern recognition

---

## 🛡️ Authorization & Permissions

### User Access Control

- Users can only access their own forecasts and deal scores
- Team members with appropriate permissions can view team forecasts
- The `bouncer()->getAuthorizedUserIds()` method restricts data access based on user roles

### Required Permissions

| Action | Permission Required |
|--------|-------------------|
| View own forecasts | Authenticated user |
| Generate forecast | Authenticated user |
| View team forecasts | Team member or manager |
| View deal scores | Lead owner or manager |
| Calculate deal score | Lead owner or manager |

---

## 🐛 Error Handling

### Common Errors

**400 Bad Request - Invalid Period Type**
```json
{
  "message": "Invalid period type. Must be week, month, or quarter."
}
```

**403 Forbidden - Unauthorized Access**
```json
{
  "message": "Unauthorized access to this forecast."
}
```

**404 Not Found - Score Not Available**
```json
{
  "message": "No score found for this lead. Please calculate the score first.",
  "data": null
}
```

**422 Validation Error**
```json
{
  "message": "The user id field is required. (and 1 more error)",
  "errors": {
    "user_id": ["The user id field is required."],
    "period_type": ["The period type field must be one of: week, month, quarter."]
  }
}
```

**500 Server Error**
```json
{
  "message": "Failed to generate forecast: Insufficient pipeline data for forecast calculation."
}
```

---

## 💡 Best Practices

### 1. Forecast Generation

- Generate forecasts at the start of each period for best accuracy
- Use team forecasts for aggregated planning and quota management
- Monitor forecast accuracy regularly to improve prediction models

### 2. Deal Scoring

- Recalculate scores when significant lead changes occur (stage change, activity, etc.)
- Use top-scored leads to prioritize sales efforts
- Review score factors to understand what drives deal success

### 3. Analytics

- Analyze trends over at least 3 months for meaningful insights
- Use scenario modeling for pipeline planning and risk assessment
- Compare forecast vs actual regularly to identify and correct biases

### 4. Performance

- Forecast calculations are cached for 5-15 minutes
- Use pagination for large result sets
- Filter by user_id or team_id to reduce response size

---

## 📊 Use Cases

### Use Case 1: Monthly Revenue Forecasting

```bash
# 1. Generate forecast for current month
curl -X POST "https://your-domain.com/api/forecasts/generate" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"user_id": 123, "period_type": "month"}'

# 2. Get team aggregated forecast
curl -X GET "https://your-domain.com/api/forecasts/team/5" \
  -H "Authorization: Bearer YOUR_API_TOKEN"

# 3. Analyze forecast scenarios
curl -X GET "https://your-domain.com/api/forecasts/analytics/scenarios?user_id=123" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### Use Case 2: Deal Prioritization

```bash
# 1. Get top scored leads
curl -X GET "https://your-domain.com/api/leads/top-scored?limit=10&min_score=70" \
  -H "Authorization: Bearer YOUR_API_TOKEN"

# 2. Calculate score for a specific lead
curl -X POST "https://your-domain.com/api/leads/42/score/calculate" \
  -H "Authorization: Bearer YOUR_API_TOKEN"

# 3. Get detailed score breakdown
curl -X GET "https://your-domain.com/api/leads/42/score" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### Use Case 3: Forecast Accuracy Analysis

```bash
# 1. Get forecast accuracy metrics
curl -X GET "https://your-domain.com/api/forecasts/accuracy?user_id=123" \
  -H "Authorization: Bearer YOUR_API_TOKEN"

# 2. Compare forecast vs actual
curl -X GET "https://your-domain.com/api/forecasts/analytics/comparison?user_id=123&limit=6" \
  -H "Authorization: Bearer YOUR_API_TOKEN"

# 3. Analyze trends
curl -X GET "https://your-domain.com/api/forecasts/analytics/trends?user_id=123&months=6" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

---

## 🔄 Automated Jobs

The forecasting system includes several automated background jobs:

### Daily Jobs

- **Calculate Deal Scores**: Automatically recalculates scores for all active leads
  - Runs: Daily at 2:00 AM
  - Updates: Deal scores, win probability, factor breakdowns

### Weekly Jobs

- **Refresh Historical Conversions**: Updates historical conversion rate statistics
  - Runs: Weekly on Sunday at 3:00 AM
  - Updates: Stage conversion rates, average deal sizes, win rates

### Period-Based Jobs

- **Track Forecast Actuals**: Creates actual records when forecast periods end
  - Runs: Daily at 4:00 AM
  - Creates: Forecast actual records, calculates variance

---

## 📚 Related Documentation

- [Sales Forecasting User Guide](../user-guide/sales-forecasting.md)
- [Lead Management API](./leads.md)
- [Pipeline Configuration](./pipelines.md)

---

## 📝 Changelog

**Version 1.0.0** - January 2024
- Initial release
- Forecast Management API
- Deal Scoring API
- Forecast Analytics API
- Automated scoring and historical analysis
- Scenario modeling and accuracy tracking

---

## 💬 Support

For questions or issues with the Forecasting API, please:
- Review the API documentation thoroughly
- Check authentication and authorization settings
- Verify that leads have sufficient data for scoring
- Contact your system administrator for advanced configuration

---

**Last Updated:** January 2024
**API Version:** 1.0.0
**Feature:** Sales Forecasting & Deal Scoring
