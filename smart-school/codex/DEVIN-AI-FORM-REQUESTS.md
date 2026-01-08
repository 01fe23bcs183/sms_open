# Smart School Management System - Form Request Validation Prompts

This document contains detailed prompts for creating form request validation classes using DevIn AI.

---

## 📋 How to Use This Guide

1. **Execute prompts in order** - Each prompt builds upon previous ones
2. **Read full context** - Understand purpose and integration before executing
3. **Verify completion** - Ensure each task is complete before moving to next
4. **Reference planning docs** - Use [`../plans/`](../plans/) for detailed architecture
5. **Reference backend prompts** - Use backend prompt files for controller implementation

---

## 🚀 Phase 1: Student Form Requests (10 Prompts)

### Prompt 338: Create Student Store Form Request

**Purpose**: Create form request for storing new student.

**Functionality**: Validates student creation form data.

**How it Works**:
- Runs `php artisan make:request StudentStoreRequest`
- Opens `app/Http/Requests/StudentStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates first_name (required, string, max:255)
  - Validates last_name (required, string, max:255)
  - Validates email (required, email, unique:users,email)
  - Validates phone (required, string, max:20)
  - Validates date_of_birth (required, date)
  - Validates gender (required, in:male,female,other)
  - Validates blood_group (nullable, string, max:10)
  - Validates religion (nullable, string, max:100)
  - Validates caste (nullable, string, max:100)
  - Validates address (required, string)
  - Validates city (required, string, max:100)
  - Validates state (required, string, max:100)
  - Validates country (required, string, max:100)
  - Validates zip_code (required, string, max:20)
  - Validates class_id (required, exists:classes,id)
  - Validates section_id (required, exists:sections,id)
  - Validates academic_session_id (required, exists:academic_sessions,id)
  - Validates admission_date (required, date)
  - Validates roll_number (required, string, max:50)
  - Validates admission_number (required, string, max:50, unique:students,admission_number)
  - Validates photo (nullable, image, mimes:jpeg,png,jpg,gif,svg, max:2048)
  - Validates parent_id (required, exists:users,id)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by StudentController store method
- Validates student creation form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create StudentStoreRequest with all validation rules.

---

### Prompt 339: Create Student Update Form Request

**Purpose**: Create form request for updating student.

**Functionality**: Validates student update form data.

**How it Works**:
- Runs `php artisan make:request StudentUpdateRequest`
- Opens `app/Http/Requests/StudentUpdateRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates first_name (required, string, max:255)
  - Validates last_name (required, string, max:255)
  - Validates email (required, email, unique:users,email,{id})
  - Validates phone (required, string, max:20)
  - Validates date_of_birth (required, date)
  - Validates gender (required, in:male,female,other)
  - Validates blood_group (nullable, string, max:10)
  - Validates religion (nullable, string, max:100)
  - Validates caste (nullable, string, max:100)
  - Validates address (required, string)
  - Validates city (required, string, max:100)
  - Validates state (required, string, max:100)
  - Validates country (required, string, max:100)
  - Validates zip_code (required, string, max:20)
  - Validates class_id (required, exists:classes,id)
  - Validates section_id (required, exists:sections,id)
  - Validates academic_session_id (required, exists:academic_sessions,id)
  - Validates admission_date (required, date)
  - Validates roll_number (required, string, max:50)
  - Validates admission_number (required, string, max:50, unique:students,admission_number,{id})
  - Validates photo (nullable, image, mimes:jpeg,png,jpg,gif,svg, max:2048)
  - Validates parent_id (required, exists:users,id)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by StudentController update method
- Validates student update form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create StudentUpdateRequest with all validation rules.

---

### Prompt 340: Create Teacher Store Form Request

**Purpose**: Create form request for storing new teacher.

**Functionality**: Validates teacher creation form data.

