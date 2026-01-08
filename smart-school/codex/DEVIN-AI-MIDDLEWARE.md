# Smart School Management System - Middleware Implementation Prompts

This document contains detailed prompts for implementing middleware using DevIn AI.

---

## ?? How to Use This Guide

1. **Execute prompts in order** - Each prompt builds upon previous ones
2. **Read full context** - Understand purpose and integration before executing
3. **Verify completion** - Ensure each task is complete before moving to next
4. **Reference planning docs** - Use [`../plans/`](../plans/) for detailed architecture
5. **Reference backend prompts** - Use backend prompt files for controller implementation

---

## ?? Phase 1: Core Access Middleware (7 Prompts)

### Prompt 308: Create Role Middleware

**Purpose**: Restrict routes by user role.

**Functionality**: Blocks requests when user lacks required role.

**How it Works**:
- Creates `app/Http/Middleware/RoleMiddleware.php`
- Reads role from route middleware parameters
- Checks user roles using Spatie Permission
- Redirects or returns 403 for unauthorized access

**Integration**:
- Applied to admin/teacher/student/parent routes
- Used across web and API route groups

**Execute**: Implement RoleMiddleware and register in `app/Http/Kernel.php`.

---

### Prompt 309: Create Permission Middleware

**Purpose**: Restrict routes by fine-grained permissions.

**Functionality**: Validates user permissions for specific actions.

**How it Works**:
- Creates `app/Http/Middleware/PermissionMiddleware.php`
- Reads permission name from middleware parameter
- Checks permission using Spatie Permission
- Returns 403 or JSON error for API requests

**Integration**:
- Used on sensitive actions (create, update, delete)
- Supports per-module permission control

**Execute**: Implement PermissionMiddleware and register in kernel.

---

### Prompt 310: Create Module Access Middleware

**Purpose**: Guard module access based on system settings.

**Functionality**: Disables modules that are turned off in settings.

**How it Works**:
- Creates `app/Http/Middleware/ModuleAccessMiddleware.php`
- Reads module status from settings table/cache
- Blocks access when module is disabled
- Returns friendly error page or JSON response

**Integration**:
- Used by settings and module management UI
- Prevents access to disabled modules

**Execute**: Implement module access middleware and apply to module routes.

---

### Prompt 311: Create Academic Session Middleware

**Purpose**: Ensure requests are tied to the active academic session.

**Functionality**: Loads current session and blocks when missing.

**How it Works**:
- Creates `app/Http/Middleware/AcademicSessionMiddleware.php`
- Resolves current session from settings
- Stores session ID in request/container
- Redirects to session setup if not configured

**Integration**:
- Used by attendance, exams, fees, and timetable modules
- Provides shared session context to controllers

**Execute**: Implement academic session middleware and apply to core routes.

---

### Prompt 312: Create School Context Middleware

**Purpose**: Support multi-school context in a single deployment.

**Functionality**: Sets the active school based on domain or header.

**How it Works**:
- Creates `app/Http/Middleware/SchoolContextMiddleware.php`
- Detects school by subdomain or request header
- Loads school settings into container
- Blocks access if school is inactive

**Integration**:
- Used by settings, branding, and multi-tenant data scoping
- Required for multi-school installations

**Execute**: Implement school context middleware and apply globally.

---

### Prompt 313: Create Locale Middleware

**Purpose**: Apply user-selected language across requests.

**Functionality**: Sets locale based on session/user preference.

**How it Works**:
- Creates `app/Http/Middleware/LocaleMiddleware.php`
- Reads locale from session or user profile
- Calls `app()->setLocale($locale)`
- Fallback to default locale if missing

**Integration**:
- Used by all views and validation messages
- Connects to language switcher endpoints

**Execute**: Implement locale middleware and apply to web routes.

---

### Prompt 314: Create Timezone Middleware

**Purpose**: Set timezone for date/time operations.

**Functionality**: Applies school or user timezone settings.

**How it Works**:
- Creates `app/Http/Middleware/TimezoneMiddleware.php`
- Loads timezone from settings/user profile
- Calls `date_default_timezone_set($tz)`
- Shares timezone with view composer

**Integration**:
- Used by schedules, attendance, and notifications
- Ensures consistent time display across UI

**Execute**: Implement timezone middleware and register globally.

---

## ?? Phase 2: Security and Request Middleware (8 Prompts)

### Prompt 315: Create Force Password Change Middleware

**Purpose**: Force users to update default or expired passwords.

**Functionality**: Redirects users to password change screen.

**How it Works**:
- Creates `app/Http/Middleware/ForcePasswordChange.php`
- Checks `force_password_change` flag on user
- Allows access only to password routes
- Redirects to password update form

