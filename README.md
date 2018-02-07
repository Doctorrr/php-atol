# php-atol
Russian tax service php class
 
https://online.atol.ru -- касса как сервис для интернет-торговли на базе кассового комплекса АТОЛ 42ФС.

php-atol -- класс для работы с виртуальными кассами компании АТОЛ 42ФС 
(этот сервис отправляет данные в налоговую по 54-ФЗ + отправляет электронный чек клиенту).
  
В текущем проекте товары продаются по 1 штуке, так что для стандартного магазина надо чуть дописать метод createBill().
 
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

## Результат:
![Результат](https://github.com/Doctorrr/php-atol/blob/master/photo_2017-11-09_19-24-37.jpg "Результат")

## Обработка callback (обратного вызова от сервиса):
```php
    Atol::createCallback();
```

## Принудительно запросить статус обработки чека по его уникальному номеру (если не получили callback):
```php
    $Atol = new Atol;
    $Atol->getResponce( $uiid );
```

## Документация
[API](https://github.com/Doctorrr/php-atol/blob/master/API%20сервиса%20АТОЛ%20Онлайн_v3.pdf)
