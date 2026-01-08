# Smart School Management System - Multi-language and RTL Prompts

This document contains detailed prompts for implementing multi-language support and RTL layout using DevIn AI.

---

## ?? How to Use This Guide

1. **Execute prompts in order** - Each prompt builds upon previous ones
2. **Read full context** - Understand purpose and integration before executing
3. **Verify completion** - Ensure each task is complete before moving to next
4. **Reference planning docs** - Use [`../plans/`](../plans/) for detailed architecture
5. **Reference frontend prompts** - Use frontend prompt files for UI implementation

---

## ?? Phase 1: Localization Foundations (8 Prompts)

### Prompt 483: Define Translation Key Strategy

**Purpose**: Establish a consistent translation key structure.

**Functionality**: Defines namespaces and key naming conventions.

**How it Works**:
- Creates translation namespaces per module (students, fees, exams)
- Uses dot notation keys (e.g., `students.profile.title`)
- Documents naming rules in a short guide
- Ensures consistency for JS and Blade translations

**Integration**:
- Used by all Blade views and validation messages
- Supports localization for notifications and exports

**Execute**: Create translation key conventions and document them.

---

### Prompt 484: Initialize Language Files

**Purpose**: Set up language directories and base files.

**Functionality**: Creates default language files for core modules.

**How it Works**:
- Creates `resources/lang/en` and `resources/lang/ar`
- Adds base files for common UI labels
- Includes system messages and buttons
- Sets fallback locale in `config/app.php`

**Integration**:
- Used by the locale middleware and views
- Provides baseline translations

**Execute**: Create default language folders and base files.

---

### Prompt 485: Implement Locale Switcher Routes

**Purpose**: Allow users to change language.

**Functionality**: Stores selected locale in session/profile.

**How it Works**:
- Adds route `POST /locale`
- Creates `LocaleController@update`
- Stores locale in session and user profile
- Redirects back with flash message

**Integration**:
- Used by language switcher UI
- Works with LocaleMiddleware

**Execute**: Implement locale switcher routes and controller.

---

### Prompt 486: Create Locale Middleware

**Purpose**: Apply the selected locale to each request.

**Functionality**: Sets locale before controllers run.

**How it Works**:
- Creates `LocaleMiddleware` (if not already)
- Reads locale from session or user settings
- Calls `app()->setLocale($locale)`
- Falls back to default locale

**Integration**:
- Used globally across web routes
- Controls translations and date formatting

**Execute**: Implement and register LocaleMiddleware.

---

### Prompt 487: Translate Validation Messages

**Purpose**: Localize form validation errors.

**Functionality**: Adds language files for validation messages.

**How it Works**:
- Updates `resources/lang/en/validation.php`
- Adds `resources/lang/ar/validation.php`
- Maps field attributes for readability
- Supports custom messages from Form Requests

**Integration**:
- Used by Form Request validation responses
- Provides localized error feedback

**Execute**: Add translated validation messages.

---

### Prompt 488: Translate Navigation and Menus

**Purpose**: Localize sidebar and header labels.

**Functionality**: Adds translation keys for navigation items.

**How it Works**:
- Extracts nav labels into translation files
- Updates Blade templates to use `__('nav.key')`
- Adds module labels for each role
- Verifies menu alignment after translation

**Integration**:
- Used by all roles and layouts
- Supports dynamic menu generation

**Execute**: Replace hard-coded labels with translation keys.

---

### Prompt 489: Add JS Translation Endpoint

**Purpose**: Provide translations for JavaScript components.

**Functionality**: Returns translations as JSON for frontend use.

**How it Works**:
- Creates `/api/v1/translations` endpoint
- Returns module-specific translation keys
- Caches translation response per locale
- Adds JS helper to fetch and store translations

**Integration**:
- Used by Alpine.js and custom JS
- Enables localized alerts and validation messages

**Execute**: Implement translation JSON endpoint and JS helper.

---

### Prompt 490: Localize Email and SMS Templates

**Purpose**: Translate communication templates.

**Functionality**: Adds localized versions of emails and SMS messages.

**How it Works**:
- Creates Blade email templates per locale
- Uses translation keys for SMS messages
- Switches locale based on recipient preference
- Logs template language used