**How it Works**:
- Runs `php artisan make:request TeacherStoreRequest`
- Opens `app/Http/Requests/TeacherStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates first_name (required, string, max:255)
  - Validates last_name (required, string, max:255)
  - Validates email (required, email, unique:users,email)
  - Validates phone (required, string, max:20)
  - Validates date_of_birth (required, date)
  - Validates gender (required, in:male,female,other)
  - Validates blood_group (nullable, string, max:10)
  - Validates address (required, string)
  - Validates city (required, string, max:100)
  - Validates state (required, string, max:100)
  - Validates country (required, string, max:100)
  - Validates zip_code (required, string, max:20)
  - Validates joining_date (required, date)
  - Validates employee_id (required, string, max:50, unique:teachers,employee_id)
  - Validates designation (required, string, max:100)
  - Validates qualification (required, string, max:255)
  - Validates experience (required, numeric, min:0)
  - Validates salary (required, numeric, min:0)
  - Validates photo (nullable, image, mimes:jpeg,png,jpg,gif,svg, max:2048)
  - Validates documents (nullable, array)
  - Validates documents.* (nullable, file, mimes:pdf,doc,docx, max:5120)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by TeacherController store method
- Validates teacher creation form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create TeacherStoreRequest with all validation rules.

---

### Prompt 341: Create Teacher Update Form Request

**Purpose**: Create form request for updating teacher.

**Functionality**: Validates teacher update form data.

**How it Works**:
- Runs `php artisan make:request TeacherUpdateRequest`
- Opens `app/Http/Requests/TeacherUpdateRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates first_name (required, string, max:255)
  - Validates last_name (required, string, max:255)
  - Validates email (required, email, unique:users,email,{id})
  - Validates phone (required, string, max:20)
  - Validates date_of_birth (required, date)
  - Validates gender (required, in:male,female,other)
  - Validates blood_group (nullable, string, max:10)
  - Validates address (required, string)
  - Validates city (required, string, max:100)
  - Validates state (required, string, max:100)
  - Validates country (required, string, max:100)
  - Validates zip_code (required, string, max:20)
  - Validates joining_date (required, date)
  - Validates employee_id (required, string, max:50, unique:teachers,employee_id,{id})
  - Validates designation (required, string, max:100)
  - Validates qualification (required, string, max:255)
  - Validates experience (required, numeric, min:0)
  - Validates salary (required, numeric, min:0)
  - Validates photo (nullable, image, mimes:jpeg,png,jpg,gif,svg, max:2048)
  - Validates documents (nullable, array)
  - Validates documents.* (nullable, file, mimes:pdf,doc,docx, max:5120)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by TeacherController update method
- Validates teacher update form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create TeacherUpdateRequest with all validation rules.

---

### Prompt 342: Create Class Store Form Request

**Purpose**: Create form request for storing new class.

**Functionality**: Validates class creation form data.

**How it Works**:
- Runs `php artisan make:request ClassStoreRequest`
- Opens `app/Http/Requests/ClassStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates name (required, string, max:100, unique:classes,name)
  - Validates academic_session_id (required, exists:academic_sessions,id)
  - Validates section_ids (nullable, array)
  - Validates section_ids.* (nullable, exists:sections,id)
  - Validates subject_ids (nullable, array)
  - Validates subject_ids.* (nullable, exists:subjects,id)
  - Validates teacher_ids (nullable, array)
  - Validates teacher_ids.* (nullable, exists:users,id)
  - Validates capacity (nullable, numeric, min:1)
  - Validates room_number (nullable, string, max:50)
  - Validates floor (nullable, string, max:50)
  - Validates description (nullable, string)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by ClassController store method
- Validates class creation form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create ClassStoreRequest with all validation rules.

---

### Prompt 343: Create Class Update Form Request

**Purpose**: Create form request for updating class.

**Functionality**: Validates class update form data.

**How it Works**:
- Runs `php artisan make:request ClassUpdateRequest`
- Opens `app/Http/Requests/ClassUpdateRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates name (required, string, max:100, unique:classes,name,{id})
  - Validates academic_session_id (required, exists:academic_sessions,id)
  - Validates section_ids (nullable, array)
  - Validates section_ids.* (nullable, exists:sections,id)
  - Validates subject_ids (nullable, array)
  - Validates subject_ids.* (nullable, exists:subjects,id)
  - Validates teacher_ids (nullable, array)
  - Validates teacher_ids.* (nullable, exists:users,id)
  - Validates capacity (nullable, numeric, min:1)
  - Validates room_number (nullable, string, max:50)
  - Validates floor (nullable, string, max:50)
  - Validates description (nullable, string)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by ClassController update method
- Validates class update form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create ClassUpdateRequest with all validation rules.

---

### Prompt 344: Create Attendance Store Form Request

