<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RulesTest extends TestCase
{
    public function testGetNumericIdRules()
    {
        $rules = [
            'numeric_id' => \App\Http\Requests\CustomFormRequest::getNumericIdRules(),
        ];

        $validator = Validator::make(['numeric_id' => 123], $rules);
        $this->assertFalse($validator->fails());

        $validator = Validator::make(['numeric_id' => -1], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['numeric_id' => 0], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['numeric_id' => 100000000000], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['numeric_id' => 'abc'], $rules);
        $this->assertTrue($validator->fails());
    }

    public function testGetAmountRules()
    {
        $minAmount = 10.0;
        $maxAmount = 100.0;
        $rules = [
            'amount' => \App\Http\Requests\CustomFormRequest::getAmountRules($minAmount, $maxAmount),
        ];

        $validator = Validator::make(['amount' => 55.00], $rules);
        $this->assertFalse($validator->fails());

        $validator = Validator::make(['amount' => $minAmount], $rules);
        $this->assertFalse($validator->fails());

        $validator = Validator::make(['amount' => $maxAmount], $rules);
        $this->assertFalse($validator->fails());

        $validator = Validator::make(['amount' => 9.99], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['amount' => 100.01], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['amount' => 50.123], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['amount' => 'no-numérico'], $rules);
        $this->assertTrue($validator->fails());
    }
}
