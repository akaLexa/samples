<?php
/**
 * MuWebCloneEngine
 * Version: 1.7
 * epmak.a@mail.ru
 * 09.08.2018
 **/

namespace mwce\Session;


/**
 * Class Session
 * @package mwce\Session
 * Обертка над механизмом сессий
 *
 * применение:
 * Session::Init()->ParameterName('parameter value')
 * Session::Init()->set('ParameterName','parameter value')
 * Session::Init()->ParameterName() - вернет текущее значение
 * Session::Init()->get('ParameterName')
 */
class Session implements \ArrayAccess
{
    /**
     * @var Session
     */
    private static $instance;

    /**
     * Session constructor.
     */
    private function __construct()
    {
        session_start();
    }

    /**
     * @return Session
     */
    public static function Init() : Session {
        if (self::$instance === null){
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function get(string $name) {
        return $_SESSION[$name] ?? null;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value) : void {
        $_SESSION[$name] = $value;
    }

    /**
     * @param string $name
     */
    public function unset(string $name) : void {
        if(isset($_SESSION[$name])){
            unset($_SESSION[$name]);
        }
    }

    public function destroy() : void {
        session_destroy();
    }

    public function abort() : void {
        session_abort();
    }

    public function getAll() : array {
        return $_SESSION;
    }

    public function __call($name, $arguments)
    {
        if(empty($arguments)){
            return $this->get($name) ?? null;
        }

        $this->set($name,$arguments[0]);
        return null;

    }

    //region ArrayAccess

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset) : bool
    {
        return isset($_SESSION[$offset]) ? true : false;
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $_SESSION[$offset] ?? null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value) : void
    {
        $_SESSION[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset) : void
    {
        unset($_SESSION[$offset]);
    }

    //endregion
}