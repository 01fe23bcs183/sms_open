<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;

/**
 * API Translation Controller
 * 
 * Prompt 305: Implement Locale Switcher and JS Translations
 * 
 * Handles translation API endpoints for frontend JavaScript components.
 * Returns JSON translation keys for Alpine.js and other JS components.
 */
class TranslationController extends Controller
{
    /**
     * Cache TTL in seconds (1 hour)
     */
    private const CACHE_TTL = 3600;

    /**
     * Available translation groups.
     */
    private const AVAILABLE_GROUPS = [
        'nav' => 'Navigation translations',
        'common' => 'Common UI translations',
        'validation' => 'Validation messages',
        'messages' => 'Flash messages',
        'buttons' => 'Button labels',
        'forms' => 'Form labels',
        'tables' => 'Table headers',
        'dashboard' => 'Dashboard translations',
        'auth' => 'Authentication translations',
    ];

    /**
     * Get translations for specified groups.
     * 
     * GET /api/v1/translations?group=nav,common
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $locale = App::getLocale();
        $requestedGroups = $request->get('group', 'nav');
        
        $groups = is_string($requestedGroups) 
            ? array_map('trim', explode(',', $requestedGroups))
            : (array) $requestedGroups;

        $groups = array_filter($groups, fn($g) => array_key_exists($g, self::AVAILABLE_GROUPS));

        if (empty($groups)) {
            $groups = ['nav'];
        }

        $cacheKey = "translations_{$locale}_" . implode('_', $groups);

        $translations = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($locale, $groups) {
            return $this->loadTranslations($locale, $groups);
        });

        return $this->successResponse(
            $translations,
            'Translations retrieved successfully',
            [
                'locale' => $locale,
                'groups' => $groups,
                'direction' => $this->getDirection($locale),
            ]
        );
    }

    /**
     * Get all available translation groups.
     * 
     * GET /api/v1/translations/groups
     * 
     * @return JsonResponse
     */
    public function groups(): JsonResponse
    {
        return $this->successResponse(
            self::AVAILABLE_GROUPS,
            'Translation groups retrieved successfully'
        );
    }

    /**
     * Get all translations for the current locale.
     * 
     * GET /api/v1/translations/all
     * 
     * @return JsonResponse
     */
    public function all(): JsonResponse
    {
        $locale = App::getLocale();
        $cacheKey = "translations_{$locale}_all";

        $translations = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($locale) {
            return $this->loadTranslations($locale, array_keys(self::AVAILABLE_GROUPS));
        });

