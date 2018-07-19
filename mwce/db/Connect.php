<?php

namespace mwce\db;

use mwce\Exceptions\CfgException;
use mwce\Exceptions\DBException;
use mwce\Tools\Configs;
use mwce\Tools\Logs;

/**
 * Class Connect
 * @package mwce
 *
 * @method FetchRow
 * @method GetRow
 * @method GetArray
 * @method fetch($params = null)
 * @method fetchAll($params = null)
 */
class Connect
{

    //region типы подключений
    public const ODBC = 1;
    public const MYSQL = 2;
    public const MSSQL = 3;
    public const INSTALL = 4;
    public const SQLSRV = 5;
    //endregion

    /**
     * @var array
     * список доступных подключений для выбора
     */
    static public $conList = array(
        self::ODBC => 'MS SQL PDO ODBC',
        self::MYSQL => 'PDO MySql',
        self::MSSQL => 'PDO MS SQL',
        self::SQLSRV => 'PDO SQLSRV (ms sql)',
    );

    /**
     * @var array пул подключений
     */
    static protected $pool = []; //пул подключений

    /**
     * @var int количество запросов
     */
    static public $queryCount = 0;

    /**
     * @var \PDO
     */
    protected $resId;

    /**
     * @var \PDO
     * последний запрос
     */
    protected $lastQh;

    /**
     * @var int
     * тип текущего подключения
     */
    protected $curConType;

