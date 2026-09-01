# CorporaOne - AI Integrated Business Management System

## Working Flow Documentation

---

## Table of Contents
1. [System Overview](#system-overview)
2. [Technology Stack](#technology-stack)
3. [Module Overview](#module-overview)
4. [User Authentication Flow](#user-authentication-flow)
5. [Core Modules Working Flow](#core-modules-working-flow)
6. [API Architecture](#api-architecture)
7. [Database Structure](#database-structure)
8. [Integration Services](#integration-services)
9. [Frontend Architecture](#frontend-architecture)

---

## 1. System Overview

CorporaOne is a comprehensive **AI-Integrated Business Management System** built on Laravel (PHP). It provides a complete ERP solution with HRM, CRM, Accounting, Project Management, POS, and multiple third-party integrations.

### Key Features:
- Multi-company support (SaaS)
- Role-based access control (RBAC)
- RESTful API for mobile/web integrations
- WhatsApp Business Integration
- IVR/VoxBay Integration
- Meta/Facebook Leads Integration
- Face Attendance System
- Payment Gateway Integrations (30+ gateways)

---

## 2. Technology Stack

| Component | Technology |
|-----------|-----------|
| Backend | Laravel 10.x |
| Database | MySQL |
| Authentication | Laravel Sanctum, JWT |
| Frontend | Blade Templates + Vue.js |
| API Documentation | REST API |
| Payment Gateways | 30+ integrated |
| Real-time | Pusher (Chatify) |
| File Storage | Local/S3 |

---

## 3. Module Overview

### Core Modules:
1. **HRM (Human Resource Management)**
   - Employee Management
   - Payroll & Salary
   - Leave Management
   - Attendance (Face/Biometric)
   - Performance Reviews
   - Training Management
   - Recruitment

2. **CRM (Customer Relationship Management)**
   - Lead Management
   - Deal Pipeline
   - Client Management
   - Contract Management

3. **Project Management**
   - Project Creation
   - Task Management
   - Timesheet Tracking
   - Bug Tracking
   - Gantt Charts

4. **Finance & Accounting**
   - Invoicing
   - Bill Management
   - Expense Tracking
   - Financial Reports
   - Bank Reconciliation

5. **POS (Point of Sale)**
   - Product Sales
   - Inventory Management
   - Barcode Support

6. **Communication**
   - Chat (Chatify)
   - WhatsApp Business
   - IVR Integration

---

## 4. User Authentication Flow

### Web Authentication Flow:
```
1. User visits /login
2. Enters credentials (email/password)
3. System validates via AuthController
4. Creates session + redirect to dashboard
5. Middleware verifies auth on each request
```

### API Authentication Flow (Sanctum):
```
1. Client POST /api/login with credentials
2. Server validates and returns Sanctum token
3. Client stores token and sends in header:
   Authorization: Bearer <token>
4. Protected endpoints verify token
5. Token can be revoked via /api/logout
```

### Multi-Company Login:
```
- Users can belong to multiple companies
- /users/{id}/login-with-company switches active company
- Session stores current company_id
```

---

## 5. Core Modules Working Flow

### 5.1 HRM Module

#### Employee Management:
```
Route: /employee (CRUD)
├── Create Employee → Personal Info, Department, Designation
├── Employee Profile → Documents, Salary, Leaves
├── Employee Documents → Contracts, Certificates
└── Employee Reports → Attendance, Payroll
```

#### Payroll Flow:
```
1. Setup Salary Components:
   - Basic Salary
   - Allowances (House, Transport, etc.)
   - Deductions (Tax, Insurance, Loans)
   
2. Generate Payslip:
   Route: /payslip
   - Select Month/Year
   - Auto-calculate based on attendance
   - Generate bulk or individual

3. Process Payment:
   - Bank Transfer
   - Cash
   - Cheque
```

#### Leave Management:
```
Employee Flow:
1. Request Leave → Select Type, Dates, Reason
2. Submit for Approval
3. Wait for HR/Manager Approval

HR Flow:
1. Review Leave Requests
2. Approve/Reject with comments
3. Update Leave Balance
```

#### Attendance System:
```
1. Face Attendance:
   - /face/enroll → Register face model
   - /face/recognize → Mark attendance
   - /face/mark → With location
   
2. Biometric:
   - API endpoint for device sync
   - Manual entry support
   
3. Bulk Attendance:
   - Import from CSV/Excel
```

### 5.2 CRM Module

#### Lead Management:
```
Lead Lifecycle:
Lead → Qualified → Proposal → Negotiation → Won/Lost

Features:
├── Web-to-Lead (Forms)
├── Import Leads
├── Lead Assignment
├── Lead Conversion to Deal
├── Email/Call Tracking
└── Lead Reports
```

#### Deal Pipeline:
```
Pipeline Stages:
Lead → Contact Made → Meeting → Proposal → Negotiation → Closed

Deal Management:
├── Create Deal from Lead
├── Assign Team Members
├── Add Products/Services
├── Track Activities
├── Add Notes & Files
└── Move through stages
```

### 5.3 Project Management

#### Project Creation:
```
1. Create Project
   - Name, Description
   - Start/End Date
   - Budget
   - Team Members

2. Create Tasks
   - Assign to team
   - Set priority
   - Add subtasks
   - Set due dates

3. Track Progress
   - Kanban Board
   - Gantt Chart
   - Timesheet
```

### 5.4 Finance Module

#### Invoice Flow:
```
1. Create Invoice
   - Add Customer
   - Add Products/Services
   - Apply Taxes
   - Add Discounts

2. Send Invoice
   - Email to customer
   - Generate PDF
   - Shareable link

3. Payment Tracking
   - Record Payments
   - Partial payments
   - Payment reminders

4. Reports
   - Income Summary
   - Tax Summary
   - Aging Report
```

#### Purchase Flow:
```
1. Create Purchase Order
2. Receive Goods
3. Create Bill
4. Record Payment
5. Vendor Reports
```

### 5.5 POS Module

#### POS Sales Flow:
```
1. Select Customer (Optional)
2. Scan/Search Products
3. Add to Cart
4. Apply Discounts
5. Select Payment Method
6. Complete Sale
7. Generate Receipt
8. Update Inventory
```

---

## 6. API Architecture

### API Endpoints Structure:

#### Authentication:
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/login | User login |
| POST | /api/logout | User logout |
| GET | /api/me | Get current user |

#### HRM API (/api):
```
Employees:
GET    /api/employees          - List all
POST   /api/employees          - Create
GET    /api/employees/{id}     - Get one
PUT    /api/employees/{id}     - Update
DELETE /api/employees/{id}    - Delete
GET    /api/employees/{id}/net-salary

Payroll:
GET    /api/payrolls
POST   /api/payrolls
POST   /api/payrolls/generate/{employeeId}

Leaves:
GET    /api/leaves
POST   /api/leaves
GET    /api/leave-types

Performance:
GET/POST/PUT/DELETE /api/appraisals
GET/POST/PUT/DELETE /api/goals
GET/POST/PUT/DELETE /api/competencies

Training:
GET/POST/PUT/DELETE /api/trainings
GET/POST/PUT/DELETE /api/trainers

Recruitment:
GET/POST/PUT/DELETE /api/jobs
GET/POST/PUT/DELETE /api/job-applications
GET/POST/PUT/DELETE /api/interview-schedules
```

#### CRM API:
```
Leads:
GET/POST/PUT/DELETE /api/leads
GET /api/leads-stages

Deals:
GET/POST/PUT/DELETE /api/deals
```

#### Reports API:
```
GET /api/reports/income-summary
GET /api/reports/expense-summary
GET /api/reports/income-vs-expense
GET /api/reports/tax-summary
```

---

## 7. Database Structure

### Key Tables:

#### Users & Auth:
- users (id, name, email, password, company_id, ...)

#### HRM:
- employees (user_id, department_id, designation_id, ...)
- departments
- designations
- leaves (employee_id, leave_type_id, start_date, end_date, status)
- leave_types
- payslips (employee_id, salary, allowance, deduction, ...)

#### CRM:
- leads (name, email, phone, pipeline_id, stage_id, ...)
- lead_stages
- deals (name, price, stage_id, ...)

#### Finance:
- invoices (customer_id, invoice_date, due_date, status, ...)
- invoice_products
- bills (vendor_id, ...)
- purchases
- accounts (chart_of_accounts)

#### Projects:
- projects
- project_tasks (project_id, milestone_id, ...)

#### Inventory:
- products
- warehouses
- warehouse_products

---

## 8. Integration Services

### 8.1 WhatsApp Integration

#### Setup:
```
Route: /whatsapp/settings
Config: config/whatsapp.php
Webhook: /api/whatsapp/webhook
```

#### Features:
- Send messages to customers
- Receive incoming messages
- Conversation management
- Template messages

### 8.2 IVR/VoxBay Integration

#### Setup:
```
Route: /ivr/settings
API: /api/ivr/make-call
Webhook: /api/ivr/webhook
```

#### Features:
- Make outbound calls
- Call recording
- Call history
- IVR configuration

### 8.3 Meta/Facebook Leads

#### Setup:
```
Route: /meta/settings
Webhook: /api/webhook/meta-leads
```

#### Features:
- Auto-sync leads from Facebook
- Lead attribution tracking

### 8.4 Payment Gateways

Integrated Gateways:
- PayPal, Stripe
- Paystack, Razorpay
- Paytm, Paytm
- Flutterwave
- Mercado Pago
- Mollie, Skrill
- And 20+ more...

#### Invoice Payment Flow:
```
1. Create Invoice
2. Customer selects payment method
3. Redirect to payment gateway
4. Customer completes payment
5. Webhook updates invoice status
6. Confirmation email sent
```

---

## 9. Frontend Architecture

### Blade Templates Structure:
```
resources/views/
├── layouts/
│   ├── admin.blade.php      - Main admin layout
│   └── landing.blade.php   - Landing page layout
├── dashboard/
│   ├── dashboard.blade.php
│   └── super_admin.blade.php
├── hrm/
├── crm/
├── pos/
├── invoice/
├── report/
├── frontend/               - Public pages
└── auth/                   - Login, Register
```

### Key Frontend Features:
- Dynamic sidebar navigation
- Role-based menu items
- AJAX for data loading
- Chart.js for reports
- DataTables for listings
- Select2 for dropdowns

---

## 10. Request Flow Diagram

### Web Request Flow:
```
Browser
    ↓
web.php routes
    ↓
Middleware (auth, verified, XSS)
    ↓
Controller
    ↓
Model (Database)
    ↓
View (Blade)
    ↓
Response
```

### API Request Flow:
```
Client App
    ↓
api.php routes
    ↓
Middleware (auth:sanctum)
    ↓
API Controller
    ↓
Model
    ↓
JSON Response
```

---

## 11. Key Configuration Files

| File | Purpose |
|------|---------|
| config/app.php | Application config |
| config/database.php | Database connection |
| config/auth.php | Auth guards |
| config/services.php | Third-party services |
| config/whatsapp.php | WhatsApp config |
| config/chatify.php | Chat config |

---

## 12. Security Features

- CSRF Protection
- XSS Filtering
- SQL Injection Prevention (Eloquent ORM)
- Rate Limiting
- IP Restriction
- Two-Factor Authentication support

---

## 13. Common Workflows

### Adding New Employee:
1. Navigate to HRM → Employee → Create
2. Fill personal details
3. Select department/designation
4. Assign shift schedule
5. Set salary components
6. Upload documents

### Creating Invoice:
1. Go to Invoice → Create
2. Select customer
3. Add products with quantities
4. Apply tax/discount
5. Set due date
6. Save and send

### Managing Project:
1. Create project with details
2. Add team members
3. Create task stages
4. Assign tasks
5. Track timesheet
6. Generate reports

---

## 14. Testing Endpoints

### Using Postman/cURL:
```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Get Employees (with token)
curl -X GET http://localhost:8000/api/employees \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 15. Console Commands

```bash
# Clear cache
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:clear

# Run migrations
php artisan migrate

# Create keys
php artisan key:generate
php artisan jwt:secret

# Queue worker
php artisan queue:work
```

---

## Conclusion

CorporaOne is a feature-rich ERP system with modular architecture. Each module operates independently while sharing common resources like users, companies, and settings. The system follows Laravel best practices and provides comprehensive APIs for third-party integrations.

For development or customization, refer to:
- Laravel Documentation
- Module-specific controllers
- Route definitions in routes/web.php and routes/api.php

---

*Document Generated: 2024*
*Version: 1.0*

