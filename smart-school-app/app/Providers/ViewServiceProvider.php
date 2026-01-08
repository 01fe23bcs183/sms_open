<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * View Service Provider
 * 
 * Prompt 295: Implement View Composers for Global Layout Data
 * 
 * This provider registers view composers that inject shared data
 * into layouts and partials. Data is cached to reduce database queries.
 */
class ViewServiceProvider extends ServiceProvider
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    protected const CACHE_TTL = 300;

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Compose data for all layout views
        View::composer(['layouts.*', 'partials.*'], function ($view) {
            $this->composeLayoutData($view);
        });

        // Compose sidebar data
        View::composer(['partials.sidebar', 'layouts.sidebar'], function ($view) {
            $this->composeSidebarData($view);
        });

        // Compose header data
        View::composer(['partials.header', 'layouts.header'], function ($view) {
            $this->composeHeaderData($view);
        });

        // Compose breadcrumb data
        View::composer(['partials.breadcrumbs', 'components.breadcrumbs'], function ($view) {
            $this->composeBreadcrumbData($view);
        });
    }

    /**
     * Compose global layout data.
     *
     * @param \Illuminate\View\View $view
     * @return void
     */
    protected function composeLayoutData($view): void
    {
        // School settings (cached)
        $schoolSettings = $this->getSchoolSettings();
        $view->with('schoolSettings', $schoolSettings);

        // Active academic session (cached)
        $currentSession = $this->getCurrentAcademicSession();
        $view->with('currentSession', $currentSession);

        // Auth user data
        $authUser = auth()->user();
        $view->with('authUser', $authUser);

        // User roles and permissions
        if ($authUser) {
            $view->with('userRoles', $authUser->getRoleNames());
            $view->with('userPermissions', $authUser->getAllPermissions()->pluck('name'));
        } else {
            $view->with('userRoles', collect());
            $view->with('userPermissions', collect());
        }

        // Feature flags (cached)
        $featureFlags = $this->getFeatureFlags();
        $view->with('featureFlags', $featureFlags);

        // App locale
        $view->with('appLocale', app()->getLocale());
        $view->with('appDirection', $this->getTextDirection());
    }

    /**
     * Compose sidebar-specific data.
     *
     * @param \Illuminate\View\View $view
     * @return void
     */
    protected function composeSidebarData($view): void
    {
        $authUser = auth()->user();

        if (!$authUser) {
            $view->with('sidebarMenus', []);
            return;
        }

        // Get role-specific menu items
        $menus = $this->getMenusForUser($authUser);
        $view->with('sidebarMenus', $menus);

        // Active menu item based on current route
        $currentRoute = request()->route()?->getName() ?? '';
        $view->with('currentRoute', $currentRoute);
    }

    /**
     * Compose header-specific data.
     *
     * @param \Illuminate\View\View $view
     * @return void
     */
    protected function composeHeaderData($view): void
    {
        $authUser = auth()->user();

        // Unread notifications count
        $unreadNotificationsCount = 0;
        if ($authUser) {
            $unreadNotificationsCount = Cache::remember(
                "user_{$authUser->id}_unread_notifications",
                60, // 1 minute cache
                function () use ($authUser) {
                    return $authUser->unreadNotifications()->count();
                }
            );
        }
        $view->with('unreadNotificationsCount', $unreadNotificationsCount);

        // Recent notifications (last 5)
        $recentNotifications = collect();
        if ($authUser) {
            $recentNotifications = Cache::remember(
                "user_{$authUser->id}_recent_notifications",
                60,
                function () use ($authUser) {
                    return $authUser->notifications()->take(5)->get();
                }
            );
        }
        $view->with('recentNotifications', $recentNotifications);

        // Available languages
        $view->with('availableLanguages', $this->getAvailableLanguages());
    }

    /**
     * Compose breadcrumb data.
     *
     * @param \Illuminate\View\View $view
     * @return void
     */
    protected function composeBreadcrumbData($view): void
    {
        $breadcrumbs = $this->generateBreadcrumbs();
        $view->with('breadcrumbs', $breadcrumbs);
    }

    /**
     * Get school settings from cache or database.
     *
     * @return object
     */
    protected function getSchoolSettings(): object
    {
        return Cache::remember('school_settings', self::CACHE_TTL, function () {
            // Try to get from database if Setting model exists
            if (class_exists(\App\Models\Setting::class)) {
                $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
            } else {
                $settings = [];
            }

            // Default settings
            return (object) array_merge([
                'name' => config('app.name', 'Smart School'),
                'logo' => asset('images/logo.png'),
                'favicon' => asset('images/favicon.ico'),
                'tagline' => 'Smart School Management System',
                'email' => 'admin@smartschool.com',
                'phone' => '+1234567890',
                'address' => '123 School Street',
                'currency' => 'INR',
                'currency_symbol' => '₹',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'timezone' => config('app.timezone', 'UTC'),
                'theme_mode' => 'light',
                'primary_color' => '#0d6efd',
            ], $settings);
        });
    }

    /**
     * Get current academic session from cache or database.
     *
     * @return object|null
     */
    protected function getCurrentAcademicSession(): ?object
    {
        return Cache::remember('current_academic_session', self::CACHE_TTL, function () {
            if (class_exists(\App\Models\AcademicSession::class)) {
                return \App\Models\AcademicSession::where('is_current', true)->first();
            }
            return null;
        });
    }

    /**
     * Get feature flags from cache or config.
     *
     * @return object
     */
    protected function getFeatureFlags(): object
    {
        return Cache::remember('feature_flags', self::CACHE_TTL, function () {
            // Try to get from database if FeatureFlag model exists
            if (class_exists(\App\Models\FeatureFlag::class)) {
                $flags = \App\Models\FeatureFlag::pluck('enabled', 'name')->toArray();
            } else {
                $flags = [];
            }

            // Default feature flags
            return (object) array_merge([
                'attendance_module' => true,
                'exam_module' => true,
                'fees_module' => true,
                'library_module' => true,
                'transport_module' => true,
                'hostel_module' => true,
                'communication_module' => true,
                'reports_module' => true,
                'online_payment' => false,
                'sms_notifications' => false,
                'email_notifications' => true,
                'push_notifications' => false,
                'multi_language' => true,
                'dark_mode' => true,
            ], $flags);
        });
    }

    /**
     * Get menus for the authenticated user based on their role.
     *
     * @param \App\Models\User $user
     * @return array
     */
    protected function getMenusForUser($user): array
    {
        $role = $user->roles->first()?->name ?? 'guest';

        return match ($role) {
            'admin' => $this->getAdminMenus(),
            'teacher' => $this->getTeacherMenus(),
            'student' => $this->getStudentMenus(),
            'parent' => $this->getParentMenus(),
            'accountant' => $this->getAccountantMenus(),
            'librarian' => $this->getLibrarianMenus(),
            default => [],
        };
    }

    /**
     * Get admin menu items.
     *
     * @return array
     */
    protected function getAdminMenus(): array
    {
        return [
            ['title' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'bi-speedometer2'],
            [
                'title' => 'Academic',
                'icon' => 'bi-mortarboard',
                'children' => [
                    ['title' => 'Sessions', 'route' => 'admin.academic-sessions.index'],
                    ['title' => 'Classes', 'route' => 'admin.classes.index'],
                    ['title' => 'Sections', 'route' => 'admin.sections.index'],
                    ['title' => 'Subjects', 'route' => 'admin.subjects.index'],
                    ['title' => 'Timetables', 'route' => 'admin.timetables.index'],
                ],
            ],
            [
                'title' => 'Students',
                'icon' => 'bi-people',
                'children' => [
                    ['title' => 'All Students', 'route' => 'admin.students.index'],
                    ['title' => 'Categories', 'route' => 'admin.student-categories.index'],
                    ['title' => 'Promotions', 'route' => 'admin.promotions.index'],
                ],
            ],
            [
                'title' => 'Attendance',
                'icon' => 'bi-calendar-check',
                'children' => [
                    ['title' => 'Mark Attendance', 'route' => 'admin.attendance.mark'],
                    ['title' => 'View Attendance', 'route' => 'admin.attendance.index'],
                    ['title' => 'Attendance Types', 'route' => 'admin.attendance-types.index'],
                    ['title' => 'Reports', 'route' => 'admin.attendance.report'],
                ],
            ],
            [
                'title' => 'Examinations',
                'icon' => 'bi-file-earmark-text',
                'children' => [
                    ['title' => 'Exams', 'route' => 'admin.exams.index'],
                    ['title' => 'Exam Types', 'route' => 'admin.exam-types.index'],
                    ['title' => 'Grades', 'route' => 'admin.exam-grades.index'],
                ],
            ],
            [
                'title' => 'Fees',
                'icon' => 'bi-currency-rupee',
                'children' => [
                    ['title' => 'Collection', 'route' => 'admin.fees-collection.index'],
                    ['title' => 'Fee Types', 'route' => 'admin.fees-types.index'],
                    ['title' => 'Fee Groups', 'route' => 'admin.fees-groups.index'],
                    ['title' => 'Fee Masters', 'route' => 'admin.fees-masters.index'],
                    ['title' => 'Discounts', 'route' => 'admin.fees-discounts.index'],
                ],
            ],
            [
                'title' => 'Library',
                'icon' => 'bi-book',
                'children' => [
                    ['title' => 'Books', 'route' => 'admin.library-books.index'],
                    ['title' => 'Categories', 'route' => 'admin.library-categories.index'],
                    ['title' => 'Members', 'route' => 'admin.library-members.index'],
                    ['title' => 'Issue/Return', 'route' => 'admin.library-issues.index'],
                ],
            ],
            [
                'title' => 'Transport',
                'icon' => 'bi-bus-front',
                'children' => [
                    ['title' => 'Routes', 'route' => 'admin.transport-routes.index'],
                    ['title' => 'Vehicles', 'route' => 'admin.transport-vehicles.index'],
                    ['title' => 'Assignments', 'route' => 'admin.transport-students.index'],
                ],
            ],
            [
                'title' => 'Communication',
                'icon' => 'bi-chat-dots',
                'children' => [
                    ['title' => 'Notices', 'route' => 'admin.notices.index'],
                    ['title' => 'Messages', 'route' => 'admin.messages.index'],
                    ['title' => 'SMS', 'route' => 'admin.sms.index'],
                ],
            ],
            ['title' => 'Reports', 'route' => 'admin.reports.index', 'icon' => 'bi-graph-up'],
            ['title' => 'Settings', 'route' => 'admin.settings.index', 'icon' => 'bi-gear'],
        ];
    }

    /**
     * Get teacher menu items.
     *
     * @return array
     */
    protected function getTeacherMenus(): array
    {
        return [
            ['title' => 'Dashboard', 'route' => 'teacher.dashboard', 'icon' => 'bi-speedometer2'],
            ['title' => 'My Classes', 'route' => 'teacher.classes.index', 'icon' => 'bi-people'],
            ['title' => 'Timetable', 'route' => 'teacher.timetable.index', 'icon' => 'bi-calendar3'],
            ['title' => 'Attendance', 'route' => 'teacher.attendance.index', 'icon' => 'bi-calendar-check'],
            ['title' => 'Exams', 'route' => 'teacher.exams.index', 'icon' => 'bi-file-earmark-text'],
            ['title' => 'Students', 'route' => 'teacher.students.index', 'icon' => 'bi-person'],
            ['title' => 'Messages', 'route' => 'teacher.messages.index', 'icon' => 'bi-envelope'],
            ['title' => 'Notices', 'route' => 'teacher.notices.index', 'icon' => 'bi-megaphone'],
            ['title' => 'Profile', 'route' => 'teacher.profile.index', 'icon' => 'bi-person-circle'],
        ];
    }

    /**
     * Get student menu items.
     *
     * @return array
     */
    protected function getStudentMenus(): array
    {
        return [
            ['title' => 'Dashboard', 'route' => 'student.dashboard', 'icon' => 'bi-speedometer2'],
            ['title' => 'Attendance', 'route' => 'student.attendance.index', 'icon' => 'bi-calendar-check'],
            ['title' => 'Exams', 'route' => 'student.exams.index', 'icon' => 'bi-file-earmark-text'],
            ['title' => 'Fees', 'route' => 'student.fees.index', 'icon' => 'bi-currency-rupee'],
            ['title' => 'Timetable', 'route' => 'student.timetable.index', 'icon' => 'bi-calendar3'],
            ['title' => 'Library', 'route' => 'student.library.index', 'icon' => 'bi-book'],
            ['title' => 'Transport', 'route' => 'student.transport.index', 'icon' => 'bi-bus-front'],
            ['title' => 'Notices', 'route' => 'student.notices.index', 'icon' => 'bi-megaphone'],
            ['title' => 'Messages', 'route' => 'student.messages.index', 'icon' => 'bi-envelope'],
            ['title' => 'Documents', 'route' => 'student.documents.index', 'icon' => 'bi-folder'],
            ['title' => 'Profile', 'route' => 'student.profile.index', 'icon' => 'bi-person-circle'],
        ];
    }

    /**
     * Get parent menu items.
     *
     * @return array
     */
    protected function getParentMenus(): array
    {
        return [
            ['title' => 'Dashboard', 'route' => 'parent.dashboard', 'icon' => 'bi-speedometer2'],
            ['title' => 'My Children', 'route' => 'parent.children.index', 'icon' => 'bi-people'],
            ['title' => 'Fees', 'route' => 'parent.fees.index', 'icon' => 'bi-currency-rupee'],
            ['title' => 'Notices', 'route' => 'parent.notices.index', 'icon' => 'bi-megaphone'],
            ['title' => 'Messages', 'route' => 'parent.messages.index', 'icon' => 'bi-envelope'],
            ['title' => 'Profile', 'route' => 'parent.profile.index', 'icon' => 'bi-person-circle'],
        ];
    }

    /**
     * Get accountant menu items.
     *
     * @return array
     */
    protected function getAccountantMenus(): array
    {
        return [
            ['title' => 'Dashboard', 'route' => 'accountant.dashboard', 'icon' => 'bi-speedometer2'],
            ['title' => 'Fees Collection', 'route' => 'accountant.fees-collection.index', 'icon' => 'bi-currency-rupee'],
            ['title' => 'Fees Reports', 'route' => 'accountant.fees-reports.index', 'icon' => 'bi-file-earmark-bar-graph'],
            ['title' => 'Expenses', 'route' => 'accountant.expenses.index', 'icon' => 'bi-cash-stack'],
            ['title' => 'Income', 'route' => 'accountant.incomes.index', 'icon' => 'bi-wallet2'],
            ['title' => 'Financial Reports', 'route' => 'accountant.financial-reports.index', 'icon' => 'bi-graph-up'],
            ['title' => 'Profile', 'route' => 'accountant.profile.index', 'icon' => 'bi-person-circle'],
        ];
    }

    /**
     * Get librarian menu items.
     *
     * @return array
     */
    protected function getLibrarianMenus(): array
    {
        return [
            ['title' => 'Dashboard', 'route' => 'librarian.dashboard', 'icon' => 'bi-speedometer2'],
            ['title' => 'Books', 'route' => 'librarian.books.index', 'icon' => 'bi-book'],
            ['title' => 'Categories', 'route' => 'librarian.categories.index', 'icon' => 'bi-folder'],
            ['title' => 'Members', 'route' => 'librarian.members.index', 'icon' => 'bi-people'],
            ['title' => 'Issue/Return', 'route' => 'librarian.issues.index', 'icon' => 'bi-arrow-left-right'],
            ['title' => 'Reports', 'route' => 'librarian.reports.index', 'icon' => 'bi-file-earmark-bar-graph'],
            ['title' => 'Profile', 'route' => 'librarian.profile.index', 'icon' => 'bi-person-circle'],
        ];
    }

    /**
     * Get available languages.
     *
     * @return array
     */
    protected function getAvailableLanguages(): array
    {
        return [
            ['code' => 'en', 'name' => 'English', 'direction' => 'ltr'],
            ['code' => 'hi', 'name' => 'Hindi', 'direction' => 'ltr'],
            ['code' => 'ar', 'name' => 'Arabic', 'direction' => 'rtl'],
        ];
    }

    /**
     * Get text direction based on current locale.
     *
     * @return string
     */
    protected function getTextDirection(): string
    {
        $rtlLocales = ['ar', 'he', 'fa', 'ur'];
        return in_array(app()->getLocale(), $rtlLocales) ? 'rtl' : 'ltr';
    }

    /**
     * Generate breadcrumbs based on current route.
     *
     * @return array
     */
    protected function generateBreadcrumbs(): array
    {
        $route = request()->route();
        if (!$route) {
            return [];
        }

        $routeName = $route->getName() ?? '';
        $parts = explode('.', $routeName);

        if (count($parts) < 2) {
            return [];
        }

        $breadcrumbs = [];
        $role = $parts[0];

        // Add home/dashboard
        $breadcrumbs[] = [
            'title' => 'Dashboard',
            'url' => route("{$role}.dashboard"),
        ];

        // Build breadcrumb path
        $currentPath = $role;
        for ($i = 1; $i < count($parts); $i++) {
            $currentPath .= '.' . $parts[$i];
            $title = ucwords(str_replace(['-', '_'], ' ', $parts[$i]));

            // Check if this is the last item (current page)
            $isLast = ($i === count($parts) - 1);

            $breadcrumbs[] = [
                'title' => $title,
                'url' => $isLast ? null : (route_exists($currentPath) ? route($currentPath) : null),
            ];
        }

        return $breadcrumbs;
    }
}

/**
 * Helper function to check if a route exists.
 *
 * @param string $name
 * @return bool
 */
if (!function_exists('route_exists')) {
    function route_exists(string $name): bool
    {
        return \Illuminate\Support\Facades\Route::has($name);
    }
}