**Purpose**: Create form request for storing attendance.

**Functionality**: Validates attendance marking form data.

**How it Works**:
- Runs `php artisan make:request AttendanceStoreRequest`
- Opens `app/Http/Requests/AttendanceStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates date (required, date)
  - Validates class_id (required, exists:classes,id)
  - Validates section_id (required, exists:sections,id)
  - Validates subject_id (nullable, exists:subjects,id)
  - Validates teacher_id (required, exists:users,id)
  - Validates attendance_type (required, in:daily,subject)
  - Validates attendance (required, array)
  - Validates attendance.*.student_id (required, exists:students,id)
  - Validates attendance.*.status (required, in:present,absent,late,half_day)
  - Validates attendance.*.remarks (nullable, string, max:255)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by AttendanceController store method
- Validates attendance marking form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create AttendanceStoreRequest with all validation rules.

---

### Prompt 345: Create Exam Store Form Request

**Purpose**: Create form request for storing exam.

**Functionality**: Validates exam creation form data.

**How it Works**:
- Runs `php artisan make:request ExamStoreRequest`
- Opens `app/Http/Requests/ExamStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates name (required, string, max:255)
  - Validates exam_type_id (required, exists:exam_types,id)
  - Validates academic_session_id (required, exists:academic_sessions,id)
  - Validates class_id (required, exists:classes,id)
  - Validates section_ids (nullable, array)
  - Validates section_ids.* (nullable, exists:sections,id)
  - Validates start_date (required, date)
  - Validates end_date (required, date, after_or_equal:start_date)
  - Validates result_date (nullable, date, after:end_date)
  - Validates passing_marks (required, numeric, min:0)
  - Validates total_marks (required, numeric, min:0)
  - Validates remarks (nullable, string)
  - Validates status (required, in:draft,published,completed)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by ExamController store method
- Validates exam creation form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create ExamStoreRequest with all validation rules.

---

### Prompt 346: Create Exam Mark Store Form Request

**Purpose**: Create form request for storing exam marks.

**Functionality**: Validates exam marks entry form data.

**How it Works**:
- Runs `php artisan make:request ExamMarkStoreRequest`
- Opens `app/Http/Requests/ExamMarkStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates exam_id (required, exists:exams,id)
  - Validates subject_id (required, exists:subjects,id)
  - Validates class_id (required, exists:classes,id)
  - Validates section_id (required, exists:sections,id)
  - Validates marks (required, array)
  - Validates marks.*.student_id (required, exists:students,id)
  - Validates marks.*.obtained_marks (required, numeric, min:0)
  - Validates marks.*.remarks (nullable, string, max:255)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by ExamMarkController store method
- Validates exam marks entry form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create ExamMarkStoreRequest with all validation rules.

---

### Prompt 347: Create Fee Collect Form Request

**Purpose**: Create form request for collecting fees.

**Functionality**: Validates fee collection form data.

**How it Works**:
- Runs `php artisan make:request FeeCollectRequest`
- Opens `app/Http/Requests/FeeCollectRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates student_id (required, exists:students,id)
  - Validates fee_ids (required, array)
  - Validates fee_ids.* (required, exists:fees_allotments,id)
  - Validates payment_method (required, in:cash,cheque,dd,online,razorpay,stripe,paypal)
  - Validates amount (required, numeric, min:0)
  - Validates payment_date (required, date)
  - Validates reference_number (nullable, string, max:100)
  - Validates bank_name (nullable, string, max:100)
  - Validates cheque_date (nullable, date, required_if:payment_method,cheque)
  - Validates remarks (nullable, string, max:255)
  - Validates discount_amount (nullable, numeric, min:0)
  - Validates discount_reason (nullable, string, max:255)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by FeesTransactionController store method
- Validates fee collection form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create FeeCollectRequest with all validation rules.

---

## 🚀 Phase 2: Additional Form Requests (10 Prompts)

### Prompt 348: Create Library Book Store Form Request

**Purpose**: Create form request for storing library book.

**Functionality**: Validates library book creation form data.

