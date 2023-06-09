<?php

declare(strict_types=1);

/**
 * Contains the Draft class.
 *
 * @copyright   Copyright (c) 2023 Vanilo UG
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-03-13
 *
 */

namespace Vanilo\Workflow;

use BackedEnum;
use Closure;
use InvalidArgumentException;
use Konekt\Enum\Enum;
use Vanilo\Workflow\Contracts\Workflow;
use Vanilo\Workflow\Exceptions\TransitionNotAllowedException;

class Draft implements Workflow
{
    protected static string $enumClass;

    protected static string $property;

    protected static array $graph;

    protected object $subject;

    protected ?Closure $saveSubjectHook = null;

    public static function forgeOnTheFly(object $subject, string $property, array $graph): Draft
    {
        $prop = $subject->{$property};
        if (!is_object($prop) || (!($prop instanceof BackedEnum) && !($prop instanceof Enum))) {
            throw new InvalidArgumentException("The `$property` property of the " . get_class($subject) . ' class is not an Enum');
        }

        $instance = self::fabricateForEnumClass(get_class($prop), $property, $graph);
        $instance->subject = $subject;

        return $instance;
    }

    public static function fabricateForEnumClass(string $enumClass, string $property, array $graph): Draft
    {
        $instance = new static();
        $instance::$enumClass = $enumClass;
        $instance::$property = $property;
        $instance::$graph = $graph;

        return $instance;
    }

    public static function transitions(): array
    {
        return array_keys(static::$graph['transitions']);
    }

    public static function hasTransition(string $transition): bool
    {
        return isset(static::$graph['transitions'][$transition]);
    }

    public static function titleOf(string $transition): string
    {
        $title = static::$graph['transitions'][$transition]['title'] ?? $transition;
        if (is_callable($title)) {
            $title = call_user_func($title, $transition);
        }

        return (string) $title;
    }

    public static function for(object $subject): static
    {
        return (new static())->usingSubject($subject);
    }

    public function getState(): BackedEnum|Enum
    {
        return $this->subject->{static::$property};
    }

    public function allowedTransitions(): array
    {
        $result = [];
        foreach (static::transitions() as $transition) {
            if ($this->can($transition)) {
                $result[] = $transition;
            }
        }

        return $result;
    }

    public function execute(string $transition, array $parameters = []): void
    {
        $this->throwExceptionIfCannot($transition);

        if (method_exists($this, $transition)) {
            $this->{$transition}($parameters);
        } else {
            $this->subject->{static::$property} = $this->forceEnum(static::$graph['transitions'][$transition]['to']);
            $this->saveSubject($parameters);
        }
    }

    public function can(string $transition): bool
    {
        if (!static::hasTransition($transition)) {
            return false;
        }

        return
            in_array($this->currentStateAsScalar(), static::$graph['transitions'][$transition]['from'])
            ||
            in_array($this->getState(), static::$graph['transitions'][$transition]['from']);
    }

    public function usingSubject(object $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function callToSaveSubjectAfterTransition(Closure $callback): static
    {
        $this->saveSubjectHook = $callback;

        return $this;
    }

    public function getCoveredEnumClass(): string
    {
        return static::$enumClass;
    }

    protected function saveSubject(array $parameters): void
    {
        if (null !== $this->saveSubjectHook) {
            call_user_func($this->saveSubjectHook, $this->subject, $parameters);
        }
    }

    protected function currentStateAsScalar(): string|int
    {
        return $this->isKonektEnum() ? $this->getState()->value() : $this->getState()->value;
    }

    protected function forceEnum(BackedEnum|Enum|string|int $value): BackedEnum|Enum
    {
        if ($value instanceof BackedEnum || $value instanceof Enum) {
            return $value;
        }

        $class = static::$enumClass;
        return match ($this->isKonektEnum()) {
            true => $class::create($value),
            default => $class::from($value),
        };
    }

    protected function isKonektEnum(): bool
    {
        return $this->getState() instanceof Enum;
    }

    protected function throwExceptionIfCannot(string $transition): void
    {
        if (!$this->can($transition)) {
            throw new TransitionNotAllowedException(
                sprintf(
                    'The `%s` transition is not allowed on the subject with %s %s',
                    $transition,
                    $this->currentStateAsScalar(),
                    static::$property,
                )
            );
        }
    }
}
