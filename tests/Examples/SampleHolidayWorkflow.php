<?php

declare(strict_types=1);

/**
 * Contains the SampleHolidayWorkflow class.
 *
 * @copyright   Copyright (c) 2023 Vanilo UG
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-03-14
 *
 */

namespace Vanilo\Workflow\Tests\Examples;

use Vanilo\Workflow\Draft;

class SampleHolidayWorkflow extends Draft
{
    protected static string $enumClass = SampleHoliday::class;

    protected static string $property = 'status';

    protected static array $graph = [
        'transitions' => [
            'approve' => [
                'from' => [SampleNativeHolidayStatus::REQUESTED],
                'to' => SampleNativeHolidayStatus::APPROVED,
            ],
            'spend' => [
                'from' => [SampleNativeHolidayStatus::APPROVED],
                'to' => SampleNativeHolidayStatus::SPENT,
            ],
            'cancel' => [
                'from' => [SampleNativeHolidayStatus::REQUESTED, SampleHolidayStatus::APPROVED],
                'to' => SampleNativeHolidayStatus::CANCELED,
            ]
        ],
    ];

    public function cancel(): void
    {
        throw new \Exception('Hey hello cancel method was invoked here!');
    }
}
