<?php

declare(strict_types=1);

/**
 * Contains the Workflow interface.
 *
 * @copyright   Copyright (c) 2023 Vanilo UG
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-03-14
 *
 */

namespace Vanilo\Workflow\Contracts;

use BackedEnum;
use Konekt\Enum\Enum;

interface Workflow
{
    public static function for(object $subject): static;

    public static function transitions(): array;

    public static function hasTransition(string $transition): bool;

    public static function titleOf(string $transition): string;

    public function getState(): BackedEnum|Enum;

    public function allowedTransitions(): array;

    public function execute(string $transition, array $parameters = []): void;

    public function can(string $transition): bool;

    public function usingSubject(object $subject): static;

    public function getCoveredEnumClass(): string;
}
