<?php

namespace App\Console\Commands;

use App\Services\ModuleRegistrar;
use Illuminate\Console\Command;

/**
 * Backfills course_modules from the content that already exists. New content
 * registers itself through ModuleObserver, so this is for the first run and
 * for repairing the registry after a bulk import.
 */
class SyncCourseModules extends Command
{
    protected $signature = 'progress:sync-modules';

    protected $description = 'Register every video, quiz, note, diagram, guide and summary as a trackable module';

    public function handle(ModuleRegistrar $registrar): int
    {
        $this->info('Syncing course modules…');

        $result = $registrar->syncAll();

        $this->line("  registered:  {$result['registered']}");
        $this->line("  deactivated: {$result['deactivated']}");

        return self::SUCCESS;
    }
}
