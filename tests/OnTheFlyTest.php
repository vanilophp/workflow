<?php

declare(strict_types=1);

/**
 * Contains the OnTheFlyTest class.
 *
 * @copyright   Copyright (c) 2023 Vanilo UG
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-03-13
 *
 */

namespace Vanilo\Workflow\Tests;

use PHPUnit\Framework\TestCase;
use Vanilo\Workflow\Tests\Examples\SampleHoliday;
use Vanilo\Workflow\Tests\Examples\SampleHolidayStatus;
use Vanilo\Workflow\Tests\Examples\SampleNativeHoliday;
use Vanilo\Workflow\Tests\Examples\SampleNativeHolidayStatus;
use Vanilo\Workflow\Draft;

class OnTheFlyTest extends TestCase
{
    /** @test */
    public function it_can_be_created_from_an_object_using_its_property()
    {
        $holiday = new SampleHoliday();
        $workflow = Draft::forgeOnTheFly($holiday, 'status', []);

        $this->assertInstanceOf(Draft::class, $workflow);
        $this->assertEquals(SampleHolidayStatus::class, $workflow->getCoveredEnumClass());
    }

    /** @test */
    public function it_can_be_created_from_an_enum_class_and_a_property()
    {
        $workflow = Draft::fabricateForEnumClass(SampleHolidayStatus::class, 'status', [])
            ->usingSubject(new SampleHoliday());

        $this->assertInstanceOf(Draft::class, $workflow);
        $this->assertEquals(SampleHolidayStatus::class, $workflow->getCoveredEnumClass());
    }

    /** @test */
    public function it_can_tell_the_allowed_transitions()
    {
        $workflow = Draft::forgeOnTheFly(new SampleHoliday(), 'status',
            [
                'transitions' => [
                    'approve' => [
                        'from' => [SampleHolidayStatus::REQUESTED],
                        'to' => SampleHolidayStatus::APPROVED,
                    ]
                ],
            ],
        );

        $this->assertTrue($workflow->can('approve'));
        $this->assertEquals(['approve'], $workflow->allowedTransitions());
    }

    /** @test */
    public function transitions_can_be_executed()
    {
        $holiday = new SampleHoliday();
        $workflow = Draft::forgeOnTheFly($holiday, 'status',
            [
                'transitions' => [
                    'approve' => [
                        'from' => [SampleHolidayStatus::REQUESTED],
                        'to' => SampleHolidayStatus::APPROVED,
                    ],
                    'spend' => [
                        'from' => [SampleHolidayStatus::APPROVED],
                        'to' => SampleHolidayStatus::SPENT,
                    ]
                ],
            ],
        );

        $this->assertEquals(SampleHolidayStatus::REQUESTED(), $holiday->status);
        $workflow->execute('approve');
        $this->assertEquals(SampleHolidayStatus::APPROVED(), $holiday->status);
        $workflow->execute('spend');
        $this->assertEquals(SampleHolidayStatus::SPENT(), $holiday->status);
    }

    /** @test */
    public function it_works_with_native_php_enums()
    {
        $holiday = new SampleNativeHoliday();
        $workflow = Draft::forgeOnTheFly($holiday, 'status',
            [
                'transitions' => [
                    'approve' => [
                        'from' => [SampleNativeHolidayStatus::REQUESTED],
                        'to' => SampleNativeHolidayStatus::APPROVED,
                    ],
                    'spend' => [
                        'from' => [SampleNativeHolidayStatus::APPROVED],
                        'to' => SampleNativeHolidayStatus::SPENT,
                    ]
                ],
            ],
        );

        $this->assertEquals(SampleNativeHolidayStatus::REQUESTED, $holiday->status);
        $workflow->execute('approve');
        $this->assertEquals(SampleNativeHolidayStatus::APPROVED, $holiday->status);
        $workflow->execute('spend');
        $this->assertEquals(SampleNativeHolidayStatus::SPENT, $holiday->status);
    }
}
