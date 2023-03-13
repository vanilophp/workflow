<?php

declare(strict_types=1);

/**
 * Contains the SampleNativeHoliday class.
 *
 * @copyright   Copyright (c) 2023 Vanilo UG
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-03-13
 *
 */

namespace Vanilo\Workflow\Tests\Examples;

class SampleNativeHoliday
{
    public SampleNativeHolidayStatus $status;

    public function __construct()
    {
        $this->status = SampleNativeHolidayStatus::REQUESTED;
    }
}
