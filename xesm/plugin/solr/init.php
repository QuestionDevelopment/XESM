<?php
/**
 * API CLASS
 *
 * @version 0.9
 * @package xesm
 * @subpackage plugin
 * @category class
 *
 * @author Justin Campo <admin@limberCMS.com>
 * @license MIT http://opensource.org/licenses/MIT
 */
namespace plugin\solr;
/*
 * Form Creation Class
 *
 * This class dynamically creates forms
 *
 */
class init
{
    /** Stores class objects used for dependency injection @var array */
    protected $c = array();

    /**
     * Form Class Constructor
     *
     * @param array $c All class dependencies are imported and verified here
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->c->assign("solr",function ($c) { return new \plugin\solr\solr($c->config); });
        //$this->c->hook->add('pre_model', '\plugin\api\solr', 'request');
    }
}

