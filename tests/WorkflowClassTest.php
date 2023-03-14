<?php

declare(strict_types=1);

/**
 * Contains the WorkflowClassTest class.
 *
 * @copyright   Copyright (c) 2023 Vanilo UG
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-03-14
 *
 */

namespace Vanilo\Workflow\Tests;

use PHPUnit\Framework\TestCase;
use Vanilo\Workflow\Tests\Examples\SampleHolidayWorkflow;
use Vanilo\Workflow\Tests\Examples\SampleNativeHoliday;

class WorkflowClassTest extends TestCase
{
    /** @test */
    public function a_custom_workflow_class_can_be_instantiated()
    {
        $holiday = new SampleNativeHoliday();
        $workflow = SampleHolidayWorkflow::for($holiday);

        $this->assertInstanceOf(SampleHolidayWorkflow::class, $workflow);
        $this->assertEquals(['approve', 'spend', 'cancel'], $workflow::transitions());
        $this->assertEquals(['approve', 'cancel'], $workflow->allowedTransitions());
    }

    /** @test */
    public function when_a_workflow_class_has_a_method_with_the_same_name_as_a_transition_then_the_method_will_be_invoked_when_executing_the_transition()
    {
        $holiday = new SampleNativeHoliday();
        $workflow = SampleHolidayWorkflow::for($holiday);

        $this->assertInstanceOf(SampleHolidayWorkflow::class, $workflow);
        $this->assertEquals(['approve', 'spend', 'cancel'], $workflow::transitions());
        $this->assertEquals(['approve', 'cancel'], $workflow->allowedTransitions());

        $this->expectExceptionMessage('Hey hello cancel method was invoked here!');
        $workflow->execute('cancel');
    }
}