**How it Works**:
- Runs `php artisan make:request LibraryBookStoreRequest`
- Opens `app/Http/Requests/LibraryBookStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates title (required, string, max:255)
  - Validates isbn (nullable, string, max:50, unique:library_books,isbn)
  - Validates author (required, string, max:255)
  - Validates publisher (nullable, string, max:255)
  - Validates category_id (required, exists:library_categories,id)
  - Validates publication_date (nullable, date)
  - Validates price (nullable, numeric, min:0)
  - Validates quantity (required, numeric, min:1)
  - Validates available_quantity (required, numeric, min:0, max:quantity)
  - Validates shelf_number (nullable, string, max:50)
  - Validates rack_number (nullable, string, max:50)
  - Validates description (nullable, string)
  - Validates cover_image (nullable, image, mimes:jpeg,png,jpg,gif,svg, max:2048)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by LibraryBookController store method
- Validates library book creation form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create LibraryBookStoreRequest with all validation rules.

---

### Prompt 349: Create Library Book Issue Form Request

**Purpose**: Create form request for issuing library book.

**Functionality**: Validates library book issue form data.

**How it Works**:
- Runs `php artisan make:request LibraryBookIssueRequest`
- Opens `app/Http/Requests/LibraryBookIssueRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates book_id (required, exists:library_books,id)
  - Validates member_id (required, exists:library_members,id)
  - Validates issue_date (required, date)
  - Validates due_date (required, date, after:issue_date)
  - Validates remarks (nullable, string, max:255)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by LibraryIssueController store method
- Validates library book issue form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create LibraryBookIssueRequest with all validation rules.

---

### Prompt 350: Create Transport Route Store Form Request

**Purpose**: Create form request for storing transport route.

**Functionality**: Validates transport route creation form data.

**How it Works**:
- Runs `php artisan make:request TransportRouteStoreRequest`
- Opens `app/Http/Requests/TransportRouteStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates name (required, string, max:255)
  - Validates route_number (required, string, max:50, unique:transport_routes,route_number)
  - Validates start_point (required, string, max:255)
  - Validates end_point (required, string, max:255)
  - Validates distance (nullable, numeric, min:0)
  - Validates fare (required, numeric, min:0)
  - Validates vehicle_id (required, exists:transport_vehicles,id)
  - Validates driver_id (required, exists:users,id)
  - Validates description (nullable, string)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by TransportRouteController store method
- Validates transport route creation form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create TransportRouteStoreRequest with all validation rules.

---

### Prompt 351: Create Hostel Store Form Request

**Purpose**: Create form request for storing hostel.

**Functionality**: Validates hostel creation form data.

**How it Works**:
- Runs `php artisan make:request HostelStoreRequest`
- Opens `app/Http/Requests/HostelStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates name (required, string, max:255)
  - Validates hostel_type (required, in:boys,girls,co-ed)
  - Validates address (required, string)
  - Validates city (required, string, max:100)
  - Validates state (required, string, max:100)
  - Validates country (required, string, max:100)
  - Validates zip_code (required, string, max:20)
  - Validates phone (required, string, max:20)
  - Validates email (nullable, email)
  - Validates capacity (required, numeric, min:1)
  - Validates warden_id (required, exists:users,id)
  - Validates description (nullable, string)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by HostelController store method
- Validates hostel creation form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create HostelStoreRequest with all validation rules.

---

### Prompt 352: Create Notice Store Form Request

**Purpose**: Create form request for storing notice.

**Functionality**: Validates notice creation form data.

**How it Works**:
- Runs `php artisan make:request NoticeStoreRequest`
- Opens `app/Http/Requests/NoticeStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates title (required, string, max:255)
  - Validates content (required, string)
  - Validates notice_type (required, in:general,exam,holiday,emergency,other)
  - Validates target_audience (required, in:all,students,teachers,parents,staff)
  - Validates class_ids (nullable, array)
  - Validates class_ids.* (nullable, exists:classes,id)
  - Validates section_ids (nullable, array)
  - Validates section_ids.* (nullable, exists:sections,id)
  - Validates publish_date (required, date)
  - Validates expiry_date (nullable, date, after_or_equal:publish_date)
  - Validates attachment (nullable, file, mimes:pdf,doc,docx, max:5120)
  - Validates status (required, in:draft,published,archived)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by NoticeController store method
- Validates notice creation form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create NoticeStoreRequest with all validation rules.

---

### Prompt 353: Create Message Store Form Request

**Purpose**: Create form request for storing message.

**Functionality**: Validates message creation form data.

**How it Works**:
- Runs `php artisan make:request MessageStoreRequest`
- Opens `app/Http/Requests/MessageStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates subject (required, string, max:255)
  - Validates message (required, string)
  - Validates recipient_type (required, in:individual,group,all)
  - Validates recipient_ids (nullable, array)
  - Validates recipient_ids.* (nullable, exists:users,id)
  - Validates attachment (nullable, file, mimes:pdf,doc,docx,jpg,jpeg,png, max:5120)
  - Validates send_email (nullable, boolean)
  - Validates send_sms (nullable, boolean)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by MessageController store method
