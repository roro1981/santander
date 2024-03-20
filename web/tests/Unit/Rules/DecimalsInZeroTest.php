<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Rules\DecimalsInZero;

class DecimalsInZeroTest extends TestCase
{
    /**
     * Test the rule passes with valid input.
     *
     * @return void
     */
    public function testRulePassesWithValidInput()
    {
        $rule = new DecimalsInZero();

        $this->assertTrue($rule->passes('amount', '100.00'));
        $this->assertTrue($rule->passes('amount', '123456.00'));
        $this->assertTrue($rule->passes('amount', '0.00'));
        $this->assertTrue($rule->passes('amount', '0')); // Considering integer as valid
    }

    /**
     * Test the rule fails with invalid input.
     *
     * @return void
     */
    public function testRuleFailsWithInvalidInput()
    {
        $rule = new DecimalsInZero();

        $this->assertFalse($rule->passes('amount', '100.01'));
        $this->assertFalse($rule->passes('amount', '123456.99'));
        $this->assertFalse($rule->passes('amount', '0.10'));
        $this->assertFalse($rule->passes('amount', 'abcd'));
        $this->assertFalse($rule->passes('amount', '100.001')); // More than two decimal places
        $this->assertFalse($rule->passes('amount', '-100.00')); // Negative value, adjust if negative values should be valid
    }

    /**
     * Test the validation message.
     *
     * @return void
     */
    public function testValidationMessage()
    {
        $rule = new DecimalsInZero();

        $this->assertEquals(
            'The :attribute must be a number with .00 as the decimal part.',
            $rule->message()
        );
    }
}
