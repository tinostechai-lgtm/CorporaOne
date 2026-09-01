# Test Login API (Sanctum)

## 1) Endpoint
- **POST** `/api/login`

From `routes/api.php`:
- `Route::post('login', [AuthController::class, 'login']);`

## 2) Request body
Send JSON:
```json
{
  "email": "user@example.com",
  "password": "your_password"
}
```

## 3) Expected responses
### Success (200)
```json
{
  "success": true,
  "message": "Login successful",
  "token": "<sanctum_plain_text_token>",
  "user": {
    "id": 1,
    "name": "...",
    "email": "...",
    "type": "..."
  }
}
```

### Disabled account (403)
If `users.is_enable_login == 0`:
```json
{
  "success": false,
  "message": "Your account is disabled. Contact administrator."
}
```

### Wrong credentials (422)
ValidationException message:
```json
{
  "email": ["The provided credentials are incorrect."]
}
```

## 4) Authenticated request (example)
The API group uses `auth:sanctum`, so pass token as:
- `Authorization: Bearer <token>`

Example:
- **GET** `/api/me`

## 5) cURL commands
### Login
```bash
curl -X POST "http://127.0.0.1:8000/api/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"user@example.com\",\"password\":\"your_password\"}"
```

### Call /api/me using returned token
```bash
curl -X GET "http://127.0.0.1:8000/api/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

## 6) Postman checklist
- Method: POST
- URL: `{{baseUrl}}/api/login`
- Body: raw → JSON
- Headers: `Content-Type: application/json`
- Assert: `success === true` and `token` is present

## 7) Quick test coverage (recommended)
1. Valid credentials → 200 + token returned
2. Invalid password → 422
3. Disabled user (`is_enable_login=0`) → 403

