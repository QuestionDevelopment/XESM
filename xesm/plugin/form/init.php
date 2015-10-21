<?php
/**
 * Form Creation Class
 *
 * @version 0.9
 * @package xesm
 * @subpackage plugin
 * @category class
 *
 * @author Josh Cunningham <josh@joshcanhelp.com>
 * @author Justin Campo <admin@limberCMS.com>
 * @license MIT http://opensource.org/licenses/MIT
 */
namespace plugin\form;
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
        $this->c->assign("form",function ($c) { return new \plugin\form\form($c); });
        $this->c->assign("form_item",function ($c) { return new \plugin\form\form_item(); });
        $this->c->assign("form_js",function ($c) { return new \plugin\form\form_js(); });
        $this->c->factory("form_item");
    }
}
