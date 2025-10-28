# API Response Standards

## Overview
This document defines the standardized response format for all RESTful API endpoints in the ICT Learning Platform.

## Standard Response Structure

All API responses follow a consistent JSON structure:

```json
{
  "success": true/false,
  "message": "Human-readable message",
  "data": { ... },
  "errors": { ... },
  "meta": {
    "timestamp": "2025-10-27T10:30:00.000000Z",
    "api_version": "1.0"
  }
}
```

### Response Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `success` | boolean | Yes | Indicates whether the request was successful |
| `message` | string | Yes | Human-readable message describing the result |
| `data` | object/array | Conditional | Contains the requested resource(s). Present only on successful requests with data to return |
| `errors` | object/array | Conditional | Contains error details. Present only on failed requests |
| `meta` | object | Yes | Contains metadata about the response |

## HTTP Status Codes

### Success Codes
- **200 OK**: Request successful, data returned
- **201 Created**: Resource created successfully
- **204 No Content**: Request successful, no data to return (e.g., DELETE operations)

### Client Error Codes
- **400 Bad Request**: Invalid request data
- **401 Unauthorized**: Authentication required or failed
- **403 Forbidden**: User lacks permission to access resource
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation failed

### Server Error Codes
- **429 Too Many Requests**: Rate limit exceeded
- **500 Internal Server Error**: Unexpected server error

## Response Examples

### Success Response (200)
```json
{
  "success": true,
  "message": "Chapters retrieved successfully",
  "data": {
    "chapters": [
      {
        "id": 1,
        "title": "Introduction to ICT",
        "description": "Learn the basics",
        "progress_percentage": 75
      }
    ]
  },
  "meta": {
    "timestamp": "2025-10-27T10:30:00.000000Z",
    "api_version": "1.0"
  }
}
```

### Created Response (201)
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "token": "1|abcdef123456...",
    "user": {
      "id": 42,
      "email": "user@example.com",
      "fullName": "John Doe"
    }
  },
  "meta": {
    "timestamp": "2025-10-27T10:30:00.000000Z",
    "api_version": "1.0"
  }
}
```

### Validation Error Response (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password must be at least 6 characters."
    ]
  },
  "meta": {
    "timestamp": "2025-10-27T10:30:00.000000Z",
    "api_version": "1.0"
  }
}
```

### Not Found Response (404)
```json
{
  "success": false,
  "message": "Resource not found",
  "meta": {
    "timestamp": "2025-10-27T10:30:00.000000Z",
    "api_version": "1.0"
  }
}
```

### Unauthorized Response (401)
```json
{
  "success": false,
  "message": "Unauthenticated",
  "meta": {
    "timestamp": "2025-10-27T10:30:00.000000Z",
    "api_version": "1.0"
  }
}
```

### Server Error Response (500)
```json
{
  "success": false,
  "message": "Internal server error",
  "meta": {
    "timestamp": "2025-10-27T10:30:00.000000Z",
    "api_version": "1.0"
  }
}
```

**Note**: In development mode (`APP_DEBUG=true`), error responses include detailed debugging information:

```json
{
  "success": false,
  "message": "Internal server error",
  "errors": {
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException",
    "message": "The route api/invalid could not be found.",
    "file": "/var/www/vendor/laravel/framework/src/...",
    "line": 123,
    "trace": [...]
  },
  "meta": {
    "timestamp": "2025-10-27T10:30:00.000000Z",
    "api_version": "1.0"
  }
}
```

## Implementation

### Using the ApiResponse Trait

All controllers should use the `App\Traits\ApiResponse` trait to ensure consistent responses:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

class ExampleController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $data = ['items' => [...]];
        return $this->successResponse($data, 'Items retrieved successfully');
    }

    public function store(Request $request)
    {
        $item = Item::create($request->all());
        return $this->createdResponse(['item' => $item], 'Item created successfully');
    }

    public function show($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return $this->notFoundResponse('Item not found');
        }

        return $this->successResponse(['item' => $item], 'Item retrieved successfully');
    }
}
```

### Available Helper Methods

| Method | Description | Status Code |
|--------|-------------|-------------|
| `successResponse($data, $message)` | Generic success response | 200 |
| `createdResponse($data, $message)` | Resource created | 201 |
| `noContentResponse()` | Success with no data | 204 |
| `errorResponse($message, $code, $errors)` | Generic error | Custom |
| `validationErrorResponse($errors, $message)` | Validation failed | 422 |
| `notFoundResponse($message)` | Resource not found | 404 |
| `unauthorizedResponse($message)` | Authentication failed | 401 |
| `forbiddenResponse($message)` | Permission denied | 403 |
| `serverErrorResponse($message, $exception)` | Server error | 500 |

## Global Exception Handling

All exceptions are automatically caught and formatted consistently in `bootstrap/app.php`:

- **ModelNotFoundException** → 404 response
- **ValidationException** → 422 response
- **AuthenticationException** → 401 response
- **AuthorizationException** → 403 response
- **ThrottleRequestsException** → 429 response
- **All other exceptions** → 500 response

## Frontend Integration

When consuming these APIs from the frontend:

```javascript
// Success handling
fetch('/api/chapters')
  .then(response => response.json())
  .then(json => {
    if (json.success) {
      const chapters = json.data.chapters;
      // Use the data
    } else {
      // Handle error
      console.error(json.message);
      if (json.errors) {
        // Display validation errors
      }
    }
  });

// Error handling with try-catch
try {
  const response = await fetch('/api/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(userData)
  });

  const json = await response.json();

  if (!json.success) {
    if (response.status === 422) {
      // Handle validation errors
      Object.keys(json.errors).forEach(field => {
        console.log(`${field}: ${json.errors[field].join(', ')}`);
      });
    } else {
      // Handle other errors
      alert(json.message);
    }
  }
} catch (error) {
  console.error('Network error:', error);
}
```

## Benefits

1. **Consistency**: All endpoints follow the same structure
2. **Predictability**: Frontend developers know exactly what to expect
3. **Error Handling**: Clear, structured error messages
4. **Debugging**: Detailed error information in development mode
5. **Maintenance**: Centralized response logic
6. **Scalability**: Easy to add new features without breaking existing code

## Migration Notes

All controllers have been updated to use the standardized response format:
- ✅ AuthController
- ✅ ChapterController
- ✅ QuizController
- ✅ SlideController

No changes are required on existing API endpoints - the structure remains backward compatible with the `success` and `message` fields that were already in use.

## Version History

- **v1.0** (2025-10-27): Initial standardization
  - Added ApiResponse trait
  - Updated all controllers
  - Added global exception handling
  - Added metadata to all responses
