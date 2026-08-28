<?php

namespace App\Observers;

use App\Services\ModuleRegistrar;
use Illuminate\Database\Eloquent\Model;

/**
 * Registers content as a trackable unit the moment it is created, so nothing
 * has to remember to do it by hand. Attached in AppServiceProvider to every
 * model named in CourseModule::TYPES.
 */
class ModuleObserver
{
    public function __construct(private readonly ModuleRegistrar $registrar)
    {
    }

    public function created(Model $record): void
    {
        $this->registrar->register($record);
    }

    /**
     * A record can be moved to another chapter, which moves the unit with it.
     */
    public function updated(Model $record): void
    {
        $this->registrar->register($record);
    }

    /**
     * Deleted content stops counting towards anyone's percentage, but the rows
     * saying who finished it are kept.
     */
    public function deleted(Model $record): void
    {
        $this->registrar->deactivate($record);
    }

    public function restored(Model $record): void
    {
        $this->registrar->reactivate($record);
    }
}
