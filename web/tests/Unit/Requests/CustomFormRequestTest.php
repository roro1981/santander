<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\CustomFormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomFormRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testNumericRules()
    {
        $request = new CustomFormRequest(['test']);

        $numericRules = $request->getNumericRules();

        $invalidData = ['numeric_field' => 'not_numeric'];

        $validator = $this->app['validator']->make($invalidData, $numericRules);

        $this->assertTrue($validator->fails(), 'La validación debería fallar para datos no numéricos');
    }

    public function testAmountRules()
    {
        $request = new CustomFormRequest([105]);

        $amountRules = $request->getAmountRules(1, 100);

        $invalidData = ['amount_field' => 200];

        $validator = $this->app['validator']->make($invalidData, $amountRules);

        $this->assertTrue($validator->fails(), 'La validación debería fallar para datos fuera del rango permitido');
    }
}
