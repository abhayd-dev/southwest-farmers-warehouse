<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateToR2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:migrate-to-r2 {--source=local_public : The source disk} {--target=r2 : The target disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate files from local storage to Cloudflare R2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sourceDisk = $this->option('source');
        $targetDisk = $this->option('target');

        $this->info("Starting migration from [{$sourceDisk}] to [{$targetDisk}]...");

        try {
            $files = Storage::disk($sourceDisk)->allFiles();
        } catch (\Exception $e) {
            $this->error("Could not read from source disk: " . $e->getMessage());
            return 1;
        }

        $total = count($files);

        if ($total === 0) {
            $this->info("No files found on source disk.");
            return 0;
        }

        $this->info("Found {$total} files. Beginning upload...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $successCount = 0;
        $failCount = 0;
        $skippedCount = 0;

        foreach ($files as $file) {
            // Skip .gitignore and other hidden files if desired
            if (basename($file) === '.gitignore' || str_starts_with(basename($file), '.')) {
                $skippedCount++;
                $bar->advance();
                continue;
            }

            try {
                // Check if file already exists on R2 to avoid redundant uploads
                if (Storage::disk($targetDisk)->exists($file)) {
                    $skippedCount++;
                } else {
                    $content = Storage::disk($sourceDisk)->get($file);
                    $mimeType = Storage::disk($sourceDisk)->mimeType($file);
                    
                    Storage::disk($targetDisk)->put($file, $content, [
                        'visibility' => 'public',
                        'ContentType' => $mimeType
                    ]);
                    $successCount++;
                }
            } catch (\Exception $e) {
                $this->error("\nFailed to migrate {$file}: " . $e->getMessage());
                $failCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        
        $this->newLine(2);
        $this->table(
            ['Metric', 'Count'],
            [
                ['Successfully Migrated', $successCount],
                ['Skipped (Existing/Hidden)', $skippedCount],
                ['Failed', $failCount],
                ['Total Processed', $total]
            ]
        );

        $this->info("Migration process completed.");
        
        return $failCount === 0 ? 0 : 1;
    }
}