**Integration**:
- Used after admin resets or first login
- Improves account security

**Execute**: Implement middleware and apply to authenticated routes.

---

### Prompt 316: Create Two-Factor Middleware

**Purpose**: Require 2FA verification for sensitive actions.

**Functionality**: Blocks access if 2FA is not verified.

**How it Works**:
- Creates `app/Http/Middleware/TwoFactorMiddleware.php`
- Checks session flag for 2FA verification
- Redirects to 2FA challenge route if missing
- Allows trusted device exemptions

**Integration**:
- Used for finance, settings, and admin routes
- Works with notification OTP flow

**Execute**: Implement 2FA middleware and register for sensitive routes.

---

### Prompt 317: Create API Throttle Middleware

**Purpose**: Limit API requests to prevent abuse.

**Functionality**: Applies rate limits per user/IP.

**How it Works**:
- Creates `app/Http/Middleware/ApiThrottleMiddleware.php`
- Uses Laravel rate limiter with custom keys
- Returns 429 response on limit hit
- Adds retry headers in response

**Integration**:
- Applied to mobile API endpoints
- Protects payment and auth routes

**Execute**: Implement custom throttle middleware and register in kernel.

---

### Prompt 318: Create Audit Log Middleware

**Purpose**: Capture user actions for compliance.

**Functionality**: Logs request metadata for key actions.

**How it Works**:
- Creates `app/Http/Middleware/AuditLogMiddleware.php`
- Stores user ID, route, method, IP, and payload
- Excludes sensitive fields (passwords, tokens)
- Sends logs to database or log channel

**Integration**:
- Used by admin and finance modules
- Supports audit reports and investigations

**Execute**: Implement audit logging middleware and apply to protected routes.

---

### Prompt 319: Create File Access Middleware

**Purpose**: Restrict access to private documents.

**Functionality**: Ensures only authorized roles can download files.

**How it Works**:
- Creates `app/Http/Middleware/FileAccessMiddleware.php`
- Validates ownership or permission via policies
- Uses signed URLs for public access when allowed
- Returns 403 or 404 for unauthorized access

**Integration**:
- Used by student/teacher document downloads
- Works with secure file storage

**Execute**: Implement file access middleware and apply to download routes.

---

### Prompt 320: Create Parent-Child Access Middleware

**Purpose**: Restrict parents to their own children only.

**Functionality**: Ensures parents cannot access other students.

**How it Works**:
- Creates `app/Http/Middleware/ParentChildAccessMiddleware.php`
- Validates student ID against parent relationships
- Blocks access when student is not linked
- Returns 403 or redirect to children list

**Integration**:
- Used by parent routes for attendance, results, fees
- Protects student privacy

**Execute**: Implement parent-child access middleware and apply to parent routes.

---

### Prompt 321: Create Teacher-Class Access Middleware

**Purpose**: Restrict teachers to their assigned classes.

**Functionality**: Validates teacher assignments before data access.

**How it Works**:
- Creates `app/Http/Middleware/TeacherClassAccessMiddleware.php`
- Checks class/section ownership against teacher assignment
- Blocks access to other classes
- Returns 403 or redirect to teacher dashboard

**Integration**:
- Used by attendance, exams, and homework routes
- Prevents unauthorized class access

**Execute**: Implement teacher class access middleware and apply to teacher routes.

---

### Prompt 322: Register Middleware and Route Groups

**Purpose**: Wire all middleware into the application.

**Functionality**: Ensures middleware is available for route usage.

**How it Works**:
- Registers middleware aliases in `app/Http/Kernel.php`
- Adds global middleware where needed
- Applies middleware in route groups (`routes/web.php`, `routes/api.php`)
- Confirms middleware order for auth and localization

**Integration**:
- Enables role/permission protection system-wide
- Ensures locale/timezone setup runs on each request

**Execute**: Register middleware aliases and apply to route groups.

---

## ?? Summary

**Total Middleware Prompts: 15**

**Phases Covered:**
1. **Core Access Middleware** (7 prompts)
2. **Security and Request Middleware** (8 prompts)

**Features Implemented:**
- Role and permission guards
- Module access checks
- Academic session context
- School context handling
- Locale and timezone setup
- Password and 2FA enforcement
- API throttling and audit logging
- Secure file access checks
- Parent and teacher scoped access

**Next Steps:**
- Service Layer Prompts
- File Upload Handling Prompts
- Export Functionality Prompts
- Real-time Notifications Prompts
- Multi-language and RTL Prompts

---

## ?? Ready for Implementation

The middleware layer is now fully planned with comprehensive prompts for access control and request security.

**Happy Building with DevIn AI!** ??
