<?php
/**
 * Created by IntelliJ IDEA.
 * User: Alex Babak
 * Date: 01.11.2017
 * Time: 5:58
 *
 * Класс для работы с виртуальными кассами компании АТОЛ 42ФС https://online.atol.ru
 * (сервис отправляет данные в налоговую по 54-ФЗ + отправляет электронный чек клиенту).
 *
 * В текущем проекте товары продаются по 1 штуке,
 * так что для стандартного магазина надо дописывать метод createBill().
 *
 * Доп функции:
 *  - json_fix_cyr -- фикс кириллицы для json
 *  - sys_alert -- вывод ошибки
 *  - sys_dbstring -- экранирование ввода в БД
 *  - sys_query -- отправка запроса к БД
 */

class Atol
{

    private $token;
    private $login;
    private $pass;
    private $callback_url;
    private $group_code;

    /**
     * Atol constructor.
     */
    public function __construct() {

        //устанавливаем параметры из "файла настроек для cms" https://online.atol.ru/lk/Company/List

        $this->callback_url = 'https://Example.com/api/atol/callback/'; //URL приёма автоматических ответов от сервиса АТОЛ
        $this->payment_address = 'Example.com';

        //А это -- тестовые настройки:
       $this->login = 'atolonlinetest2';
       $this->pass = 'LpywGxLi7';
       $this->group_code = 'AtolOnline2-Test';
       $this->inn = '7717586110';

        //формируем запрос на токен согласно документации
        $token_request = json_fix_cyr( json_encode( [
            'login'=>$this->login,
            'pass'=>$this->pass,
        ] ) );

        $curl = curl_init('https://online.atol.ru/possystem/v3/getToken' );
        curl_setopt( $curl, CURLOPT_POST, true );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $token_request );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($token_request))
        );

        $token_responce = curl_exec( $curl );
        $token_responce = json_decode( $token_responce );

        if ( $token_responce->code >=2  ) {
            exit ( sys_alert('Ошибка выдачи электронного чека', 'danger' ) );

        }

        //получили токен
        $this->token = $token_responce->token;

    }


    /**
     * @param array $params
     * Пример запроса: https://jsonformatter.org/4b455c
     *
     * в текущем проекте товары продаются по одному, поэтому ограничим список параметров
     */
    public function createBill(array $params ) {
        //проверяем необходимые параметры товара
        $required_params = [
            $params['external_id'], //Идентификатор документа внешней системы, уникальный среди всех документов, отправляемых в данную группу ККТ.
            $params['items'][0]['name'],
            $params['items'][0]['price'],

            $params['customer']['email'],
            $params['customer']['phone'],
        ];

        foreach ( $required_params as $required_param ) {
            if ( !$required_param ) {
                exit ( sys_alert( 'Недостаточно данных для формирования чека!', 'danger' ) );

            }
        }

        $bill_request = [
            'external_id' => $params['external_id'],

            'receipt' =>[
                'attributes' => [
                    'email' => $params['customer']['email'],
                    'phone' => $params['customer']['phone'],
                ],

                'items' => [
                    [
                        'name' => $params['items'][0]['name'],
                        'price' => (int)$params['items'][0]['price'],
                        'quantity' => 1,
                        'sum' => (int)$params['items'][0]['price'],
                        'tax' => 'vat0',
//                        'tax_sum',
                    ],
                ],

                'payments' => [
                    [
                        'type' => 1, //электронные платежи
                        'sum' =>(int) $params['items'][0]['price'],
                    ],
                ],

                'total' => (int)$params['items'][0]['price'],

            ],

            'service' => [
                'callback_url' => $this->callback_url,
                'inn' => $this->inn,
                'payment_address' => $this->payment_address,
            ],

            'timestamp' => date( 'd.m.Y H:i:s' ),

        ];

        $bill_request = json_fix_cyr( json_encode( $bill_request ) );

        echooo( $bill_request );

        $curl = curl_init('https://online.atol.ru/possystem/v3/'.$this->group_code.'/sell?tokenid=' . $this->token );
        curl_setopt( $curl, CURLOPT_POST, true );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $bill_request );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen( $bill_request ))
        );

        $bill_responce = curl_exec( $curl );
        $bill_responce_object = json_decode( $bill_responce );

        echooo('$bill_responce_object:' );
        echooo( $bill_responce_object );

        echo  '<a href="https://Example.com/curl_test.php?uiid='.$bill_responce_object->uiid.'">'.$bill_responce_object->uiid.'</a>';

        $sql = "INSERT INTO `sys_atol` SET
           `atol:external_id` = ".sys_dbstring( $params['external_id'] ).",
           `atol:uuid` = ".sys_dbstring( $bill_responce_object->uuid ).",
           `atol:document` = ".sys_dbstring( $bill_request ).",
           `atol:request` = ".sys_dbstring( $bill_responce ).",
           `atol:request_status` = ".sys_dbstring( $bill_responce_object->status )."
        ";
        sys_query( $sql );

    }

    /**
     * это обработка автоматического ответа от ATOL
     */
    public static function createCallback() {

        sys_mail ( 'alexey@babak.ru', 'ATOL callback:<br><pre>'.print_r( $_POST ).'</pre>', 'ATOL callback' );

        $responce = json_decode( $_POST );
        $sql = "UPDATE `sys_atol` SET 
          `atol:responce` = ".sys_dbstring( print_r( $_POST ) ).",
          `atol:responce_status` = ".sys_dbstring( $responce->status )."
          
          WHERE `atol:uuid` = ".sys_dbstring( $responce->uuid )."
        ";
        sys_query( $sql );

    }


    /**
     * Это принудительный запрос, если коллбек не пришёл за 300 секунд
     * после регистрации документа в системе АТОЛ
     * (а на тесте он почему-то никогда не приходит)
     */
    public function getResponce( string $uiid ) {
        $url = 'https://online.atol.ru/possystem/v3/'.$this->group_code.'/report/'.$uiid.'?tokenid='.$this->token;

        echo '<a href="'.$url.'">'.$url.'</a><br>';

        $curl = curl_init( $url );
        curl_setopt( $curl, CURLOPT_POST, false );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);

        $responce = curl_exec( $curl );
        $responce_object = json_decode( $responce );

        echooo('$responce:' );
        echooo( $responce_object );

        $sql = "UPDATE `sys_atol` SET 
          `atol:responce` = ".sys_dbstring( $responce ).",
          `atol:responce_status` = ".sys_dbstring( $responce_object->status )."
          
          WHERE `atol:uuid` = ".sys_dbstring( $responce_object->uuid )."
        ";
        sys_query( $sql );

    }


    /**
     * Форма для выписки чека
     */
    public static function billForm() {
        sys_minRole( 50 );

        $form = [];

    }

    /**
     * листинг операций
     */
    public static function list() {
        sys_minRole( 50 );

        $sql = "SELECT * FROM `sys_atol` ORDER BY `atol:id` DESC";
        return sys_assoc( $sql );

    }




    /**
     * @return mixed
     */
    public function getToken()
    {
        sys_minRole( 50 );
        return $this->token;
    }

    /**
     * @return string
     */
    public function getGroupCode(): string
    {
        sys_minRole( 50 );
        return $this->group_code;
    }



}