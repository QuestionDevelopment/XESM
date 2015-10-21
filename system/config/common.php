<?php
/**
 * Global Config File
 *
 * @version 1.0
 * @package xesm
 * @subpackage config
 * @category class
 * @author Justin Campo <admin@xesm.com>
 * @license MIT http://opensource.org/licenses/MIT
 */
namespace xesm\config;

/**
 * Global Config File
 *
 * This class stores all data that is site wide and
 * will be used regardless of version of site.
 */
class common
{
    //
    // Security
    //
    /** The site wide security salt of the website @var string */
    public $security_salt = '@@lkds@@980z';

    //
    // Database:
    //
    /** The type of database being used @var string */
    public $db_type = 'mysql';
    /** The address/location of the database @var string */
    public $db_host = '127.0.0.1';

    //
    // Xesm Dirs
    //
    /** The location of the bundle folder @var string */
    public $dir_bundle= 'xesm/bundle/';
    /** The location of the cache folder @var string */
    public $dir_cache = 'xesm/cache/';
    /** The location core files are stored @var string */
    public $dir_core = 'xesm/core/';
    /** The location dcoumentation files are stored @var string */
    public $dir_doc = 'xesm/doc/';
    /** The location logs are saved in @var string */
    public $dir_log = 'xesm/log/';
    /** The location the plugins are stored @var string */
    public $dir_plugin = 'xesm/plugin/';

    //
    // Site Dirs
    //
    /** The location of the config folder @var string */
    public $dir_config = 'site/config/';
    /** The location of the controller folder @var string */
    public $dir_controller = 'site/controller/';
    /** The location of the cron folder @var string */
    public $dir_cron = 'site/cron/';
    /** The location of the inc folder @var string */
    public $dir_inc = 'site/inc/';
    /** The location of the interface folder @var string */
    public $dir_item = 'site/item/';
    /** The location of the this view folder @var string */
    public $dir_model = 'site/model/';
    /** The location where core files are stored @var string */
    public $dir_object= 'site/object/';
    /** The location where scripts files are stored @var string */
    public $dir_script= 'site/script/';
    /** The location of the this view folder @var string */
    public $dir_view = 'site/view/';
    /** The location user uploads are saved in @var string */
    public $dir_upload = 'site/upload/';
    /** The location where the query objects are held @var string */
    public $dir_query = 'site/query/';

    //
    // Template Dirs
    //  
    /** The location of the template folder @var string */
    public $dir_template = 'template/';
}
