<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TranslationService;
use Illuminate\Support\Facades\File;

/**
 * Export Translations Command
 * 
 * Prompt 449: Create Translation Export Command
 * 
 * Artisan command to export translations from the database to files.
 * Supports JSON and PHP language file formats.
 * 
 * Features:
 * - Export to JSON files
 * - Export to PHP language files
 * - Export all groups
 * - Export specific group
 * - Directory creation
 */
class ExportTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:export 
                            {locale : The locale to export translations for}
                            {--group= : Specific translation group to export}
                            {--format=json : Output format (json or php)}
                            {--output= : Output directory or file path}
                            {--all : Export all groups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export translations from the database to files';

    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $locale = $this->argument('locale');
        $group = $this->option('group');
        $format = $this->option('format');
        $output = $this->option('output');
        $exportAll = $this->option('all');

        $this->info("Exporting translations for locale: {$locale}");

        if ($exportAll) {
            return $this->exportAllGroups($locale, $format, $output);
        }

        if ($group) {
            return $this->exportGroup($locale, $group, $format, $output);
        }

        // Export all translations to a single file
        return $this->exportAll($locale, $format, $output);
    }

    /**
     * Export a specific group.
     *
     * @param string $locale
     * @param string $group
     * @param string $format
     * @param string|null $output
     * @return int
     */
    protected function exportGroup(string $locale, string $group, string $format, ?string $output): int
    {
        try {
            if ($format === 'json') {
                $filePath = $output ?? storage_path("exports/translations/{$locale}/{$group}.json");
                $this->ensureDirectoryExists(dirname($filePath));
                $this->translationService->exportToJson($filePath, $locale, $group);
            } else {
                $filePath = $output ?? lang_path("{$locale}/{$group}.php");
                $this->ensureDirectoryExists(dirname($filePath));
                $this->translationService->exportToLangFile($locale, $group);
            }

            $this->info("Exported to: {$filePath}");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Export failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Export all translations to a single file.
     *
     * @param string $locale
     * @param string $format
     * @param string|null $output
     * @return int
     */
    protected function exportAll(string $locale, string $format, ?string $output): int
    {
        try {
            $translations = $this->translationService->export($locale);

            if ($format === 'json') {
                $filePath = $output ?? storage_path("exports/translations/{$locale}/all.json");
                $this->ensureDirectoryExists(dirname($filePath));
                $content = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                File::put($filePath, $content);
            } else {
                $filePath = $output ?? storage_path("exports/translations/{$locale}/all.php");
                $this->ensureDirectoryExists(dirname($filePath));
                $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
                File::put($filePath, $content);
            }

            $this->info("Exported " . count($translations) . " translations to: {$filePath}");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Export failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Export all groups to separate files.
     *
     * @param string $locale
     * @param string $format
     * @param string|null $output
     * @return int
     */
    protected function exportAllGroups(string $locale, string $format, ?string $output): int
    {
        $groups = $this->translationService->getGroups();

        if (empty($groups)) {
            $this->warn('No translation groups found');
            return Command::SUCCESS;
        }

        $this->info("Found " . count($groups) . " groups");

        $baseDir = $output ?? ($format === 'json' 
            ? storage_path("exports/translations/{$locale}")
            : lang_path($locale));

        $this->ensureDirectoryExists($baseDir);

        $exported = 0;

        foreach ($groups as $group) {
            $this->line("Exporting group: {$group}");

            try {
                if ($format === 'json') {
                    $filePath = "{$baseDir}/{$group}.json";
                    $this->translationService->exportToJson($filePath, $locale, $group);
                } else {
                    $this->translationService->exportToLangFile($locale, $group);
                    $filePath = lang_path("{$locale}/{$group}.php");
                }

                $this->info("  - Exported to: {$filePath}");
                $exported++;

            } catch (\Exception $e) {
                $this->warn("  - Failed: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Exported {$exported} of " . count($groups) . " groups");

        return Command::SUCCESS;
    }

    /**
     * Ensure directory exists.
     *
     * @param string $directory
     * @return void
     */
    protected function ensureDirectoryExists(string $directory): void
    {
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }
}
