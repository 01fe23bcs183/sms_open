# Smart School Management System - API Endpoints and Documentation Prompts

This document contains detailed prompts for implementing API endpoints and documentation using DevIn AI.

---

## ?? How to Use This Guide

1. **Execute prompts in order** - Each prompt builds upon previous ones
2. **Read full context** - Understand purpose and integration before executing
3. **Verify completion** - Ensure each task is complete before moving to next
4. **Reference planning docs** - Use [`../plans/`](../plans/) for detailed architecture
5. **Reference backend prompts** - Use backend prompt files for controller implementation

---

## ?? Phase 1: API Foundation (4 Prompts)

### Prompt 423: Configure API Authentication

**Purpose**: Secure API access for mobile and external clients.

**Functionality**: Uses Laravel Sanctum for token-based auth.

**How it Works**:
- Installs `laravel/sanctum` package
- Publishes Sanctum config and migrations
- Adds `auth:sanctum` middleware to API routes
- Implements token issuance and revocation

**Integration**:
- Used by mobile app and third-party integrations
- Works with rate limiting and permissions

**Execute**: Configure Sanctum and enable API authentication.

---

### Prompt 424: Define API Versioning and Base Routes

**Purpose**: Organize API endpoints by version.

**Functionality**: Sets up `/api/v1` prefix and shared middleware.

**How it Works**:
- Updates `routes/api.php` with `Route::prefix('v1')`
- Applies `auth:sanctum` and `throttle` middleware
- Sets namespace for API controllers
- Adds default response format middleware

**Integration**:
- Used by all API endpoints
- Supports future version upgrades

**Execute**: Create versioned API route groups.

---

### Prompt 425: Create Auth API Endpoints

**Purpose**: Provide login and token management for API users.

**Functionality**: Implements login, logout, and refresh endpoints.

**How it Works**:
- Creates `Api/AuthController`
- Endpoints: `POST /auth/login`, `POST /auth/logout`, `POST /auth/refresh`
- Validates credentials and issues tokens
- Returns user profile and roles

**Integration**:
- Used by mobile apps and API clients
- Works with role-based access checks

**Execute**: Implement auth API endpoints and token responses.

---

### Prompt 426: Create Profile API Endpoints

**Purpose**: Allow users to fetch and update profile data.

**Functionality**: Exposes profile details and password changes.

**How it Works**:
- Creates `Api/ProfileController`
- Endpoints: `GET /profile`, `PUT /profile`, `PUT /profile/password`
- Uses Form Requests for validation
- Returns standardized JSON responses

**Integration**:
- Used by all authenticated users
- Supports profile updates in mobile app

**Execute**: Implement profile API endpoints.

---

## ?? Phase 2: Module API Endpoints (10 Prompts)

### Prompt 427: Create Student API Endpoints

**Purpose**: Expose student data and actions via API.

**Functionality**: CRUD and related endpoints for students.

**How it Works**:
- Creates `Api/StudentController`
- Endpoints: `GET /students`, `POST /students`, `GET /students/{id}`, `PUT /students/{id}`
- Adds endpoints for attendance, results, fees
- Uses resources for JSON formatting

**Integration**:
- Used by admin and parent mobile views
- Supports filters and pagination

**Execute**: Implement student API endpoints with resources.

---

### Prompt 428: Create Teacher API Endpoints

**Purpose**: Expose teacher data and assignments.

**Functionality**: Provides teacher profiles and class mappings.

**How it Works**:
- Creates `Api/TeacherController`
- Endpoints for list, show, classes, timetable
- Includes subject assignments in response
- Supports search and filters

**Integration**:
- Used by admin and teacher mobile views
- Supports timetable and attendance flows

**Execute**: Implement teacher API endpoints.

---

### Prompt 429: Create Academic API Endpoints

**Purpose**: Provide class, section, and subject data via API.

**Functionality**: Supports dropdowns and academic setup.

**How it Works**:
- Creates `Api/ClassController`, `Api/SectionController`, `Api/SubjectController`
- Endpoints for list and detail views
- Adds dependent endpoints for sections and subjects
- Returns lightweight JSON resources

**Integration**:
- Used by forms and mobile clients
- Supports dependent dropdowns

**Execute**: Implement academic API endpoints.