        return $this->successResponse(
            $translations,
            'All translations retrieved successfully',
            [
                'locale' => $locale,
                'direction' => $this->getDirection($locale),
            ]
        );
    }

    /**
     * Clear translation cache.
     * 
     * POST /api/v1/translations/clear-cache
     * 
     * @return JsonResponse
     */
    public function clearCache(): JsonResponse
    {
        $locales = ['en', 'es', 'fr', 'de', 'ar', 'hi', 'zh', 'ja', 'pt', 'ru'];
        $groups = array_keys(self::AVAILABLE_GROUPS);

        foreach ($locales as $locale) {
            Cache::forget("translations_{$locale}_all");
            
            foreach ($groups as $group) {
                Cache::forget("translations_{$locale}_{$group}");
            }

            $groupCombinations = $this->getGroupCombinations($groups);
            foreach ($groupCombinations as $combination) {
                Cache::forget("translations_{$locale}_" . implode('_', $combination));
            }
        }

        return $this->successResponse(
            null,
            'Translation cache cleared successfully'
        );
    }

    /**
     * Load translations for specified groups.
     * 
     * @param string $locale
     * @param array $groups
     * @return array
     */
    private function loadTranslations(string $locale, array $groups): array
    {
        $translations = [];

        foreach ($groups as $group) {
            $translations[$group] = $this->getGroupTranslations($locale, $group);
        }

        return $translations;
    }

    /**
     * Get translations for a specific group.
     * 
     * @param string $locale
     * @param string $group
     * @return array
     */
    private function getGroupTranslations(string $locale, string $group): array
    {
        $langPath = resource_path("lang/{$locale}/{$group}.php");
        
        if (File::exists($langPath)) {
            return include $langPath;
        }

        $fallbackPath = resource_path("lang/en/{$group}.php");
        
        if (File::exists($fallbackPath)) {
            return include $fallbackPath;
        }

        return $this->getDefaultTranslations($group);
    }

    /**
     * Get default translations for a group.
     * 
     * @param string $group
     * @return array
     */
    private function getDefaultTranslations(string $group): array
    {
        $defaults = [
            'nav' => [
                'dashboard' => 'Dashboard',
                'students' => 'Students',
                'teachers' => 'Teachers',
                'classes' => 'Classes',
                'sections' => 'Sections',
                'subjects' => 'Subjects',
                'attendance' => 'Attendance',
                'exams' => 'Exams',
                'fees' => 'Fees',
                'library' => 'Library',
                'transport' => 'Transport',
                'hostel' => 'Hostel',
                'reports' => 'Reports',
                'settings' => 'Settings',
                'profile' => 'Profile',
                'logout' => 'Logout',
            ],
            'common' => [
                'save' => 'Save',
                'cancel' => 'Cancel',
                'delete' => 'Delete',
                'edit' => 'Edit',
                'view' => 'View',
                'add' => 'Add',
                'search' => 'Search',
                'filter' => 'Filter',
                'export' => 'Export',
                'import' => 'Import',
                'print' => 'Print',
                'loading' => 'Loading...',
                'no_data' => 'No data available',
                'confirm' => 'Confirm',
                'yes' => 'Yes',
                'no' => 'No',
                'actions' => 'Actions',
                'status' => 'Status',
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
            'validation' => [
                'required' => 'This field is required',
                'email' => 'Please enter a valid email address',
                'min' => 'Minimum :min characters required',
                'max' => 'Maximum :max characters allowed',
                'numeric' => 'Please enter a valid number',
                'date' => 'Please enter a valid date',
                'unique' => 'This value already exists',
            ],
            'messages' => [
                'success' => 'Operation completed successfully',
                'error' => 'An error occurred',
                'warning' => 'Warning',
                'info' => 'Information',
                'created' => 'Record created successfully',
                'updated' => 'Record updated successfully',
                'deleted' => 'Record deleted successfully',
                'confirm_delete' => 'Are you sure you want to delete this record?',
            ],
            'buttons' => [
                'submit' => 'Submit',
                'reset' => 'Reset',
                'back' => 'Back',
                'next' => 'Next',
                'previous' => 'Previous',
                'close' => 'Close',
                'download' => 'Download',
                'upload' => 'Upload',
            ],
            'forms' => [
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'address' => 'Address',
                'date' => 'Date',
                'description' => 'Description',
                'remarks' => 'Remarks',
                'select' => 'Select...',
                'choose_file' => 'Choose file',
            ],
            'tables' => [
                'id' => 'ID',
                'name' => 'Name',
                'email' => 'Email',
                'status' => 'Status',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
                'actions' => 'Actions',
                'showing' => 'Showing',
                'of' => 'of',
                'entries' => 'entries',
            ],
            'dashboard' => [
                'welcome' => 'Welcome',
                'total_students' => 'Total Students',
                'total_teachers' => 'Total Teachers',
                'total_classes' => 'Total Classes',
                'fees_collected' => 'Fees Collected',
                'attendance_today' => 'Attendance Today',
                'recent_activities' => 'Recent Activities',
            ],
            'auth' => [
                'login' => 'Login',
                'logout' => 'Logout',
                'register' => 'Register',
                'forgot_password' => 'Forgot Password?',
                'remember_me' => 'Remember Me',
                'email_address' => 'Email Address',
                'password' => 'Password',
                'confirm_password' => 'Confirm Password',
            ],
        ];

        return $defaults[$group] ?? [];
    }

    /**
     * Get text direction for a locale.
     * 
     * @param string $locale
     * @return string
     */
    private function getDirection(string $locale): string
    {
        $rtlLocales = ['ar', 'he', 'fa', 'ur'];
        return in_array($locale, $rtlLocales) ? 'rtl' : 'ltr';
    }

    /**
     * Get common group combinations for cache clearing.
     * 
     * @param array $groups
     * @return array
     */
    private function getGroupCombinations(array $groups): array
    {
        $combinations = [];
        
        $combinations[] = ['nav'];
        $combinations[] = ['nav', 'common'];
        $combinations[] = ['common'];
        $combinations[] = ['validation'];
        $combinations[] = ['messages'];
        
        return $combinations;
    }
}
