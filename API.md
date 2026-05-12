# Food Safety & Premises Grading Management System
## API Documentation

### Base URL
```
http://your-domain.com/food-safety-system/api/
```

### Authentication
All API endpoints (except public search) require Bearer token authentication.

### Response Format
```json
{
    "success": true/false,
    "message": "Response message",
    "data": { ... }
}
```

### Endpoints

#### Public Endpoints

**1. Search Premises**
```
GET /api/search.php?query={search}&district={district}&grade={grade}
```
Response:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "shop_name": "Sunil Restaurant",
            "grade": "A",
            "district": "Colombo",
            "inspection_date": "2024-01-15"
        }
    ]
}
```

**2. Get Premises Details**
```
GET /api/premises.php?id={id}
```

**3. Verify Premises (QR)**
```
GET /api/verify.php?id={id}
```

#### Authenticated Endpoints

**4. Create Inspection**
```
POST /api/inspections.php
Headers: Authorization: Bearer {token}
Body: {
    "premises_id": 1,
    "inspection_date": "2024-06-01",
    "cleanliness": 5,
    "food_storage": 5,
    ...
}
```

**5. Submit Complaint**
```
POST /api/complaints.php
Body: {
    "shop_name": "Example Shop",
    "category": "unhygienic_conditions",
    "description": "Description...",
    "location": "Address...",
    "contact_details": "0771234567"
}
```

**6. Get Dashboard Stats**
```
GET /api/stats.php
Headers: Authorization: Bearer {token}
```

**7. Export Data**
```
GET /api/export.php?type={premises|complaints|workers}&format={excel|pdf}
Headers: Authorization: Bearer {token}
```

### Error Codes
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 429: Too Many Requests
- 500: Internal Server Error

### Rate Limiting
- Public endpoints: 100 requests per hour
- Authenticated endpoints: 1000 requests per hour
