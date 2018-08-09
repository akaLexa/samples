<?php

/**
 * MuWebCloneEngine
 * epmak.a@mail.ru
 * 09.08.2010
 **/

namespace mwce\Routing;

use mwce\Tools\HttpHeaders;

/**
 * Class URLParser
 * @package mwce\Routing
 *
 * Пасинг URI-запроса или параметров командной строки
 * =====================
 * URI-запрос
 * =====================
 * парсер читает запрос типа siteaddress/<type>/controller/action, где
 * <type> - тип запроса
 * controller - модуль, к которому нужно обратиться
 * action - метод, к который будет запущен
 * Например: mysite.com/page/News/GetNews.html будет иметь следующие данные:
 * тип запроса - page
 * модуль - News
 * action - GetNews
 * Например: mysite.com/json/News/GetNewsperID?num=1
 * тип запроса - json
 * модуль - News
 * action - GetNewsperID
 *
 * =====================
 * командная строка
 * =====================
 * Параметры из командной строки пишутся парами (название=значение) через пробел, например:
 * php path\to\index\index.php build=mailer module=mail timeout=150,limit=10
 * т.е., шаблон: index.php build=<название build> module=controller [param1=2,param2=3...]
 * парсер вернет:
 * isCmd = true
 * build = mailer
 * type = true
 * controller = mail
 * timeout=150,limit=10 станет $_GET['timeout'] = 150; $_GET['limit'] = 10;
 */
class URLParser
{
    /**
     * возможные типы запросов
     * @var array
     */
    public static $types = array( 'page', 'json' );

    /**
     * @var URLParser
     */
    protected static $inst;

    /**
     * Возвращаемые параметры
     * @var array
     */
    protected $parserData = array(
        //запуск из-под CLI?
        'isCmd' => false,
        //аякс запрос?
        'isBg' => false,
        //тип запроса по адресу из URI
        'type' => 'page',
        //какое приложение (используется для CLI)
        'build' => null,
        //какой модуль
        'controller' => null,
        //какое действие
        'action' => null,
    );

    /**
     * порядок чтения из URI
     * @var array
     */
    protected $URI_template = array( 'type', 'controller', 'action' );

    /**
     * @return array
     */
    public static function Parse(): array
    {
        if (null === self::$inst) {
            self::$inst = new self();
        }

        return self::$inst->parserData;
    }

    /**
     * URLParser constructor.
     */
    protected function __construct()
    {
        $url = '';

        if (empty($_SERVER['argc'])) {
            $url = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        }
        else {
            if ($_SERVER['argc'] > 0) {
                $url = $this->parseCLI();
            }
        }

        $path = preg_replace('/(\.){1}(\w)+/', '', trim(parse_url($url, PHP_URL_PATH), '/'));
        $this->parserData['isBg'] = HttpHeaders::get()['ajax'];

        $path_array = explode('/', $path);

        if (!empty($path_array)) {

            foreach ($this->URI_template as $category) {
                $this->parserData[$category] = array_shift($path_array);
                if (empty($path_array)) {
                    break;
                }
            }
        }
        else {
            $this->parserData['type'] = self::$types[0];
        }
    }

    /**
     * парсинг параметров из командной строки
     * @return string
     */
    private function parseCLI(): string
    {
        $this->parserData['isCmd'] = true;

        if (empty($_SERVER['argv'][1])) {
            die('type build is undefined!');
        }

        $data_ = explode('=', $_SERVER['argv'][1]);
        $this->parserData['build'] = $data_[1];

        if (empty($_SERVER['argv'][2])) {
            die('type page is undefined!');
        }

        $tmp = explode('=', $_SERVER['argv'][2]);
        $url = 'page/' . $tmp[1];

        //region данные в GET массив
        if (!empty($_SERVER['argv'][3])) {

            $params = explode(',', $_SERVER['argv'][3]);
            foreach ($params as $item) {
                $data_ = explode('=', $item);
                $_GET[trim($data_[0])] = trim($data_[1]);
            }
        }
        //endregion

        return $url;
    }
}