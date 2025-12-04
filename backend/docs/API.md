# API Documentation

## School Management Platform - REST API

Base URL: `/api`

## Authentication

All API endpoints require JWT authentication except public endpoints.

### Headers
```
Authorization: Bearer <jwt_token>
Content-Type: application/json
```

---

## Payment Module

### Create Payment Plan
**POST** `/payments/plans`

**Role Required:** `ROLE_SECRETARIA`

**Request Body:**
```json
{
  "enrollment_id": 1,
  "total_amount": 1500.00,
  "num_installments": 10
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "payment_plan_id": "uuid-v7",
  "installments": [...]
}
```

---

### Record Installment Payment
**POST** `/payments/installments/{id}/pay`

**Role Required:** `ROLE_SECRETARIA`

**Request Body:**
```json
{
  "amount": 150.00,
  "payment_method": "cash",
  "reference": "REC-001"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Payment recorded successfully"
}
```

---

### Get Debtors Report
**GET** `/payments/debtors`

**Role Required:** `ROLE_SECRETARIA`

**Query Parameters:**
- `grade_id` (optional): Filter by grade
- `min_debt` (optional): Minimum debt amount

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "student_id": 1,
      "student_name": "Juan Pérez",
      "total_debt": 450.00,
      "overdue_installments": 3
    }
  ]
}
```

---

### Get Daily Closure
**GET** `/payments/daily-closure`

**Role Required:** `ROLE_SECRETARIA`

**Query Parameters:**
- `date` (optional): Date in YYYY-MM-DD format (default: today)

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "date": "2025-12-04",
    "total_received": 2500.00,
    "payment_count": 15,
    "by_method": {
      "cash": 1500.00,
      "card": 1000.00
    }
  }
}
```

---

## Contract Module

### Generate Contract
**POST** `/contracts/generate`

**Role Required:** `ROLE_SECRETARIA`

**Request Body:**
```json
{
  "enrollment_id": 1,
  "total_amount": 15000.00,
  "num_installments": 10
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "contract_id": "uuid-v7"
}
```

---

### Sign Contract
**POST** `/contracts/{id}/sign`

**Role Required:** `ROLE_PARENT`

**Request Body:**
```json
{
  "signature_data": "data:image/png;base64,..."
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Contract signed successfully"
}
```

---

### Download Contract PDF
**GET** `/contracts/{id}/download`

**Role Required:** `ROLE_USER`

**Response:** `200 OK`
- Content-Type: application/pdf
- Binary PDF data

---

### Get Contract Details
**GET** `/contracts/{id}`

**Role Required:** `ROLE_USER`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "status": "signed",
    "created_at": "2025-12-04T12:00:00Z",
    "signed_at": "2025-12-04T14:00:00Z"
  }
}
```

---

## Coordination Module

### Create Assignment
**POST** `/coordination/assignments`

**Role Required:** `ROLE_COORDINATOR`

**Request Body:**
```json
{
  "teacher_id": 1,
  "subject_id": 1,
  "grade_id": 1,
  "section_id": 1,
  "academic_year": 2025
}
```

**Response:** `201 Created`

---

### Get Teacher Assignments
**GET** `/coordination/assignments/teacher/{teacherId}`

**Role Required:** `ROLE_USER`

**Query Parameters:**
- `year` (optional): Academic year

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "subject_name": "Matemáticas",
      "grade_name": "Primero Básico",
      "section_name": "A"
    }
  ]
}
```

---

### Create Announcement
**POST** `/coordination/announcements`

**Role Required:** `ROLE_COORDINATOR`

**Request Body:**
```json
{
  "title": "School Meeting",
  "content": "Important meeting tomorrow...",
  "type": "general",
  "expires_at": "2025-12-31"
}
```

**Types:** `general`, `teachers`, `parents`, `students`, `specific_grade`

**Response:** `201 Created`

---

### Get Announcements
**GET** `/coordination/announcements`

**Role Required:** `ROLE_USER`

**Query Parameters:**
- `type` (optional): Filter by announcement type

**Response:** `200 OK`

---

### Create Calendar Event
**POST** `/coordination/calendar`

**Role Required:** `ROLE_COORDINATOR`

**Request Body:**
```json
{
  "title": "Final Exams",
  "description": "End of year exams",
  "start_date": "2025-12-15",
  "end_date": "2025-12-19",
  "type": "exam",
  "is_all_day": true
}
```

**Types:** `holiday`, `exam`, `activity`, `meeting`

**Response:** `201 Created`

---

### Get Calendar Events
**GET** `/coordination/calendar`

**Role Required:** `ROLE_USER`

**Query Parameters:**
- `start_date` (required): Start of date range
- `end_date` (required): End of date range

**Response:** `200 OK`

---

## Grades Module

### Record Grade
**POST** `/grades`

**Role Required:** `ROLE_TEACHER`

**Request Body:**
```json
{
  "student_id": 1,
  "subject_id": 1,
  "teacher_id": 1,
  "bimester": 1,
  "academic_year": 2025,
  "grade": 85.5,
  "comments": "Good performance"
}
```

**Response:** `201 Created`

---

### Bulk Record Grades
**POST** `/grades/bulk`

**Role Required:** `ROLE_TEACHER`

**Request Body:**
```json
{
  "teacher_id": 1,
  "subject_id": 1,
  "bimester": 1,
  "academic_year": 2025,
  "grades": [
    {"student_id": 1, "grade": 85.0},
    {"student_id": 2, "grade": 78.5}
  ]
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "recorded": 2,
  "errors": []
}
```

---

### Get Student Grades
**GET** `/grades/student/{studentId}`

**Role Required:** `ROLE_USER`

**Query Parameters:**
- `bimester` (optional): 1-4
- `year` (optional): Academic year

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "subject_name": "Matemáticas",
      "bimester": 1,
      "grade": 85.5,
      "letter_grade": "B",
      "is_passing": true
    }
  ]
}
```

---

### Close Bimester
**POST** `/grades/bimester/close`

**Role Required:** `ROLE_COORDINATOR`

**Request Body:**
```json
{
  "grade_id": 1,
  "bimester": 1,
  "academic_year": 2025
}
```

**Response:** `200 OK`

---

## Error Responses

All endpoints return consistent error responses:

### 400 Bad Request
```json
{
  "success": false,
  "error": "Invalid input: field X is required"
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "error": "Authentication required"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "error": "Access denied"
}
```

### 404 Not Found
```json
{
  "success": false,
  "error": "Resource not found"
}
```

### 409 Conflict
```json
{
  "success": false,
  "error": "Business rule violation"
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "error": "Internal server error"
}
```

---

## Rate Limiting

- 100 requests per minute per user
- 1000 requests per hour per user

## Versioning

Current API version: v1

Future versions will be available at `/api/v2/...`
