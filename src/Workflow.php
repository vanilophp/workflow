<?php

declare(strict_types=1);

/**
 * Contains the Workflow class.
 *
 * @copyright   Copyright (c) 2023 Vanilo UG
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-03-13
 *
 */

namespace Vanilo\Workflow;

use BackedEnum;
use InvalidArgumentException;
use Konekt\Enum\Enum;
use Vanilo\Workflow\Exceptions\TransitionNotAllowedException;

class Workflow
{
    protected static string $enumClass;
    protected static string $property;

    protected static array $graph;

    protected object $subject;

    public static function createOnTheFly(object $subject, string $property, array $graph): Workflow
    {
        $prop = $subject->{$property};
        if (!is_object($prop) || ( !($prop instanceof BackedEnum) && !($prop instanceof Enum))) {
            throw new InvalidArgumentException("The `$property` property of the " . get_class($subject) . ' class is not an Enum');
        }

        $instance = self::createForEnumClass(get_class($prop), $property, $graph);
        $instance->subject = $subject;

        return $instance;
    }

    public static function createForEnumClass(string $enumClass, string $property, array $graph): Workflow
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

    public function execute(string $transition): void
    {
        $this->throwExceptionIfCannot($transition);

        $this->subject->{static::$property} = $this->forceEnum(static::$graph['transitions'][$transition]['to']);
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

    public function getCoveredEnumClass(): string
    {
        return static::$enumClass;
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
        return match($this->isKonektEnum()) {
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
