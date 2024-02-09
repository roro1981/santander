<?php
use Tests\TestCase;
use App\Traits\XmlConversionTrait;

class XmlConversionTraitTest extends TestCase
{
    use XmlConversionTrait;

    public function testConvertXmlToArray()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <root>
                <item>
                    <id>1</id>
                    <name>Item 1</name>
                </item>
                <item>
                    <id>2</id>
                    <name>Item 2</name>
                </item>
            </root>';

        $result = $this->convertXmlToArray($xml);

        $expectedResult = [
            'item' => [
                ['id' => '1', 'name' => 'Item 1'],
                ['id' => '2', 'name' => 'Item 2']
            ]
        ];

        $this->assertEquals($expectedResult, $result);
    }
}
