<?php

declare(strict_types=1);

/**
 * Contains the SampleHolidayStatus class.
 *
 * @copyright   Copyright (c) 2023 Vanilo UG
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-03-13
 *
 */

namespace Vanilo\Workflow\Tests\Examples;

use Konekt\Enum\Enum;

class SampleHolidayStatus extends Enum
{
    public const REQUESTED = 'requested';
    public const APPROVED = 'approved';
    public const SPENT = 'spent';
}