---

### Prompt 430: Create Attendance API Endpoints

**Purpose**: Provide attendance data and actions via API.

**Functionality**: Supports attendance marking and reports.

**How it Works**:
- Creates `Api/AttendanceController`
- Endpoints for list, mark, summary
- Accepts class/date filters
- Returns attendance statistics

**Integration**:
- Used by teacher and parent mobile views
- Works with AttendanceService

**Execute**: Implement attendance API endpoints.

---

### Prompt 431: Create Exam API Endpoints

**Purpose**: Provide exam schedules and results via API.

**Functionality**: Exposes exam lists, schedules, and marks.

**How it Works**:
- Creates `Api/ExamController`
- Endpoints for exams and schedules
- Adds result and grade endpoints
- Returns report card data

**Integration**:
- Used by student and parent apps
- Works with ResultService

**Execute**: Implement exam API endpoints.

---

### Prompt 432: Create Fees API Endpoints

**Purpose**: Provide fee invoices and payments via API.

**Functionality**: Lists fees, dues, and payment history.

**How it Works**:
- Creates `Api/FeesController`
- Endpoints for invoices, payments, receipts
- Supports online payment initiation
- Returns payment status and references

**Integration**:
- Used by parent app payment flow
- Works with PaymentService

**Execute**: Implement fees API endpoints.

---

### Prompt 433: Create Library API Endpoints

**Purpose**: Provide library catalog and issue data via API.

**Functionality**: Exposes books, issues, and returns.

**How it Works**:
- Creates `Api/LibraryController`
- Endpoints for books and member issues
- Supports search by title/author
- Returns availability status

**Integration**:
- Used by librarian and student apps
- Works with LibraryService

**Execute**: Implement library API endpoints.

---

### Prompt 434: Create Transport API Endpoints

**Purpose**: Provide transport routes and allocations via API.

**Functionality**: Exposes routes, stops, and assigned students.

**How it Works**:
- Creates `Api/TransportController`
- Endpoints for routes and allocations
- Includes vehicle and driver details
- Supports route filters

**Integration**:
- Used by admin and parent apps
- Works with TransportService

**Execute**: Implement transport API endpoints.

---

### Prompt 435: Create Hostel API Endpoints

**Purpose**: Provide hostel rooms and allocations via API.

**Functionality**: Exposes hostel status and assignments.

**How it Works**:
- Creates `Api/HostelController`
- Endpoints for rooms and allocations
- Includes occupancy and availability
- Supports hostel filters

**Integration**:
- Used by hostel management and parent views
- Works with HostelService

**Execute**: Implement hostel API endpoints.

---

### Prompt 436: Create Communication API Endpoints

**Purpose**: Provide notices, messages, and notifications via API.

**Functionality**: Exposes inbox, sent, and notice lists.

**How it Works**:
- Creates `Api/CommunicationController`
- Endpoints for notices and messages
- Includes notification fetch and read actions
- Supports pagination and search

**Integration**:
- Used by all roles in mobile app
- Works with NotificationService

**Execute**: Implement communication API endpoints.

---

### Prompt 437: Create API Documentation and Examples

**Purpose**: Document endpoints for developers and clients.

**Functionality**: Produces OpenAPI spec and Postman collection.

**How it Works**:
- Generates OpenAPI schema with request/response examples
- Documents auth flow and headers
- Provides Postman collection with sample environments
- Adds API usage guide in `docs/`

**Integration**:
- Used by mobile and frontend teams
- Ensures consistent API usage

**Execute**: Create OpenAPI docs and Postman collection.

---

## ?? Summary

**Total API Endpoint and Docs Prompts: 15**

**Phases Covered:**
1. **API Foundation** (4 prompts)
2. **Module API Endpoints** (10 prompts)
3. **API Documentation** (1 prompt)

**Features Implemented:**
- Sanctum authentication and versioned routes
- Module-specific API endpoints
- Consistent JSON resources
- OpenAPI and Postman documentation

**Next Steps:**
- Middleware Implementation Prompts
- Service Layer Prompts
- Real-time Notifications Prompts

---

## ?? Ready for Implementation

The API layer is now fully planned with comprehensive prompts for endpoint coverage and documentation.

**Happy Building with DevIn AI!** ??
