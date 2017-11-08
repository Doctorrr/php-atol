# php-atol
Russian tax service php class
 
https://online.atol.ru -- касса как сервис для интернет-торговли на базе кассового комплекса АТОЛ 42ФС.

Класс для работы с виртуальными кассами компании АТОЛ 42ФС 
(сервис отправляет данные в налоговую по 54-ФЗ + отправляет электронный чек клиенту).
  
В текущем проекте товары продаются по 1 штуке, так что для стандартного магазина надо дописывать метод createBill().
 
## Создать чек:
```php
    $Atol = new Atol();
    
    $test_bill = [
        'external_id'=>'external_id-'.date('U'),
        'customer'=> [
            'phone' => '+79670660742',
            'email' => 'regs@babak.ru',
        ],

        'items'=> [
            [
                'name' => 'тестовая покупка',
                'price' => 1,
            ]
        ]
    ];

    $Atol->createBill( $test_bill );
```

## Обработка callback:
```php
    Atol::createCallback();
```

## Принудительно запросить статус обработки чека:
```php
    $Atol = new Atol;
    $Atol->getResponce( $uiid );
```

## Документация
[API](https://github.com/Doctorrr/php-atol/blob/master/API%20сервиса%20АТОЛ%20Онлайн_v3.pdf)
