<?php

declare(strict_types=1);

namespace Tests;

use V8Js;

class TaskNumber4Test extends BaseTestCase
{
    private V8Js $v8;

    protected function setUp(): void
    {
        parent::setUp();
        $this->v8 = new V8Js();
    }

    /**
     * @dataProvider dataProvider
     * @param string $data
     */
    public function testJs(string $data, string $expectedString): void
    {
        $js = <<<JS
function printOrderTotal(responseString) {
   var responseJSON = JSON.parse(responseString);
   var total = 0;
   responseJSON.forEach(function(item){
      if (item.price !== undefined) {
         total += item.price;
      }
   });
   return 'Стоимость заказа: ' + (total > 0 ? total + ' руб.' : 'Бесплатно');
};
printOrderTotal($data);
JS;
        $result = $this->v8->executeString($js);

        static::assertEquals($expectedString, $result);
    }

    public function dataProvider(): array
    {
        return [
            ["'[{ \"price\": 100 }, { \"price\": 220 }]'", 'Стоимость заказа: 320 руб.'],
            ["'[{ \"price\": 0 }, { \"price\": 0 }]'", 'Стоимость заказа: Бесплатно'],
            ["'[{ \"price\": 200 }, { \"price\": -500 }]'", 'Стоимость заказа: Бесплатно'],
            ["'[{ \"pricez\": 200 }, { \"pricez\": 300 }]'", 'Стоимость заказа: Бесплатно'],
            ["'[{ \"price\": 200 }, { \"pricez\": 300 }]'", 'Стоимость заказа: 200 руб.'],
        ];
    }
}
