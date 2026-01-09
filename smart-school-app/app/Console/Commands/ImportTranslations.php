<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TranslationService;
use Illuminate\Support\Facades\File;

/**
 * Import Translations Command
 * 
 * Prompt 448: Create Translation Import Command
 * 
 * Artisan command to import translations from files into the database.
 * Supports JSON and PHP language files.
 * 
 * Features:
 * - Import from JSON files
 * - Import from PHP language files
 * - Batch import from directory
 * - Overwrite option
 * - Progress display
 */
class ImportTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:import 
                            {locale : The locale to import translations for}
                            {--file= : Path to a specific file to import}
                            {--group= : Translation group name}
                            {--format=json : File format (json or php)}
                            {--overwrite : Overwrite existing translations}
                            {--all : Import all language files for the locale}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import translations from files into the database';

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
        $file = $this->option('file');
        $group = $this->option('group');
        $format = $this->option('format');
        $overwrite = $this->option('overwrite');
        $importAll = $this->option('all');

        $this->info("Importing translations for locale: {$locale}");

        if ($importAll) {
            return $this->importAllFiles($locale, $overwrite);
        }

        if ($file) {
            return $this->importFile($file, $locale, $group, $format, $overwrite);
        }

        if ($group) {
            return $this->importGroup($locale, $group, $format, $overwrite);
        }

        $this->error('Please specify --file, --group, or --all option');
        return Command::FAILURE;
    }

    /**
     * Import a specific file.
     *
     * @param string $file
     * @param string $locale
     * @param string|null $group
     * @param string $format
     * @param bool $overwrite
     * @return int
     */
    protected function importFile(string $file, string $locale, ?string $group, string $format, bool $overwrite): int
    {
        if (!File::exists($file)) {
            $this->error("File not found: {$file}");
            return Command::FAILURE;
        }

        $group = $group ?? pathinfo($file, PATHINFO_FILENAME);

        try {
            if ($format === 'json') {
                $result = $this->translationService->importFromJson($file, $locale, $group, $overwrite);
            } else {
                $content = include $file;
                if (!is_array($content)) {
                    $this->error('Invalid PHP language file format');
                    return Command::FAILURE;
                }
                $result = $this->translationService->import($content, $locale, $group, $overwrite);
            }

            $this->info("Imported: {$result['imported']}, Skipped: {$result['skipped']}");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Import a translation group from Laravel lang directory.
     *
     * @param string $locale
     * @param string $group
     * @param string $format
     * @param bool $overwrite
     * @return int
     */
    protected function importGroup(string $locale, string $group, string $format, bool $overwrite): int
    {
        try {
            if ($format === 'json') {
                $file = lang_path("{$locale}.json");
                if (!File::exists($file)) {
                    $this->error("JSON language file not found: {$file}");
                    return Command::FAILURE;
                }
                $result = $this->translationService->importFromJson($file, $locale, $group, $overwrite);
            } else {
                $result = $this->translationService->importFromLangFile($locale, $group, $overwrite);
            }

            $this->info("Imported: {$result['imported']}, Skipped: {$result['skipped']}");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Import all language files for a locale.
     *
     * @param string $locale
     * @param bool $overwrite
     * @return int
     */
    protected function importAllFiles(string $locale, bool $overwrite): int
    {
        $langPath = lang_path($locale);
        $totalImported = 0;
        $totalSkipped = 0;

        // Import PHP files
        if (File::isDirectory($langPath)) {
            $files = File::files($langPath);

            $this->info("Found " . count($files) . " PHP language files");

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $group = $file->getFilenameWithoutExtension();
                $this->line("Importing group: {$group}");

                try {
                    $result = $this->translationService->importFromLangFile($locale, $group, $overwrite);
                    $totalImported += $result['imported'];
                    $totalSkipped += $result['skipped'];
                    $this->info("  - Imported: {$result['imported']}, Skipped: {$result['skipped']}");
                } catch (\Exception $e) {
                    $this->warn("  - Failed: {$e->getMessage()}");
                }
            }
        }

        // Import JSON file if exists
        $jsonFile = lang_path("{$locale}.json");
        if (File::exists($jsonFile)) {
            $this->line("Importing JSON file: {$locale}.json");

            try {
                $result = $this->translationService->importFromJson($jsonFile, $locale, 'json', $overwrite);
                $totalImported += $result['imported'];
                $totalSkipped += $result['skipped'];
                $this->info("  - Imported: {$result['imported']}, Skipped: {$result['skipped']}");
            } catch (\Exception $e) {
                $this->warn("  - Failed: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Total imported: {$totalImported}");
        $this->info("Total skipped: {$totalSkipped}");

        return Command::SUCCESS;
    }
}
