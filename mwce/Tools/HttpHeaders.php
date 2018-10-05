<?php
/**
 * MuWebCloneEngine
 * Version: 1.7
 * epmak.a@mail.ru
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
            //$this->headers['method'] = !empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
            $this->headers['method'] = $_SERVER['REQUEST_METHOD'] ??  '';
            $this->headers['query'] = $_SERVER['QUERY_STRING'] ?? '';
            $this->headers['charset'] = $_SERVER['HTTP_ACCEPT_CHARSET'] ?? '';
            $this->headers['connection'] =  $_SERVER['HTTP_CONNECTION'] ?? '';
            $this->headers['userAgent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $this->headers['referer'] = $_SERVER['HTTP_REFERER'] ?? '';
            $this->headers['host'] =  $_SERVER['HTTP_HOST'] ?? '';
            $this->headers['from'] =  $_SERVER['REMOTE_ADDR'] ?? '';
            $this->headers['fromDomain'] = $_SERVER['REMOTE_HOST'] ?? '';
            $this->headers['user'] =  $_SERVER['REMOTE_USER'] ?? '';
            $this->headers['script'] = $_SERVER['SCRIPT_FILENAME'] ?? '';
            $this->headers['port'] = $_SERVER['REMOTE_PORT'] ?? null;
            $this->headers['serverPort'] = $_SERVER['SERVER_PORT'] ?? null;
            $this->headers['uri'] = $_SERVER['REQUEST_URI'] ?? '';
            $this->headers['aDigest'] = $_SERVER['PHP_AUTH_DIGEST'] ?? '';
            $this->headers['aUser'] = $_SERVER['PHP_AUTH_USER'] ?? '';
            $this->headers['aPwd'] = $_SERVER['PHP_AUTH_PW'] ?? '';
            $this->headers['aType'] = $_SERVER['AUTH_TYPE'] ?? '';
            $this->headers['pathInfo'] = $_SERVER['PATH_INFO'] ?? '';
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
                $this->headers['encoding'] = explode(', ',$_SERVER['HTTP_ACCEPT_ENCODING']);
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