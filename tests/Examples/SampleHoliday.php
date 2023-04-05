<?php

declare(strict_types=1);

/**
 * Contains the SampleHoliday class.
 *
 * @copyright   Copyright (c) 2023 Vanilo UG
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-03-13
 *
 */

namespace Vanilo\Workflow\Tests\Examples;

class SampleHoliday
{
    public SampleHolidayStatus $status;

    public bool $saved = false;

    public mixed $misc = null;

    public function __construct()
    {
        $this->status = SampleHolidayStatus::REQUESTED();
    }
}
