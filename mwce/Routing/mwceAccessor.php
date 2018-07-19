<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 25.11.2016
 *
 **/
namespace mwce\Routing;

use mwce\db\Connect;
use mwce\Interfaces\ImwceAccessor;
use mwce\Tools\Content;

abstract class mwceAccessor implements ImwceAccessor
{
    /**
     * @var array
     */
    protected $pages = [];

    /**
     * @var array
     */
    protected $plugins = [];

    /**
     * @var Content
     */
    protected $view;

    /**
     * @var Connect
     */
    protected $db;

    /**
     * mwceAccessor constructor.
     * @param Content $view
     * @param int $conNum
     * @throws \mwce\Exceptions\CfgException
     * @throws \mwce\Exceptions\DBException
     */
    public function __construct(Content $view,$conNum = 0)
    {
        $this->db = Connect::start($conNum);
        $this->view = $view;
    }

    /**
     * @return array
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * @return array
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * @param string $page
     * @return bool|array
     */
    public function getCurPage($page)
    {
        if(!empty($this->pages[$page])){
            return $this->pages[$page];
        }

        return false;
    }

    /**
     * @param string $plugin
     * @return bool|array
     */
    public function getCurPlugin($plugin)
    {
        if(!empty($this->plugins[$plugin])){
            return $this->plugins[$plugin];
        }

        return false;
    }
}