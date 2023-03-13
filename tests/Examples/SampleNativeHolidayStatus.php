<?php

namespace Vanilo\Workflow\Tests\Examples;

enum SampleNativeHolidayStatus: string
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case SPENT = 'spent';
}