- Validates message creation form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create MessageStoreRequest with all validation rules.

---

### Prompt 354: Create Homework Store Form Request

**Purpose**: Create form request for storing homework.

**Functionality**: Validates homework creation form data.

**How it Works**:
- Runs `php artisan make:request HomeworkStoreRequest`
- Opens `app/Http/Requests/HomeworkStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates title (required, string, max:255)
  - Validates description (required, string)
  - Validates class_id (required, exists:classes,id)
  - Validates section_ids (nullable, array)
  - Validates section_ids.* (nullable, exists:sections,id)
  - Validates subject_id (required, exists:subjects,id)
  - Validates teacher_id (required, exists:users,id)
  - Validates due_date (required, date, after_or_equal:today)
  - Validates submission_date (nullable, date, after_or_equal:due_date)
  - Validates marks (nullable, numeric, min:0)
  - Validates attachment (nullable, file, mimes:pdf,doc,docx,jpg,jpeg,png, max:5120)
  - Validates status (required, in:draft,published,closed)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by HomeworkController store method
- Validates homework creation form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create HomeworkStoreRequest with all validation rules.

---

### Prompt 355: Create Study Material Store Form Request

**Purpose**: Create form request for storing study material.

**Functionality**: Validates study material creation form data.

**How it Works**:
- Runs `php artisan make:request StudyMaterialStoreRequest`
- Opens `app/Http/Requests/StudyMaterialStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates title (required, string, max:255)
  - Validates description (nullable, string)
  - Validates material_type (required, in:notes,assignment,reference,other)
  - Validates class_id (required, exists:classes,id)
  - Validates section_ids (nullable, array)
  - Validates section_ids.* (nullable, exists:sections,id)
  - Validates subject_id (required, exists:subjects,id)
  - Validates teacher_id (required, exists:users,id)
  - Validates attachment (required, file, mimes:pdf,doc,docx,ppt,pptx, max:10240)
  - Validates status (required, in:draft,published)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by StudyMaterialController store method
- Validates study material creation form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create StudyMaterialStoreRequest with all validation rules.

---

### Prompt 356: Create Income Store Form Request

**Purpose**: Create form request for storing income.

**Functionality**: Validates income entry form data.

**How it Works**:
- Runs `php artisan make:request IncomeStoreRequest`
- Opens `app/Http/Requests/IncomeStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates income_type_id (required, exists:income_types,id)
  - Validates amount (required, numeric, min:0)
  - Validates date (required, date)
  - Validates description (required, string)
  - Validates reference_number (nullable, string, max:100)
  - Validates attachment (nullable, file, mimes:pdf,doc,docx,jpg,jpeg,png, max:5120)
  - Validates remarks (nullable, string, max:255)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by IncomeController store method
- Validates income entry form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create IncomeStoreRequest with all validation rules.

---

### Prompt 357: Create Expense Store Form Request

**Purpose**: Create form request for storing expense.

**Functionality**: Validates expense entry form data.

**How it Works**:
- Runs `php artisan make:request ExpenseStoreRequest`
- Opens `app/Http/Requests/ExpenseStoreRequest.php`
- Implements `authorize()` method:
  - Returns true for authorized users
  - Checks user permissions using Spatie Permission
- Implements `rules()` method:
  - Validates expense_type_id (required, exists:expense_types,id)
  - Validates amount (required, numeric, min:0)
  - Validates date (required, date)
  - Validates description (required, string)
  - Validates reference_number (nullable, string, max:100)
  - Validates attachment (nullable, file, mimes:pdf,doc,docx,jpg,jpeg,png, max:5120)
  - Validates remarks (nullable, string, max:255)
- Implements `messages()` method:
  - Custom error messages for all fields
- Implements `attributes()` method:
  - Custom attribute names for all fields

**Integration**:
- Used by ExpenseController store method
- Validates expense entry form
- Provides validation error messages
- Supports multi-language messages

**Execute**: Create ExpenseStoreRequest with all validation rules.

---

## ?? Phase 2: Academic and Operations Form Requests (15 Prompts)

### Prompt 358: Create Academic Session Store Form Request

**Purpose**: Create form request for creating academic sessions.

**Functionality**: Validates academic session creation data.

**How it Works**:
- Runs `php artisan make:request AcademicSessionStoreRequest`
- Opens `app/Http/Requests/AcademicSessionStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates name (required, string, max:100, unique:academic_sessions,name)
  - Validates start_date (required, date)
  - Validates end_date (required, date, after:start_date)
  - Validates is_current (boolean)
  - Validates status (required, in:active,inactive)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by AcademicSessionController store method
