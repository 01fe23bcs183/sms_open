<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;
use Illuminate\Support\Facades\DB;

/**
 * Language Seeder
 * 
 * Prompt 450: Create Language Seeder
 * 
 * Seeds the database with commonly used languages for the
 * Smart School Management System.
 * 
 * Features:
 * - Common languages pre-configured
 * - RTL language support
 * - Indian regional languages
 * - International languages
 */
class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            // Primary Languages
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'hi',
                'name' => 'Hindi',
                'native_name' => 'हिन्दी',
                'is_rtl' => false,
                'is_active' => true,
            ],

            // Indian Regional Languages
            [
                'code' => 'kn',
                'name' => 'Kannada',
                'native_name' => 'ಕನ್ನಡ',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'ta',
                'name' => 'Tamil',
                'native_name' => 'தமிழ்',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'te',
                'name' => 'Telugu',
                'native_name' => 'తెలుగు',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'ml',
                'name' => 'Malayalam',
                'native_name' => 'മലയാളം',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'mr',
                'name' => 'Marathi',
                'native_name' => 'मराठी',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'gu',
                'name' => 'Gujarati',
                'native_name' => 'ગુજરાતી',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'bn',
                'name' => 'Bengali',
                'native_name' => 'বাংলা',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'pa',
                'name' => 'Punjabi',
                'native_name' => 'ਪੰਜਾਬੀ',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'or',
                'name' => 'Odia',
                'native_name' => 'ଓଡ଼ିଆ',
                'is_rtl' => false,
                'is_active' => false,
            ],
            [
                'code' => 'as',
                'name' => 'Assamese',
                'native_name' => 'অসমীয়া',
                'is_rtl' => false,
                'is_active' => false,
            ],

            // RTL Languages
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'is_rtl' => true,
                'is_active' => true,
            ],
            [
                'code' => 'ur',
                'name' => 'Urdu',
                'native_name' => 'اردو',
                'is_rtl' => true,
                'is_active' => true,
            ],
            [
                'code' => 'he',
                'name' => 'Hebrew',
                'native_name' => 'עברית',
                'is_rtl' => true,
                'is_active' => false,
            ],
            [
                'code' => 'fa',
                'name' => 'Persian',
                'native_name' => 'فارسی',
                'is_rtl' => true,
                'is_active' => false,
            ],

            // International Languages
            [
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'de',
                'name' => 'German',
                'native_name' => 'Deutsch',
                'is_rtl' => false,
                'is_active' => false,
            ],
            [
                'code' => 'es',
                'name' => 'Spanish',
                'native_name' => 'Español',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'pt',
                'name' => 'Portuguese',
                'native_name' => 'Português',
                'is_rtl' => false,
                'is_active' => false,
            ],
            [
                'code' => 'ru',
                'name' => 'Russian',
                'native_name' => 'Русский',
                'is_rtl' => false,
                'is_active' => false,
            ],
            [
                'code' => 'zh',
                'name' => 'Chinese (Simplified)',
                'native_name' => '简体中文',
                'is_rtl' => false,
                'is_active' => false,
            ],
            [
                'code' => 'ja',
                'name' => 'Japanese',
                'native_name' => '日本語',
                'is_rtl' => false,
                'is_active' => false,
            ],
            [
                'code' => 'ko',
                'name' => 'Korean',
                'native_name' => '한국어',
                'is_rtl' => false,
                'is_active' => false,
            ],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }

        $this->command->info('Languages seeded successfully!');
        $this->command->info('Active languages: ' . Language::where('is_active', true)->count());
        $this->command->info('Total languages: ' . Language::count());
    }
}
