<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 31.07.2018
 *
 **/

namespace mwce\Tools;

class HttpHeaders
{
    /**
     * @var HttpHeaders instance
     */
    protected static $instance;

    /**
     * @var array width data from http
     */
    private $headers = array();

    /**
     * HttpHeaders constructor.
     */
    private function __construct()
    {
        if(!empty($_SERVER)){
            $this->headers['method'] = !empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
            $this->headers['query'] = !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
            $this->headers['charset'] = !empty($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : '';
            $this->headers['connection'] = !empty($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : '';
            $this->headers['userAgent'] = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $this->headers['referer'] = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $this->headers['host'] = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
            $this->headers['from'] = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
            $this->headers['fromDomain'] = !empty($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : '';
            $this->headers['user'] = !empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : '';
            $this->headers['script'] = !empty($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
            $this->headers['port'] = !empty($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : null;
            $this->headers['serverPort'] = !empty($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null;
            $this->headers['uri'] = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $this->headers['aDigest'] = !empty($_SERVER['PHP_AUTH_DIGEST']) ? $_SERVER['PHP_AUTH_DIGEST'] : '';
            $this->headers['aUser'] = !empty($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
            $this->headers['aPwd'] = !empty($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
            $this->headers['aType'] = !empty($_SERVER['AUTH_TYPE']) ? $_SERVER['AUTH_TYPE'] : '';
            $this->headers['pathInfo'] = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
            $this->headers['ajax'] = false;

            if(!empty($_SERVER['SERVER_PROTOCOL'])){
                $this->headers['protocol'] = explode('/',$_SERVER['SERVER_PROTOCOL'])[1];
            }

            if(!empty($_SERVER['SERVER_SOFTWARE'])){
                $this->headers['webServer'] = $_SERVER['SERVER_SOFTWARE'];
            }

            if(!empty($_SERVER['HTTP_ACCEPT'])){
                $this->headers['accept'] = explode(',',$_SERVER['HTTP_ACCEPT']);
            }

            if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
                $this->headers['lang'] = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            }

            if(!empty($_SERVER['HTTP_ACCEPT_ENCODING'])){
                $this->headers['encoding'] = explode(',',$_SERVER['HTTP_ACCEPT_ENCODING']);
            }

            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])){

                if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'){
                    $this->headers['ajax'] = true;
                }
                else{
                    $this->headers['requestWith'] = $_SERVER['HTTP_X_REQUESTED_WITH'];
                }
            }

            $this->headers['https'] = !empty($_SERVER['HTTPS']) ? true : false;
        }
    }

    /**
     * @return array
     */
    public static function get() : array {
        if(self::$instance === null){
            self::$instance = new self();
        }

        return self::$instance->headers;
    }

}