- Ensures current session logic is valid
- Supports multi-language validation messages

**Execute**: Create AcademicSessionStoreRequest with all validation rules.

---

### Prompt 359: Create Section Store Form Request

**Purpose**: Create form request for creating sections.

**Functionality**: Validates section creation data.

**How it Works**:
- Runs `php artisan make:request SectionStoreRequest`
- Opens `app/Http/Requests/SectionStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates class_id (required, exists:classes,id)
  - Validates name (required, string, max:100)
  - Validates capacity (nullable, integer, min:1)
  - Validates class_teacher_id (nullable, exists:users,id)
  - Validates status (required, in:active,inactive)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by SectionController store method
- Connects sections to classes
- Supports multi-language validation messages

**Execute**: Create SectionStoreRequest with all validation rules.

---

### Prompt 360: Create Subject Store Form Request

**Purpose**: Create form request for creating subjects.

**Functionality**: Validates subject creation data.

**How it Works**:
- Runs `php artisan make:request SubjectStoreRequest`
- Opens `app/Http/Requests/SubjectStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates class_id (required, exists:classes,id)
  - Validates name (required, string, max:150)
  - Validates code (required, string, max:50, unique:subjects,code)
  - Validates type (required, in:theory,practical)
  - Validates full_marks (required, numeric, min:0)
  - Validates pass_marks (required, numeric, min:0, lte:full_marks)
  - Validates teacher_id (nullable, exists:users,id)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by SubjectController store method
- Supports class-subject mapping
- Supports multi-language validation messages

**Execute**: Create SubjectStoreRequest with all validation rules.

---

### Prompt 361: Create Exam Type Store Form Request

**Purpose**: Create form request for creating exam types.

**Functionality**: Validates exam type creation data.

**How it Works**:
- Runs `php artisan make:request ExamTypeStoreRequest`
- Opens `app/Http/Requests/ExamTypeStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates name (required, string, max:100)
  - Validates code (required, string, max:20, unique:exam_types,code)
  - Validates description (nullable, string, max:500)
  - Validates is_active (boolean)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by ExamTypeController store method
- Used in exam scheduling
- Supports multi-language validation messages

**Execute**: Create ExamTypeStoreRequest with all validation rules.

---

### Prompt 362: Create Grade Scale Store Form Request

**Purpose**: Create form request for creating grade scales.

**Functionality**: Validates grading scale creation data.

**How it Works**:
- Runs `php artisan make:request GradeScaleStoreRequest`
- Opens `app/Http/Requests/GradeScaleStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates name (required, string, max:50)
  - Validates min_percent (required, numeric, min:0, max:100)
  - Validates max_percent (required, numeric, min:0, max:100, gte:min_percent)
  - Validates grade_point (nullable, numeric, min:0)
  - Validates remark (nullable, string, max:255)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by GradeController store method
- Used in report card calculations
- Supports multi-language validation messages

**Execute**: Create GradeScaleStoreRequest with all validation rules.

---

### Prompt 363: Create Fee Type Store Form Request

**Purpose**: Create form request for creating fee types.

**Functionality**: Validates fee type creation data.