    /**
     * @var array
     * массив с командами при подключении PDO
     */
    protected $commands = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET names 'utf8'",
    ];

    /**
     * точка входа singleton
     * @param int $conNum номер или название подключения
     * @return mixed|Connect
     * @throws \mwce\Exceptions\DBException
     * @throws \mwce\Exceptions\CfgException
     */
    public static function start($conNum = null)
    {
        if (null === $conNum) {
            $conNum = (int)(FALSE !== Configs::buildCfg('defConNum') ? Configs::buildCfg('defConNum') : Configs::globalCfg('defaultConNum'));
        }

        if (!isset(self::$pool[$conNum])) {
            if ($conNum === -1) {
                self::$pool[$conNum] = new self($conNum);
            }
            else {
                self::$pool[$conNum] = new self($conNum);
            }
        }

        return self::$pool[$conNum];
    }

    /**
     * Connect constructor.
     * @param $conNum
     * @throws CfgException
     * @throws DBException
     */
    private function __construct($conNum)
    {
        if ($conNum === -1) {
            $configs = array(
                -1 => [
                    'server' => $_SESSION['installServer'],
                    'db' => !empty($_SESSION['installDb']) ? $_SESSION['installDb'] : '',
                    'user' => $_SESSION['installUsr'],
                    'password' => $_SESSION['installPwd'],
                    'type' => $_SESSION['installCt']
                ]
            );
        }
        else {

            $configs = Configs::loadConnectionCfg();

            if (empty($configs) || !\is_array($configs)) {
                throw new CfgException('Connections config is empty or wrong! Build: ' . Configs::currentBuild());
            }

            if (empty($configs[$conNum]) && $conNum === 'siteBase') //если нет отдельно выделенного конфига под базу(с настройками) сайта, то переключаем в умолчание
            {
                $conNum = (int)Configs::globalCfg('defaultConNum');
            }

            if (empty($configs[$conNum])) {
                throw new CfgException('Config file corrupted');
            }

            if (empty($configs[$conNum]['type'])) {
                throw new CfgException('Config file corrupted: connection type is empty');
            }
        }

        $this->curConType = $configs[$conNum]['type'];

        try {
            switch ($configs[$conNum]['type']) {
                case self::MSSQL:
                    $this->mssql($configs[$conNum]);
                    break;
                case self::MYSQL:
                    $this->mysql($configs[$conNum]);
                    break;
                case self::ODBC:
                    $this->odbc($configs[$conNum]);
                    break;
                case self::INSTALL:
                    return;
                case self::SQLSRV:
                    $this->sqlsrv($configs[$conNum]);
            }
        }
        catch (\Exception $e) {
            throw new DBException($e->getMessage(), 1, $e);
        }
    }

    //region "коннекторы"

    /**
     * @param array $params
     */
    private function odbc($params): void
    {
        $this->resId = new \PDO('odbc:Driver={SQL Server};SERVER=' . $params['server'] . ';Database=' . $params['db'] . ';', $params['user'], $params['password']);
        $this->resId->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param array $params
     */
    private function mysql($params): void
    {
        $this->resId = new \PDO('mysql:host=' . $params['server'] . ';dbname=' . $params['db'], $params['user'], $params['password'], $this->commands);
    }

    /**
     * @param array $params
     */
    private function mssql($params): void
    {
        $this->resId = new \PDO('dblib:host=' . $params['server'] . ';dbname=' . $params['db'], $params['user'], $params['password']);
        $this->resId->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param $params
     */
    private function sqlsrv($params): void
    {
        $this->resId = new \PDO('sqlsrv:server=' . $params['server'] . ';Database=' . $params['db'], $params['user'], $params['password']);
        $this->resId->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    //endregions

    /**
     * логи в таблицу mwc_logs
     *
     * @param string $msg - текст лога
     * @param string $file - файл, в котором ахтунг
     * @param int $errNo - номер ошибки
     * @param bool|true $isValid - экранировать ли текст лога?
     * @throws \Exception
     */
    public function SQLog($msg, $file = '1', $errNo = 0, $isValid = true): void
    {

        if ($this->curConType === self::INSTALL) {
            Logs::textLog($errNo, $msg);
            return;
        }

        $this->closeCursor();

        if ($file === '1') {
            $file = basename(__FILE__, '.php');
        }

        if ($isValid === true) {
            $msg = htmlspecialchars($msg, ENT_QUOTES);
        }

        $dt = self::MSSQL === $this->curConType || self::ODBC === $this->curConType ? 'GETDATE()' : 'NOW()';

        $msg = str_replace('\\', '/', $msg);
        $file = str_replace('\\', '/', $file);
        self::$queryCount++;

        if (empty($msg)) {
            return;
        }

        try {
            $this->resId->exec("INSERT INTO mwce_logs(col_ErrNum,col_msg,col_mname,col_createTime,tbuild) VALUES($errNo,'$msg','$file',$dt,'" . Configs::currentBuild() . "')");
        }
        catch (\Exception $e) {
            Logs::textLog(1, $e->getMessage() . ' log text: ' . $msg);
        }
    }

    /**
     * функция возвращает последний insert id
     * @param string|null $tbname - название таблицы, куда была последняя вставка
     * @return int id
     * @throws DBException
     */
    public function lastId($tbname = null): ?int
    {
        try {
            return $this->resId->lastInsertId($tbname);
        }
        catch (\Exception $e) {
            if ($this->curConType !== self::MYSQL) // ms
            {
                if (!$tbname) {
                    return NULL;
                }

                $res = $this->query("SELECT IDENT_CURRENT('{$tbname}') as lastid")->fetch();
            }
            else {
                $res = $this->query('SELECT LAST_INSERT_ID()  as lastid')->fetch();
            }
            return $res['lastid'];
        }

    }

    /**
     * @param string $qtext
     * @param null|array $bind
     * @return bool|Connect|\PDO
     * @throws DBException
     */
    public function query($qtext, array $bind = [])
    {

        self::$queryCount++;
        try {
            $this->lastQh = $this->resId->prepare($qtext);
            $this->lastQh->execute($bind);
        }
        catch (\Exception $e) {
            throw new DBException($e->getMessage() . ', log text: ' . $qtext);
        }


        return $this;
    }

    /**
     * принудительно осободить ресурсы для выполнения след. задания.
     * @return bool
     */
    public function closeCursor(): bool
    {
        try {
            if (null !== $this->lastQh && \is_object($this->lastQh)) {
                $this->lastQh->closeCursor();
            }
            return true;
        }
        catch (\Exception $e) {
            //throw new DBException($e->getMessage(),3,$e);
        }
        return false;
    }

    /**
     * @param string $qtext
     * @param array $bind
     * @return bool
     * @throws DBException
     */
    public function exec($qtext, $bind = []): bool
    {
        self::$queryCount++;
        try {
            $dbh = $this->resId->prepare($qtext);
            $res = $dbh->execute($bind);
        }
        catch (\Exception $e) {
            throw new DBException($e->getMessage() . ', log text: ' . $qtext);
        }

        return $res;
    }


    //region magic

    /**
     * @param $name
     * @param $arguments
     * @return bool|string
     * @throws DBException
     */
    public function __call($name, $arguments)
    {
        $ars = '';
        $obj = $this->lastQh;

        if (\is_object($obj)) {
            switch (strtolower($name)) {
                case 'getrows':
                case 'getarray':
                case 'fetchall':
                    try {
                        $ars = !empty($arguments[0]) ? $obj->fetchAll(\PDO::FETCH_CLASS, $arguments[0]) : $obj->fetchAll(\PDO::FETCH_ASSOC);
                    }
                    catch (\Exception $e) {
                        throw new DBException($e->getMessage(), 1, $e);
                    }
                    break;
                case 'fetch':
                case 'fetchrow':
                    try {
                        if (!empty($arguments[0])) {
                            $ars = $obj->fetchObject($arguments[0]);
                        }
                        else {
                            $ars = $obj->fetch(\PDO::FETCH_ASSOC);
                        }
                    }
                    catch (\Exception $e) {
                        throw new DBException($e->getMessage(), 1, $e);
                    }
                    break;
                default:
                    break;
            }

            if (!empty($ars)) {
                return $ars;
            }

        }
        else {
            throw new DBException('Call to a member function ' . $name . '() on boolean');
        }

        return false;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'type':
                return $this->curConType;
                break;
            case 'suf':
                if ($this->curConType !== self::MYSQL) {
                    return 'dbo.';
                }
                return '';
            default:
                return false;
        }
    }

    public function __set($name, $value)
    {

    }

    public function __isset($name)
    {

    }

    //endregion
}
