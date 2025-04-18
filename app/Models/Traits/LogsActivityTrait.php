<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Trait LogsActivityTrait for logging model changes including create, update, delete.
 *
 * @class LogsActivityTrait
 */
trait LogsActivityTrait
{
    use LogsActivity;

    /**
     * The events that should be logged for the model - created, updated, deleted as default.
     * (That comes from the spatie laravel-activitylog package)
     *
     * @var string[]
     */
    protected static $recordEvents = ['created', 'deleted', 'updated'];

    /**
     * This method is needed for customizing the log options for spatie laravel-activitylog package.
     * Check the documentation for more details: https://spatie.be/docs/laravel-activitylog/v4/advanced-usage/logging-model-events
     *
     * @return LogOptions
     */
    protected function getActivitylogOptions(): LogOptions
    {
        return $this->customLogOptions($this->defaultLogOptions());
    }

    /**
     * Customizes the log options for the model by overriding this method in model.
     *
     * @param LogOptions $options
     *
     * @return LogOptions
     */
    protected function customLogOptions(LogOptions $options): LogOptions
    {
        return $options;
    }

    /**
     * Returns the default log options for the model where all attributes are logged, empty logs are not submitted, and log name is set.
     *
     * @return LogOptions
     */
    private function defaultLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->setDescriptionForEvent(fn(string $eventName) => class_basename($this) . " is {$eventName}!")
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getLogName());
    }

    /**
     * Returns the log name for the model by combining the base class name with the model activity suffix.
     *
     * @return string
     */
    private function getLogName(): string
    {
        return class_basename($this) . '_model_activity';
    }
}