**How it Works**:
- Runs `php artisan make:request FeeTypeStoreRequest`
- Opens `app/Http/Requests/FeeTypeStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates name (required, string, max:100)
  - Validates code (required, string, max:50, unique:fee_types,code)
  - Validates description (nullable, string, max:500)
  - Validates is_active (boolean)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by FeeTypeController store method
- Used in fee group/master setup
- Supports multi-language validation messages

**Execute**: Create FeeTypeStoreRequest with all validation rules.

---

### Prompt 364: Create Fee Group Store Form Request

**Purpose**: Create form request for creating fee groups.

**Functionality**: Validates fee group creation data.

**How it Works**:
- Runs `php artisan make:request FeeGroupStoreRequest`
- Opens `app/Http/Requests/FeeGroupStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates name (required, string, max:100)
  - Validates due_date (required, date)
  - Validates fine_rule_id (nullable, exists:fees_fines,id)
  - Validates description (nullable, string, max:500)
  - Validates status (required, in:active,inactive)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by FeeGroupController store method
- Used in fee master setup
- Supports multi-language validation messages

**Execute**: Create FeeGroupStoreRequest with all validation rules.

---

### Prompt 365: Create Fee Master Store Form Request

**Purpose**: Create form request for creating fee master items.

**Functionality**: Validates fee master creation data.

**How it Works**:
- Runs `php artisan make:request FeeMasterStoreRequest`
- Opens `app/Http/Requests/FeeMasterStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates fee_group_id (required, exists:fee_groups,id)
  - Validates fee_type_id (required, exists:fee_types,id)
  - Validates amount (required, numeric, min:0)
  - Validates is_optional (boolean)
  - Validates status (required, in:active,inactive)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by FeeMasterController store method
- Used in fee allotment and collection
- Supports multi-language validation messages

**Execute**: Create FeeMasterStoreRequest with all validation rules.

---

### Prompt 366: Create Fee Discount Store Form Request

**Purpose**: Create form request for creating fee discounts.

**Functionality**: Validates fee discount creation data.

**How it Works**:
- Runs `php artisan make:request FeeDiscountStoreRequest`
- Opens `app/Http/Requests/FeeDiscountStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates name (required, string, max:100)
  - Validates type (required, in:percentage,fixed)
  - Validates value (required, numeric, min:0)
  - Validates start_date (nullable, date)
  - Validates end_date (nullable, date, after_or_equal:start_date)
  - Validates status (required, in:active,inactive)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by FeeDiscountController store method
- Used in fee allotment calculation
- Supports multi-language validation messages

**Execute**: Create FeeDiscountStoreRequest with all validation rules.

---

### Prompt 367: Create Fee Allotment Store Form Request

**Purpose**: Create form request for allotting fees to students.

**Functionality**: Validates fee allotment data.

**How it Works**:
- Runs `php artisan make:request FeeAllotmentStoreRequest`
- Opens `app/Http/Requests/FeeAllotmentStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates student_id (required_without:class_id, exists:students,id)
  - Validates class_id (required_without:student_id, exists:classes,id)
  - Validates academic_session_id (required, exists:academic_sessions,id)
  - Validates fee_group_id (required, exists:fee_groups,id)
  - Validates discount_id (nullable, exists:fee_discounts,id)
  - Validates due_date (required, date)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by FeesAllotmentController store method
- Drives student fee invoices
- Supports multi-language validation messages

**Execute**: Create FeeAllotmentStoreRequest with all validation rules.

---

### Prompt 368: Create Fee Refund Form Request

**Purpose**: Create form request for processing fee refunds.

**Functionality**: Validates refund request data.

**How it Works**:
- Runs `php artisan make:request FeeRefundRequest`
- Opens `app/Http/Requests/FeeRefundRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates transaction_id (required, exists:fees_transactions,id)
  - Validates refund_amount (required, numeric, min:0)
  - Validates refund_method (required, in:cash,cheque,dd,online)
  - Validates reason (required, string, max:500)
  - Validates reference_number (nullable, string, max:100)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by FeesTransactionController refund method
- Updates payment status and ledger
- Supports multi-language validation messages

**Execute**: Create FeeRefundRequest with all validation rules.

---

### Prompt 369: Create Library Category Store Form Request

**Purpose**: Create form request for creating library categories.

**Functionality**: Validates library category data.