**Integration**:
- Used by NotificationService and mail jobs
- Supports multi-language communication

**Execute**: Localize email and SMS templates.

---

## ?? Phase 2: RTL Support (7 Prompts)

### Prompt 491: Add RTL Stylesheet

**Purpose**: Provide right-to-left layout styling.

**Functionality**: Adds RTL CSS adjustments for Bootstrap.

**How it Works**:
- Creates `resources/css/rtl.css`
- Overrides alignment, padding, and margins
- Sets `direction: rtl` for body
- Adjusts sidebar and header positioning

**Integration**:
- Loaded when locale is RTL
- Works with locale switcher

**Execute**: Create RTL stylesheet and include in layout.

---

### Prompt 492: Toggle RTL Layout in Blade

**Purpose**: Switch layout direction based on locale.

**Functionality**: Adds RTL classes and direction attributes.

**How it Works**:
- Adds `dir` and `lang` attributes to `<html>`
- Applies RTL class to body when locale is RTL
- Uses conditional helpers in Blade
- Updates layout to mirror sidebar placement

**Integration**:
- Used by all pages and components
- Works with RTL stylesheet

**Execute**: Update base layout to toggle RTL direction.

---

### Prompt 493: Enable RTL Bootstrap Build

**Purpose**: Ensure Bootstrap components render correctly in RTL.

**Functionality**: Builds or loads RTL-compatible Bootstrap CSS.

**How it Works**:
- Adds Bootstrap RTL build or separate CSS
- Ensures navbar, dropdown, and forms align correctly
- Verifies grid order and spacing
- Tests responsiveness in RTL mode

**Integration**:
- Used by all UI components
- Reduces custom RTL overrides

**Execute**: Add Bootstrap RTL build and include conditionally.

---

### Prompt 494: Fix RTL Charts and Tables

**Purpose**: Adjust charts and tables for RTL layout.

**Functionality**: Aligns axes, labels, and table columns.

**How it Works**:
- Updates Chart.js config for RTL tooltips
- Aligns table headers and cells to right
- Mirrors pagination controls
- Tests charts in RTL mode

**Integration**:
- Used by dashboards and reports
- Improves readability for RTL users

**Execute**: Apply RTL adjustments to charts and tables.

---

### Prompt 495: Localize Date, Time, and Numbers

**Purpose**: Display dates and numbers in locale format.

**Functionality**: Uses locale-specific formats.

**How it Works**:
- Uses Carbon localization for dates
- Adds number formatting helpers
- Updates frontend date pickers with locale
- Ensures export formatting respects locale

**Integration**:
- Used by reports, forms, and dashboards
- Consistent across UI and exports

**Execute**: Apply localized formatting helpers.

---

### Prompt 496: Add Language Management UI

**Purpose**: Manage available languages in admin settings.

**Functionality**: Enables enabling/disabling languages.

**How it Works**:
- Creates settings page for languages
- Stores enabled locales in database
- Validates RTL flags for each locale
- Updates locale switcher options dynamically

**Integration**:
- Used by admin settings and middleware
- Controls available languages for users

**Execute**: Build language management UI and settings.

---

### Prompt 497: Create i18n and RTL Test Checklist

**Purpose**: Validate localization and RTL changes.

**Functionality**: Ensures translations and layout alignments are correct.

**How it Works**:
- Creates checklist for pages and roles
- Tests key forms and tables in RTL
- Verifies translations for common actions
- Confirms locale switcher persistence

**Integration**:
- Used by QA and release checklist
- Ensures consistent multi-language UX

**Execute**: Document and run i18n/RTL checklist.

---

## ?? Summary

**Total i18n/RTL Prompts: 15**

**Phases Covered:**
1. **Localization Foundations** (8 prompts)
2. **RTL Support** (7 prompts)

**Features Implemented:**
- Translation key strategy and language files
- Locale switcher and middleware
- JS and email/SMS localization
- RTL layout and Bootstrap support

**Next Steps:**
- Queue Jobs Prompts
- Events and Listeners Prompts
- API Endpoints and Docs Prompts

---

## ?? Ready for Implementation

Multi-language and RTL support is now fully planned with comprehensive prompts for localization.

**Happy Building with DevIn AI!** ??