**How it Works**:
- Runs `php artisan make:request LibraryCategoryStoreRequest`
- Opens `app/Http/Requests/LibraryCategoryStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates name (required, string, max:100)
  - Validates code (nullable, string, max:50, unique:library_categories,code)
  - Validates description (nullable, string, max:500)
  - Validates is_active (boolean)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by LibraryCategoryController store method
- Used in book classification
- Supports multi-language validation messages

**Execute**: Create LibraryCategoryStoreRequest with all validation rules.

---

### Prompt 370: Create Library Member Store Form Request

**Purpose**: Create form request for creating library members.

**Functionality**: Validates library membership data.

**How it Works**:
- Runs `php artisan make:request LibraryMemberStoreRequest`
- Opens `app/Http/Requests/LibraryMemberStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates member_type (required, in:student,teacher,staff)
  - Validates user_id (required, exists:users,id)
  - Validates card_number (required, string, max:50, unique:library_members,card_number)
  - Validates start_date (required, date)
  - Validates end_date (nullable, date, after_or_equal:start_date)
  - Validates status (required, in:active,inactive)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by LibraryMemberController store method
- Used for issue/return tracking
- Supports multi-language validation messages

**Execute**: Create LibraryMemberStoreRequest with all validation rules.

---

### Prompt 371: Create Transport Vehicle Store Form Request

**Purpose**: Create form request for creating transport vehicles.

**Functionality**: Validates transport vehicle data.

**How it Works**:
- Runs `php artisan make:request TransportVehicleStoreRequest`
- Opens `app/Http/Requests/TransportVehicleStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates vehicle_number (required, string, max:50, unique:vehicles,vehicle_number)
  - Validates capacity (required, integer, min:1)
  - Validates driver_id (nullable, exists:users,id)
  - Validates route_id (nullable, exists:transport_routes,id)
  - Validates insurance_expiry (nullable, date)
  - Validates documents (nullable, file, mimes:pdf,jpg,jpeg,png, max:5120)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by VehicleController store method
- Links vehicles to routes and drivers
- Supports multi-language validation messages

**Execute**: Create TransportVehicleStoreRequest with all validation rules.

---

### Prompt 372: Create Hostel Room Store Form Request

**Purpose**: Create form request for creating hostel rooms.

**Functionality**: Validates hostel room data.

**How it Works**:
- Runs `php artisan make:request HostelRoomStoreRequest`
- Opens `app/Http/Requests/HostelRoomStoreRequest.php`
- Implements `authorize()` with permission checks
- Implements `rules()` method:
  - Validates hostel_id (required, exists:hostels,id)
  - Validates room_type_id (required, exists:hostel_room_types,id)
  - Validates room_number (required, string, max:50)
  - Validates capacity (required, integer, min:1)
  - Validates rent (required, numeric, min:0)
  - Validates status (required, in:available,occupied,maintenance)
- Implements `messages()` and `attributes()` methods

**Integration**:
- Used by HostelRoomController store method
- Used in hostel allocation flow
- Supports multi-language validation messages

**Execute**: Create HostelRoomStoreRequest with all validation rules.

---
## 📊 Summary

**Total Form Request Validation Prompts: 35**

**Phases Covered:**
1. **Student Form Requests** (10 prompts)
2. **Additional Form Requests** (10 prompts)
3. **Academic and Operations Form Requests** (15 prompts)
**Features Implemented:**
- Student store/update form requests
- Teacher store/update form requests
- Class store/update form requests
- Attendance store form request
- Exam store form request
- Exam mark store form request
- Fee collect form request
- Academic session form request
- Section form request
- Subject form request
- Exam type form request
- Grade scale form request
- Fee type form request
- Fee group form request
- Fee master form request
- Fee discount form request
- Fee allotment form request
- Fee refund form request
- Library category form request
- Library member form request
- Transport vehicle form request
- Hostel room form request
- Library book store/issue form requests
- Transport route store form request
- Hostel store form request
- Notice store form request
- Message store form request
- Homework store form request
- Study material store form request
- Income store form request
- Expense store form request

**Next Steps:**
- Middleware Implementation Prompts
- Service Layer Prompts
- File Upload Handling Prompts
- Export Functionality Prompts
- Real-time Notifications Prompts
- Multi-language in Views Prompts
- RTL Implementation Prompts

---

## 🚀 Ready for Implementation

The form request validation is now fully planned with comprehensive prompts for all form validations.

**Happy Building with DevIn AI!** 🚀


