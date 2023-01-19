<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\Module\CModule;
use Ox\Core\Plugin\Button\ButtonPluginManager;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CThumbnail;
use ReflectionClass;
use Smarty;

/**
 * Mediboard integration of Smarty engine main class
 *
 * Provides an extension of smarty class with directory initialization
 * integrated to Mediboard framework as well as standard data assignment
 */
class CSmartyMB extends Smarty
{
    static $extraPath = "";

    protected static $compiled_cache = [];

    /**
     * Construction
     * Directories initialisation
     * Standard data assignment
     *
     * @param string $dir The template directory
     */
    function __construct($dir = null)
    {
        global $can, $m, $a, $tab, $g, $f, $action, $actionType, $dialog, $ajax, $suppressHeaders, $uistyle;

        $rootDir   = CAppUI::conf("root_dir");
        $extraPath = self::$extraPath;

        if (!$dir) {
            $root = $extraPath ? "$rootDir/$extraPath" : $rootDir;
            $dir  = "$root/modules/$m";
        }

        // Sf dotenv toto wait 01_2023
//        if ($_SERVER['APP_DEBUG'] === '0') {
//            $this->compile_check = false;
//        }

        $this->compile_dir = "$rootDir/tmp/templates_c/";

        // Directories initialisation
        $this->template_dir = "$dir/templates/";

        // Check if the cache dir is writeable
        if (!is_dir($this->compile_dir)) {
            CMbPath::forceDir($this->compile_dir);
        }

        // Delimiter definition
        $this->left_delimiter  = "{{";
        $this->right_delimiter = "}}";

        // Default modifier for security reason
        $this->default_modifiers = ["@cleanField"];

        // Register mediboard functions
        $this->register_block("tr", [$this, "tr"]);
        $this->register_block("thumblink", [$this, "thumblink"]);

        $this->register_function("mb_default", [$this, "mb_default"]);
        $this->register_function("mb_ditto", [$this, "mb_ditto"]);
        $this->register_function("mb_class", [$this, "mb_class"]);
        $this->register_function("mb_path", [$this, "mb_path"]);
        $this->register_function("mb_configure", [$this, "mb_configure"]);
        $this->register_function("mb_value", [$this, "mb_value"]);
        $this->register_function("mb_include", [$this, "mb_include"]);
        $this->register_function("mb_include_buttons", [$this, "mb_include_buttons"]);
        $this->register_function("mb_script", [$this, "mb_script"]);
        $this->register_function("mb_vue", [$this, "mb_vue"]);
        $this->register_function("mb_entry_point", [$this, "mb_entry_point"]);
        $this->register_function("mb_script_register_end", [$this, "mb_script_register_end"]);
        $this->register_function("unique_id", [$this, "unique_id"]);
        $this->register_function("mb_didacticiel", [$this, "mb_didacticiel"]);
        $this->register_function("app_version_key", [$this, "app_version_key"]);
        $this->register_function("thumbnail", [$this, "thumbnail"]);

        $this->register_modifier("idex", [$this, "idex"]);
        $this->register_modifier("conf", [$this, "conf"]);
        $this->register_modifier('gconf', [$this, 'gconf']);
        $this->register_modifier("pad", [$this, "pad"]);
        $this->register_modifier("json", [$this, "json"]);
        $this->register_modifier("purify", [$this, "purify"]);
        $this->register_modifier("markdown", [$this, "markdown"]);
        $this->register_modifier("iso_date", [$this, "iso_date"]);
        $this->register_modifier("iso_time", [$this, "iso_time"]);
        $this->register_modifier("iso_datetime", [$this, "iso_datetime"]);
        $this->register_modifier("utc_datetime", [$this, "utc_datetime"]);
        $this->register_modifier("progressive_date", [$this, "progressive_date"]);
        $this->register_modifier("progressive_date_day", [$this, "progressive_date_day"]);
        $this->register_modifier("progressive_date_month", [$this, "progressive_date_month"]);
        $this->register_modifier("progressive_date_year", [$this, "progressive_date_year"]);
        $this->register_modifier("rel_datetime", [$this, "rel_datetime"]);
        $this->register_modifier("week_number_month", [$this, "week_number_month"]);
        $this->register_modifier("const", [$this, "_const"]);
        $this->register_modifier("static", [$this, "_static"]);
        $this->register_modifier("static_call", [$this, "static_call"]);
        $this->register_modifier("instanceof", [$this, "_instanceof"]);
        $this->register_modifier("cleanField", [$this, "cleanField"]);
        $this->register_modifier("stripslashes", [$this, "stripslashes"]);
        $this->register_modifier("emphasize", [$this, "emphasize"]);
        $this->register_modifier("ireplace", [$this, "ireplace"]);
        $this->register_modifier("ternary", [$this, "ternary"]);
        $this->register_modifier("trace", [$this, "trace"]);
        $this->register_modifier("currency", ["Ox\Core\CMbString", "currency"]);
        $this->register_modifier("percent", [$this, "percent"]);
        $this->register_modifier("threshold", [$this, "threshold"]);
        $this->register_modifier("map", [$this, "map"]);
        $this->register_modifier("spancate", [$this, "spancate"]);
        $this->register_modifier("float", [$this, "float"]);
        $this->register_modifier("integer", [$this, "integer"]);
        $this->register_modifier("decabinary", [$this, "decabinary"]);
        $this->register_modifier("decasi", [$this, "decasi"]);
        $this->register_modifier("module_installed", [$this, "module_installed"]);
        $this->register_modifier("module_active", [$this, "module_active"]);
        $this->register_modifier("JSAttribute", [$this, "JSAttribute"]);
        $this->register_modifier("nozero", [$this, "nozero"]);
        $this->register_modifier("ide", [$this, "ide"]);
        $this->register_modifier("first", [$this, "first"]);
        $this->register_modifier("last", [$this, "last"]);
        $this->register_modifier("highlight", [$this, "highlight"]);
        $this->register_modifier("getShortName", [$this, "getShortName"]);
        $this->register_modifier("tpl_exist", [$this, "tpl_exist"]);

        // Todo: Remove from Smarty constructor (=> includes in main) and ref the templates using $modules variable
        // Complete module list should not be necessary...
        $modules = CModule::getActive();
        foreach ($modules as $mod) {
            $mod->canDo();
        }

        $this->register_compiler_function("mb_return", [$this, "mb_return"]);

        // Standard data assignment
        $this->assign("style", $uistyle);
        $this->assign("app", CAppUI::$instance);
        $this->assign("conf", CAppUI::conf());
        $this->assign("user", CAppUI::$instance->user_id); // shouldn't be necessary
        $this->assign("version", CApp::getVersion()->toArray());
        $this->assign("suppressHeaders", $suppressHeaders);
        $this->assign("can", $can);
        $this->assign("m", $m);
        $this->assign("a", $a);
        $this->assign("tab", $tab);
        $this->assign("action", $action);
        $this->assign("actionType", $actionType);
        $this->assign("g", $g);
        $this->assign("f", $f);
        $this->assign("dialog", $dialog);
        $this->assign("ajax", $ajax);
        $this->assign("modules", $modules);
        $this->assign("base_url", CApp::getBaseUrl());
        $this->assign("ua", CAppUI::getUA());
        $this->assign("current_group", CGroups::loadCurrent());
        $this->assign("dnow", CMbDT::date());
        $this->assign("dtnow", CMbDT::dateTime());
        $this->assign("tnow", CMbDT::time());
    }

    /**
     * Mb_return
     */
    public function mb_return(): string
    {
        return "\nreturn;";
    }

    /**
     * Assign a template var to default value if undefined
     *
     * @param array $params Smarty parameters
     *                      * var  : Name of the var
     *                      * value: Default value of the var
     * @param self  $smarty The Smarty object
     */
    public function mb_default($params, &$smarty): void
    {
        $var   = CMbArray::extract($params, "var", true);
        $value = CMbArray::extract($params, "value", true);

        if (!isset($smarty->_tpl_vars[$var])) {
            $smarty->assign($var, $value);
        }
    }

    /**
     * Show a value if different from previous cached one
     *
     * @param array $params Smarty parameters
     *                      * name : Name of the cached value
     *                      * value: Value to show, empty string to clear out cache
     *                      * reset: Reset value
     * @param self  $smarty The Smarty object
     */
    public function mb_ditto($params, &$smarty): ?string
    {
        static $cache = [];
        $name        = CMbArray::extract($params, "name", null, true);
        $value       = CMbArray::extract($params, "value", null, true);
        $reset       = CMbArray::extract($params, "reset", false);
        $center      = CMbArray::extract($params, "center", false);
        $replacement = CMbArray::extract($params, "replacement", "|");
        $old         = '';
        if (!$reset) {
            $old = CMbArray::get($cache, $name, "");
        }
        $cache[$name] = $value;

        $new_value = $old != $value ? $value : $replacement;
        if ($center && $new_value == $replacement) {
            $new_value = "<div style='text-align:center;'>$new_value</div>";
        }

        return $new_value;
    }

    /**
     * Cette fonction prend les mêmes paramètres que mb_field, mais seul object est requis.
     *
     * @param array $params Smarty parameters
     *
     * @return string
     */
    public function mb_class($params): string
    {
        if (null == $object = CMbArray::extract($params, "object")) {
            $class = CMbArray::extract($params, "class", null, true);
        } else {
            $class = $object->_class;
        }

        return "<input type=\"hidden\" name=\"@class\" value=\"$class\" />";
    }

    /**
     * Mise en DOM du chemin de ressource du formulaire
     *
     * @param array $params Smarty parameters
     *                      url : Url de la ressource (ex. "/gui/admin/change_password")
     *
     * @return string
     */
    public function mb_path($params)
    {
        $url = CMbArray::extract($params, "url", null, true);

        return "<input type=\"hidden\" name=\"@path\" value=\"$url\" />";
    }

    /**
     *
     * @param array $params Smarty parameters
     * @param self  $smarty The Smarty object
     *
     * @return string
     */
    function mb_configure($params, &$smarty)
    {
        if ($module = CMbArray::extract($params, "module", null, true)) {
            $module = CModule::prefixModuleName($module);
        } // If no module is specified then use system configuration controller
        else {
            $module = "system";
        }

        return "<input type=\"hidden\" name=\"@config\" value=\"$module\" />";
    }

    /**
     * Get the value of a given field (property)
     *
     * @param array $params Smarty parameters
     * @param self  $smarty The Smarty object
     *
     * @return string
     */
    function mb_value($params, &$smarty)
    {
        /** @var CMbObject $object */
        $object = CMbArray::extract($params, "object", null, true);
        $field  = CMbArray::extract($params, "field");

        if (!$field) {
            return "<span onmouseover=\"ObjectTooltip.createEx(this, '$object->_guid')\">$object->_view</span>";
        }

        $value              = CMbArray::extract($params, "value");
        $accept_empty_value = CMbArray::extract($params, "accept_empty_value");

        if (null !== $value || $accept_empty_value) {
            $object->$field = $value;

            // Empties cache for forward references
            if (isset($object->_fwd[$field])) {
                unset($object->_fwd[$field]);
            }
        }

        $spec = $object->_specs[$field];

        return $spec->getHtmlValue($object, $params);
    }

    /**
     * Get a concrete filename for automagically created content
     *
     * @param string $auto_base   Base path
     * @param string $auto_source Source path
     * @param string $auto_id     Custom ID
     *
     * @return string
     */
    function _get_auto_filename($auto_base, $auto_source = null, $auto_id = null)
    {
        $_compile_dir_sep = $this->use_sub_dirs ? DIRECTORY_SEPARATOR : '^';

        // Get real template path
        $_return = $this->_get_template_compile_dir($auto_base, $auto_source);

        if (isset($auto_id)) {
            // make auto_id safe for directory names
            $auto_id = str_replace('%7C', $_compile_dir_sep, urlencode($auto_id));
            // split into separate directories
            $_return .= $auto_id . $_compile_dir_sep;
        }

        if (isset($auto_source)) {
            // make source name safe for filename
            $_filename = urlencode(basename($auto_source));
            $_crc32    = sprintf('%08X', crc32($auto_source));
            // prepend %% to avoid name conflicts with
            // with $params['auto_id'] names

            // increment this value at dev time to enforce template recompilation
            static $increment = 3;
            $_return .= "$_filename.$increment.%$_crc32%";
        }

        return $_return;
    }

    /**
     * Get template compile directory
     *
     * @param string $base
     * @param string $source
     *
     * @return string
     */
    protected function _get_template_compile_dir($base, $source)
    {
        static $_subdir_cache = [];

        $_key = $this->template_dir . $source;

        if (isset($_subdir_cache[$_key])) {
            $subdir = $_subdir_cache[$_key];
        } else {
            $realpath = realpath($this->template_dir . $source);

            $path = CMbPath::getRelativePath($realpath);
            $path = explode("/", $path);

            // Remove "templates" subdir
            CMbArray::removeValue("templates", $path);

            $subdir = dirname(implode("/", $path));

            $_subdir_cache[$_key] = $subdir;
        }

        return "$base$subdir/";
    }

    /**
     * Adapted method, to include a cache for "file_exists" calls
     *
     * @inheritdoc
     */
    function _parse_resource_name(&$params)
    {
        static $_file_exists = [];

        // split tpl_path by the first colon
        $_resource_name_parts = explode(':', $params['resource_name'], 2);

        if (count($_resource_name_parts) === 1) {
            // no resource type given
            $params['resource_type'] = $this->default_resource_type;
            $params['resource_name'] = $_resource_name_parts[0];
        } else {
            if (strlen($_resource_name_parts[0]) === 1) {
                // 1 char is not resource type, but part of filepath
                $params['resource_type'] = $this->default_resource_type;
            } else {
                $params['resource_type'] = $_resource_name_parts[0];
                $params['resource_name'] = $_resource_name_parts[1];
            }
        }

        if ($params['resource_type'] === 'file') {
            if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $params['resource_name'])) {
                // relative pathname to $params['resource_base_path']
                // use the first directory where the file is found
                foreach ((array)$params['resource_base_path'] as $_curr_path) {
                    $_fullpath = $_curr_path . DIRECTORY_SEPARATOR . $params['resource_name'];

                    // ------ Cache put here
                    if (isset($_file_exists[$_fullpath]) || (file_exists($_fullpath) && is_file($_fullpath))) {
                        $_file_exists[$_fullpath] = true;
                        $params['resource_name']  = $_fullpath;

                        return true;
                    }
                    // ------ End cache

                    // didn't find the file, try include_path
                    $_params = ['file_path' => $_fullpath];

                    require_once SMARTY_CORE_DIR . 'core.get_include_path.php';
                    if (smarty_core_get_include_path($_params, $this)) {
                        $params['resource_name'] = $_params['new_file_path'];

                        return true;
                    }
                }

                return false;
            } else {
                /* absolute path */
                return file_exists($params['resource_name']);
            }
        } elseif (empty($this->_plugins['resource'][$params['resource_type']])) {
            $_params = ['type' => $params['resource_type']];

            require_once SMARTY_CORE_DIR . 'core.load_resource_plugin.php';
            smarty_core_load_resource_plugin($_params, $this);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    function _fetch_resource_info(&$params)
    {
        static $_filemtime_cache = [];
        static $_is_cache_cache = [];

        if (!isset($params['get_source'])) {
            $params['get_source'] = true;
        }
        if (!isset($params['quiet'])) {
            $params['quiet'] = false;
        }

        $_return = false;
        $_params = ['resource_name' => $params['resource_name']];
        if (isset($params['resource_base_path'])) {
            $_params['resource_base_path'] = $params['resource_base_path'];
        } else {
            $_params['resource_base_path'] = $this->template_dir;
        }

        if ($this->_parse_resource_name($_params)) {
            $_resource_type = $_params['resource_type'];
            $_resource_name = $_params['resource_name'];
            switch ($_resource_type) {
                case 'file':
                    if ($params['get_source']) {
                        $params['source_content'] = $this->_read_file($_resource_name);
                    }

                    //// ---- Start cache
                    if (isset($_filemtime_cache[$_resource_name])) {
                        $_filemtime = $_filemtime_cache[$_resource_name];
                    } else {
                        $_filemtime                        = filemtime($_resource_name);
                        $_filemtime_cache[$_resource_name] = $_filemtime;
                    }

                    $params['resource_timestamp'] = $_filemtime;
                    $_return                      = isset($_is_cache_cache[$_resource_name]) || ($_is_cache_cache[$_resource_name] = is_file(
                            $_resource_name
                        ));
                    //// ---- End cache
                    break;

                default:
                    // call resource functions to fetch the template source and timestamp
                    if ($params['get_source']) {
                        $_source_return = isset($this->_plugins['resource'][$_resource_type]) &&
                            call_user_func_array(
                                $this->_plugins['resource'][$_resource_type][0][0],
                                [$_resource_name, &$params['source_content'], &$this]
                            );
                    } else {
                        $_source_return = true;
                    }

                    $_timestamp_return = isset($this->_plugins['resource'][$_resource_type]) &&
                        call_user_func_array(
                            $this->_plugins['resource'][$_resource_type][0][1],
                            [$_resource_name, &$params['resource_timestamp'], &$this]
                        );

                    $_return = $_source_return && $_timestamp_return;
                    break;
            }
        }

        if (!$_return) {
            // see if we can get a template with the default template handler
            if (!empty($this->default_template_handler_func)) {
                if (!is_callable($this->default_template_handler_func)) {
                    $this->trigger_error(
                        "default template handler function \"$this->default_template_handler_func\" doesn't exist."
                    );
                } else {
                    $_return = call_user_func_array(
                        $this->default_template_handler_func,
                        [
                            $_params['resource_type'],
                            $_params['resource_name'],
                            &$params['source_content'],
                            &$params['resource_timestamp'],
                            &$this,
                        ]
                    );
                }
            }
        }

        if (!$_return) {
            if (!$params['quiet']) {
                $this->trigger_error('unable to read resource: "' . $params['resource_name'] . '"');
            }
        } else {
            if ($_return && $this->security) {
                require_once SMARTY_CORE_DIR . 'core.is_secure.php';
                if (!smarty_core_is_secure($_params, $this)) {
                    if (!$params['quiet']) {
                        $this->trigger_error(
                            '(secure mode) accessing "' . $params['resource_name'] . '" is not allowed'
                        );
                    }
                    $params['source_content']     = null;
                    $params['resource_timestamp'] = null;

                    return false;
                }
            }
        }

        return $_return;
    }

    /**
     * @inheritdoc
     */
    function _is_compiled($resource_name, $compile_path)
    {
        if (!$this->force_compile
            && (isset(self::$compiled_cache[$compile_path])
                || self::$compiled_cache[$compile_path] = file_exists($compile_path))
        ) {
            if (!$this->compile_check) {
                // no need to check compiled file
                return true;
            }

            // get file source and timestamp
            $_params = ['resource_name' => $resource_name, 'get_source' => false];
            if (!$this->_fetch_resource_info($_params)) {
                return false;
            }
            if ($_params['resource_timestamp'] <= filemtime($compile_path)) {
                // template not expired, no recompile
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    function _compile_resource($resource_name, $compile_path)
    {
        $_params = ['resource_name' => $resource_name];
        if (!$this->_fetch_resource_info($_params)) {
            return false;
        }

        $_source_content = $_params['source_content'];
        $_cache_include  = substr($compile_path, 0, -4) . '.inc';

        if ($this->_compile_source($resource_name, $_source_content, $_compiled_content, $_cache_include)) {
            unset(self::$compiled_cache[$compile_path]);

            // if a _cache_serial was set, we also have to write an include-file:
            if ($this->_cache_include_info) {
                require_once SMARTY_CORE_DIR . 'core.write_compiled_include.php';
                smarty_core_write_compiled_include(
                    array_merge(
                        $this->_cache_include_info,
                        ['compiled_content' => $_compiled_content, 'resource_name' => $resource_name]
                    ),
                    $this
                );
            }

            $_params = ['compile_path' => $compile_path, 'compiled_content' => $_compiled_content];
            require_once SMARTY_CORE_DIR . 'core.write_compiled_resource.php';
            smarty_core_write_compiled_resource($_params, $this);

            return true;
        }

        return false;
    }

    /**
     * Show debug spans
     *
     * @param string $tpl_file Template file
     * @param string $params   Smarty parameters
     *
     * @return void
     */
    function showDebugSpans($tpl_file, $params)
    {
        $title = empty($params['ajax']) ? $tpl_file : '[AJAX] ' . $tpl_file;
        $class = 'smarty-include';

        $vars = isset($params["smarty_include_vars"]) ? $params["smarty_include_vars"] : [];
        foreach ($vars as $var => $value) {
            $show = $value;
            if ($value instanceof CMbObject) {
                $show = $value->_guid;
            }

            if (is_array($value)) {
                $count = count($value);
                $show  = "array ($count)";
            }

            $_var  = is_scalar($var) ? $var : (is_object($var) ? get_class($var) : gettype($var));
            $_show = is_scalar($show) ? $show : (is_object($show) ? get_class($show) : gettype($show));

            $title .= "&#10;" . CMbString::htmlEntities($_var) . ": " . CMbString::htmlEntities($_show);
        }

        // The span
        echo "\n<span class='{$class}' title='{$title}'></span>";
    }

    /**
     * Called for included templates
     *
     * @param array $params Smarty parameters
     *
     * @return void
     */
    function _smarty_include($params)
    {
        $tpl_file   = $params["smarty_include_tpl_file"];
        $vars       = $params["smarty_include_vars"];
        $skip_files = ["login.tpl", "common.tpl", "header.tpl", "footer.tpl", "tabbox.tpl", "ajax_errors.tpl"];


        if (CDevtools::isActive() !== true
            || isset($params["smarty_include_vars"]['nodebug'])
            || in_array(basename($tpl_file), $skip_files)
        ) {
            parent::_smarty_include($params);

            return;
        }

        // Only at debug time
        echo "\n<!-- Start include: $tpl_file -->\n";
        parent::_smarty_include($params);
        echo "\n<!-- Stop include: $tpl_file -->\n";
    }

    /**
     * Delegates the actual translation to CAppUI framework object
     *
     * @param array  $params  Smarty parameters
     * @param string $content Translation content
     */
    public function tr($params, $content): string
    {
        if (!isset($content)) {
            return '';
        }

        $markdown = CMbArray::extract($params, 'markdown');

        // Check for the multiple translation
        $vars = [];
        foreach ($params as $key => $value) {
            if (preg_match("/^var\d+/", $key)) {
                $vars[] = $value;
            }
        }

        // CAppUI translation
        $content = CAppUI::tr($content, $vars);

        foreach ($params as $_key => $_val) {
            switch ($_key) {
                case "escape":
                    if ($_val === "JSAttribute") {
                        $content = $this->JSAttribute($content);
                        break;
                    }

                    $content = smarty_modifier_escape($content, $_val);
                    break;

                default:
            }
        }

        if ($markdown) {
            $content = CMbString::purifyHTML(CMbString::markdown(CMbString::br2nl($content)));
        }

        return $content;
    }

    /**
     * Pad a string to a certain length with another string. like php/str_pad
     *
     * Example:  {$text|pad:20:'.':'both'}
     *    will pad $string with dots, in both sides
     *    until $text length equal to 20 characteres
     *    (assuming that $text has less than 20 characteres)
     *
     * @param string $string     The string to be padded
     * @param int    $length     Desired string length
     * @param string $pad_string String used to pad
     * @param string $pad_type   Both, left or right
     *
     * @return string
     */
    function pad($string, $length, $pad_string = ' ', $pad_type = 'left')
    {
        static $pads = [
            'left'  => STR_PAD_LEFT,
            'right' => STR_PAD_RIGHT,
            'both'  => STR_PAD_BOTH,
        ];

        return str_pad($string, $length, $pad_string, $pads[$pad_type]);
    }

    /**
     * JSON encode an object for Javascript use
     * Example:  {$object|json}
     *
     * @param mixed $object       The object to be encoded
     * @param bool  $force_object Force object notation for empty arrays : "{}"
     *
     * @return string
     */
    function json($object, $force_object = false)
    {
        if ($force_object && is_array($object) && empty($object)) {
            return "{}";
        }

        return CMbArray::toJSON($object, true);
    }

    /**
     * HTML input cleaner
     *
     * @param string $html HTML input
     *
     * @return string
     */
    function purify($html)
    {
        return CMbString::purifyHTML($html);
    }

    /**
     * Markdown parser
     *
     * @param string $text    Text input to parse
     * @param bool   $minimal Only format with emphasis, bold and lists
     *
     * @return string
     */
    function markdown($text, $minimal = false)
    {
        return CMbString::markdown($text, $minimal);
    }

  /**
   * Format to ISO DATE
   * Example: {$datetime|iso_date}
   *
   * @param string $datetime The date to format
   *
   * @return string
   */
  function iso_date($datetime) {
    return CMbDT::strftime("%Y-%m-%d", strtotime($datetime));
  }

  /**
   * Format to ISO TIME
   * Example: {$datetime|iso_time}
   *
   * @param string $datetime The date to format
   *
   * @return string
   */
  function iso_time($datetime) {
    return CMbDT::strftime("%H:%M:%S", strtotime($datetime));
  }

  /**
   * Format to ISO DATETIME
   * Example: {$datetime|iso_datetime}
   *
   * @param string $datetime The date to format
   *
   * @return string
   */
  function iso_datetime($datetime) {
    return CMbDT::strftime("%Y-%m-%d %H:%M:%S", strtotime($datetime));
  }

    /**
     * Format to UTC DATETIME (ISO8601)
     * Example: {$datetime|utc_datetime}
     *
     * @param string $datetime The date to format
     *
     * @return string
     */
    function utc_datetime($datetime)
    {
        return CMbDT::dateTimeToUTC($datetime);
    }

  /**
   * Format to a progressive date
   * Example: {$date|progressive_date}
   *
   * @param string $date The date to format
   *
   * @return string
   */
  function progressive_date($date) {
    $parts = explode('-', $date);
    $date  = $parts[0] . '-' . (intval($parts[1]) ? $parts[1] : '01') . '-' . (intval($parts[2]) ? $parts[2] : '01');
    if (intval($parts[1]) && intval($parts[2])) {
      return CMbDT::strftime("%d/%m/%Y", strtotime($date));
    }
    if (intval($parts[1])) {
      return CMbDT::strftime("%m/%Y", strtotime($date));
    }

    return CMbDT::strftime("%Y", strtotime($date));
  }

  /**
   * Show the day of a progressive date
   * Example: {$date|progressive_date_day}
   *
   * @param string $date   The date to format
   * @param string $format The day format
   *
   * @return string
   */
  function progressive_date_day($date, $format = "%d") {
    $parts = explode('-', $date);
    $date  = $parts[0] . '-' . (intval($parts[1]) ? $parts[1] : '01') . '-' . (intval($parts[2]) ? $parts[2] : '01');
    if (intval($parts[1]) && intval($parts[2])) {
      return CMbDT::strftime($format, strtotime($date));
    }

        return "";
    }

  /**
   * Show the day of a progressive date
   * Example: {$date|progressive_date_month}
   *
   * @param string $date   The date to format
   * @param string $format The month format
   *
   * @return string
   */
  function progressive_date_month($date, $format = "%B") {
    $parts = explode('-', $date);
    $date  = $parts[0] . '-' . (intval($parts[1]) ? $parts[1] : '01') . '-' . (intval($parts[2]) ? $parts[2] : '01');
    if (intval($parts[1])) {
      return CMbDT::strftime($format, strtotime($date));
    }

        return "";
    }

    /**
     * Show the day of a progressive date
     * Example: {$date|progressive_date_year}
     *
     * @param string $date   The date to format
     * @param string $format The year format
     *
     * @return string
     */
    function progressive_date_year($date, $format = "%Y")
    {
        $parts = explode('-', $date);
        $date  = $parts[0] . '-' . (intval($parts[1]) ? $parts[1] : '01') . '-' . (intval(
                $parts[2]
            ) ? $parts[2] : '01');

    return CMbDT::strftime($format, strtotime($date));
  }

    /**
     * Week number in month to ISO DATETIME
     * Example: {$datetime|week_number_month}
     *
     * @param string $datetime The date to format
     *
     * @return string
     */
    function week_number_month($datetime)
    {
        return CMbDT::weekNumberInMonth($datetime);
    }

    /**
     * Configuration accessor
     *
     * @param string $path    The configuration path
     * @param object $context The context
     *
     * @return string
     */
    function conf($path, $context = null)
    {
        return CAppUI::conf($path, $context);
    }

    /**
     * CGroups configuration accessor
     *
     * @param string       $path     Configuration path
     * @param integer|null $group_id CGroups ID
     *
     * @return string
     */
    function gconf($path, $group_id = null)
    {
        return CAppUI::gconf($path, $group_id);
    }

    /**
     * Idex loader and accessor
     *
     * @param CMbObject $object The configuration path
     * @param string    $tag    The context
     *
     * @return string The idex scalar value, empty string if undefined
     */
    function idex($object, $tag = null)
    {
        return $object->loadLastId400($tag)->id400;
    }

    /**
     * Format to relative datetime
     * Example: {$datetime|rel_datetime:$now}
     *
     * @param string $datetime  The date to format
     * @param string $reference Reference datetime
     *
     * @return null|string
     */
    function rel_datetime($datetime, $reference = null)
    {
        if (!$datetime) {
            return null;
        }
        $relative = CMbDT::relativeDuration(CMbDT::dateTime($reference), $datetime);

        return $relative["locale"];
    }

    /**
     * Truncate a string, with a full string titled span if actually truncated
     * Example:  {$value|spancate}
     *
     * @param string $string      The string to truncate
     * @param int    $length      The maximum string length
     * @param string $etc         The ellipsis
     * @param bool   $break_words Break words
     * @param bool   $middle      Put the ellipsis at the middle of the string instead of at the end
     *
     * @return string
     */
    function spancate($string, $length = 80, $etc = '...', $break_words = true, $middle = false)
    {
        require_once $this->_get_plugin_filepath('modifier', 'truncate');

        $string    = html_entity_decode($string, ENT_QUOTES, CApp::$encoding);
        $truncated = smarty_modifier_truncate($string, $length, $etc, $break_words, $middle);
        $truncated = CMbString::nl2bull($truncated);
        $string    = CMbString::htmlEntities($string);

        return strlen($string) > $length ? "<span title=\"$string\">$truncated</span>" : $truncated;
    }

    /**
     * Formats a value as a float
     * Example: {$value|float:2}
     *
     * @param float $value    The value to format
     * @param int   $decimals Number of decimal digits
     *
     * @return string
     */
    function float($value, $decimals = 0)
    {
        return number_format($value, $decimals, $dec_point = ',', $thousands_sep = ' ');
    }

  /**
   * Formats a value as an integer
   * Example: {$value|integer}
   *
   * @param int $value The value to format
   *
   * @return string
   */
  function integer($value) {
    return number_format($value ?? 0, 0, $dec_point = ',', $thousands_sep = ' ');
  }

    /**
     * Converts a value to decabinary format
     * Example: {$value|decabinary}
     *
     * @param float $value The value to format
     *
     * @return string
     */
    function decabinary($value)
    {
        $decabinary = CMbString::toDecaBinary($value);

        return "<span title=\"{$this->integer($value)}\">$decabinary</span>";
    }

    /**
     * Converts a value to decabinary SI format
     * Example: {$value|decabinary}
     *
     * @param float  $value The value to format
     * @param string $unit  Unit
     *
     * @return string
     */
    function decaSI($value, $unit = "o")
    {
        $decabinary = CMbString::toDecaSI($value, $unit);

        return "<span title=\"{$this->integer($value)}\">$decabinary</span>";
    }

    /**
     * Converts a value to decabinary format
     * Example:  {$value|decabinary}
     *
     * @param float $value The value to format
     *
     * @return string
     */
    function nozero($value)
    {
        return $value ? $value : '';
    }

    /**
     * Create a link to open the file in an IDE
     *
     * @param string $file File to open in the IDE
     * @param int    $line Line number
     * @param string $text Text in the link
     *
     * @return string
     */
    function ide($file, $line = null, $text = null)
    {
        $text = isset($text) ? $text : $file;
        $url  = null;

        $ide_url = CAppUI::conf("dPdeveloppement ide_url");
        if ($ide_url) {
            $url = str_replace("%file%", urlencode($file), $ide_url) . ":$line";
        } else {
            $ide_path = CAppUI::conf("dPdeveloppement ide_path");
            if ($ide_path) {
                $url = "ide:" . urlencode($file) . ":$line";
            }
        }

        if ($url) {
            return "<a target=\"ide-launch-iframe\" href=\"$url\">$text</a>";
        }

        return $text;
    }

    /**
     * Percentage 2-digit format modifier
     * Example:  {$value|percent}
     *
     * @param float $value The value to format
     *
     * @return string
     */
    function percent($value)
    {
        return !is_null($value) ? number_format($value * 100, 2) . "%" : "";
    }

    /**
     * Emit a WARNING if given classname if not in a namespace
     *
     * @param string $class
     *
     * @return void
     */
    private function checkNamespacedClass(string $class): void
    {
        if (strpos($class, '\\') === false) {
            trigger_error("Only namespaced classes must be called statically : {$class}", E_USER_WARNING);
        }
    }

    /**
     * Class constant accessor
     *
     * @param object|string $object The object or the class to get the constant from
     * @param string        $name   The constant name
     *
     * @return mixed
     */
    function _const($object, $name)
    {
        if (is_string($object)) {
            $this->checkNamespacedClass($object);
        } else {
            $object = get_class($object);
        }

        return constant("$object::$name");
    }

    /**
     * Static property accessor
     *
     * @param object|string $object The object or the class to get the static property from
     * @param string        $name   The static property name
     *
     * @return mixed
     * @throws Exception
     */
    function _static($object, $name)
    {
        if (is_string($object)) {
            $this->checkNamespacedClass($object);
        } else {
            $object = get_class($object);
        }

        $class   = new ReflectionClass($object);
        $statics = $class->getStaticProperties();
        if (!array_key_exists($name, $statics)) {
            trigger_error("Static variable '$name' for class '$class->name' does not exist", E_USER_WARNING);

            return null;
        }

        return $statics[$name];
    }

    /**
     * Static call from Smarty
     *
     * @param string $callback The callback
     * @param array  $args     Array of arguments
     *
     * @return mixed
     */
    function static_call($callback, $args)
    {
        $args     = func_get_args();
        $callback = array_shift($args);
        $callback = explode("::", $callback);

        $classname = $callback[0];

        $this->checkNamespacedClass($classname);

        return call_user_func_array($callback, $args);
    }

    /**
     * Static call from Smarty
     *
     * @param string $object The object
     * @param array  $args   Array of arguments
     *
     * @return mixed
     */

    /**
     * Tell if the given object is an instance of given class
     *
     * @param object $object Object to test
     * @param string $class  Class name
     *
     * @return bool
     */
    function _instanceof($object, $class)
    {
        if (!class_exists($class) && !interface_exists($class)) {
            trigger_error(CAppUI::tr("common-error-%s class does not exist", $class), E_USER_ERROR);
        }

        return ($object instanceof $class);
    }

    /**
     * Compare value to ascending thresholds to map to another value
     * No mapping occurs if all tests fail
     * eg {{15|threashold:0:blue:10:red:20:green}} will map to red
     *
     * @param string $value The value to compare
     * @param array  $pairs Array of threshold-value pairs, threshold being ascendingly sorted
     *
     * @return mixed
     *
     * @see also self::map();
     */
    function threshold($value, $pairs)
    {
        $pairs = func_get_args();
        array_shift($pairs);
        $result = $value;
        while (!empty($pairs)) {
            $_threshold = array_shift($pairs);
            $_value     = array_shift($pairs);
            if ($value >= $_threshold) {
                $result = $_value;
            }
        }

        return $result;
    }

    /**
     * Compare value to keys to map to another value
     * No mapping occurs if all tests fail
     * eg {{10|threashold:0:blue:10:red:20:green}} will map to red
     *
     * @param string $value The value to compare
     * @param array  $pairs Array of key-value pairs
     *
     * @return mixed
     *
     * @see also self::threshold();
     */
    function map($value, $pairs)
    {
        $pairs = func_get_args();
        array_shift($pairs);
        $result = $value;
        while (!empty($pairs)) {
            $_key   = array_shift($pairs);
            $_value = array_shift($pairs);
            if ($value == $_key) {
                $result = $_value;
            }
        }

        return $result;
    }

    /**
     * True if the module is installed
     * Example:  {"dPfiles"|module_installed}
     *
     * @param string $module The module name
     *
     * @return CModule The module object if installed, null otherwise
     */
    function module_installed($module)
    {
        return CModule::getInstalled($module);
    }

    /**
     * True if the module is active
     * Example: {"dPfiles"|module_active}
     *
     * @param string $module The module name
     *
     * @return CModule The module object if active, null otherwise
     */
    function module_active($module)
    {
        return CModule::getActive($module);
    }

    /**
     * True if the module is visible
     * Example: {"dPfiles"|module_visible}
     *
     * @param string $module The module name
     *
     * @return CModule The module object if visible, null otherwise
     */
    function module_visible($module)
    {
        return CModule::getVisible($module);
    }

    /**
     * Escape a JavaScript code to be used inside DOM attributes
     *
     * @param string $string The string to escape
     *
     * @return string The escaped string
     */
    function JSAttribute($string)
    {
        return str_replace(
            ['\\', "'", '"', "\r", "\n", '</'],
            ['\\\\', "\\'", '&quot;', '\\r', '\\n', '<\/'],
            //array('\\',   "'",   '"',      "\r",  "\n",  '<',    '>'),
            //array('\\\\', "\\'", '&quot;', '\\r', '\\n', '&lt;', '&gt;'),
            $string
        );
    }

    /**
     * The default Smarty escape
     *
     * @param string $string The string to escape
     *
     * @return string Escaped string
     */
    function cleanField($string)
    {
        if (!is_scalar($string)) {
            return $string;
        }

        return CMbString::htmlSpecialChars($string, ENT_QUOTES);
    }

    /**
     * Strip slashes
     *
     * @param string $string Strip slashes
     *
     * @return string Unescaped string
     */
    function stripslashes($string)
    {
        return stripslashes($string);
    }

    /**
     * Emphasize a text, putting <em> nodes around found tokens
     * Example:  {$text|emphasize:$tokens}
     *
     * @param string       $text   The text subject
     * @param array|string $tokens The string tokens to emphasize, space seperated if string
     * @param string       $tag    The HTML tag to use to emphasize
     */
    public function emphasize($text, $tokens, $tag = "em"): string
    {
        if (!is_array($tokens)) {
            $tokens = explode(" ", $tokens);
        }
        CMbArray::removeValue("", $tokens);

        if (count($tokens) == 0) {
            return $text;
        }

        foreach ($tokens as &$token) {
            $token = preg_quote($token);
            $token = CMbString::allowDiacriticsInRegexp($token);
        }

        $regexp = str_replace("/", "\\/", implode("|", $tokens));

        return preg_replace("/($regexp)/i", "<$tag>$1</$tag>", $text);
    }

    /**
     * Smarty ireplace, case insensitive str_ireplace wrapper
     *
     * @param string $str    text
     * @param string $value1 search value
     * @param string $value2 replace value
     *
     * @return string
     */
    function ireplace($str, $value1, $value2)
    {
        return str_ireplace($value1, $value2, $str);
    }

    /**
     * A ternary operator
     *
     * @param object $value   The condition
     * @param object $option1 the value if the condition evaluates to true
     * @param object $option2 the value if the condition evaluates to false
     *
     * @return object $option1 or $option2
     */
    function ternary($value, $option1, $option2)
    {
        return $value ? $option1 : $option2;
    }

    /**
     * Trace modifier
     *
     * @param object $value The value to mbExport
     *
     * @return void
     * @deprecated
     *
     */
    function trace($value)
    {
        CApp::log(($value instanceof CModelObject) ? $value->getProperties() : $value);
    }

    /**
     * Insert an hidden input corresponding to the object's primary key
     * Cette fonction prend les mêmes paramètres que mb_field, mais seul object est requis .
     *
     * @param array $params Smarty parameters
     * @param self  $smarty The Smarty object
     *
     * @return string
     */
    function mb_key($params, &$smarty)
    {
        $params['field']  = $params["object"]->_spec->key;
        $params['prop']   = 'ref';
        $params['hidden'] = true;

        return $this->mb_field($params, $smarty);
    }

    /**
     * Javascript HTML inclusion
     *
     * @param array $params Smarty params
     *                      * path   : Direct script file path with extension
     *                      * script : Script name, without extension, supersedes 'path' and depends on 'module'
     *                      * module : Module name to find script, if not provided, use global includes
     * @param self  $smarty The Smarty object
     */
    public function mb_script($params, &$smarty): string
    {
        // Path provided
        $path     = CMbArray::extract($params, "path");
        $ajax     = CMbArray::extract($params, "ajax");
        $register = CMbArray::extract($params, "register");

        // Désactivation du register
        if ($register) {
            $ajax     = true;
            $register = false;
        }

        // Script name providied
        if ($script = CMbArray::extract($params, "script")) {
            if ($module = CMbArray::extract($params, "module")) {
                $module = CModule::prefixModuleName($module);
            }

            $prefix = CMbArray::extract($params, "mobile") ? "mobile/" : "";
            $dir    = $module ? $prefix . "modules/$module/javascript" : "includes/javascript";
            $path   = "$dir/$script.js";
        }

        // Render HTML with build version
        if ($ajax && !empty($smarty->_tpl_vars["ajax"])) {
            $script = file_get_contents($path);

            return "<script type=\"text/javascript\">$script</script>";
        } elseif ($register) {
            return "<script>Main.registerDependency({module: '{$module}', script: '{$script}'});</script>";
        } else {
            $version_build = CApp::getVersion()->getKey();

            return "<script type=\"text/javascript\" src=\"$path?build=$version_build\"></script>";
        }
    }

    function mb_script_register_end($params, &$smarty)
    {
        return "<script>Main.loadDependencies()</script>";
    }

    /**
     * VueJS compiled file inclusion
     *
     * @param array $params Smarty params
     *                      * script : Entry Point file name, without extension
     *                      * module : Module name to find Vue entry point, if not provided, use global includes
     */
    public function mb_vue($params)
    {
        $module = CMbArray::extract($params, "module");
        $script = CMbArray::extract($params, "script");
        $path   = $module ? "modules/$module/vue/_src/dist/$script-dist.js" : "javascript/dist/js/$script.js";

        return "<script type='text/javascript'>Main.add(function() {App.loadJS('$path');})</script>";
    }

    /**
     * Build a div that represent the entry point for vue.
     *
     * Each $entry->data is converted into an attribute using their key :vue-XXX.
     * $entry->meta is converted in a single attribute :vue-meta if present.
     * $entry->prefs is converted in a single attribute :vue-prefs if present.
     * $entry->configs is converted in a single attribute :vue-configs if present.
     * $entry->links is converted in a single attribute :vue-links if present.
     * $entry->locales is converted in a single attribute :vue-locales if present.
     */
    public function mb_entry_point(array $params): string
    {
        /** @var EntryPoint $entry_point */
        $entry_point = CMbArray::get($params, 'entry_point');

        if (!$entry_point instanceof EntryPoint) {
            throw new CMbException('CSmartyMB-Error-Object must be an EntryPoint');
        }

        $html = '';
        if ($script = $entry_point->getScriptName()) {
            $html = $this->mb_vue(['script' => $script]);
        }

        $html .= sprintf('<div id="%s"', $entry_point->getId());

        if ($data = $entry_point->getData()) {
            foreach ($data as $key => $datum) {
                $html .= sprintf(
                    " %svue-%s='%s'",
                    is_string($datum) || $datum === null ? '' : ':',
                    $key,
                    is_string($datum) || $datum === null  ? $datum : json_encode($datum, JSON_HEX_APOS | JSON_HEX_QUOT)
                );
            }
        }

        if ($links = $entry_point->getLinks()) {
            $html .= sprintf(" :vue-links='%s'", json_encode($links, JSON_HEX_APOS | JSON_HEX_QUOT));
        }

        if ($configs = $entry_point->getConfigs()) {
            $html .= sprintf(" :vue-configs='%s'", json_encode($configs, JSON_HEX_APOS | JSON_HEX_QUOT));
        }

        if ($prefs = $entry_point->getPrefs()) {
            $html .= sprintf(" :vue-prefs='%s'", json_encode($prefs, JSON_HEX_APOS | JSON_HEX_QUOT));
        }

        if ($meta = $entry_point->getMeta()) {
            $html .= sprintf(" :vue-meta='%s'", json_encode($meta, JSON_HEX_APOS | JSON_HEX_QUOT));
        }

        if ($locales = $entry_point->getLocales()) {
            $html .= sprintf(" :vue-locales='%s'", json_encode($locales, JSON_HEX_APOS | JSON_HEX_QUOT));
        }

        $html .= "></div>";

        return $html;
    }

    /**
     * Module/Style aware include alternative
     *
     * @param array $params Smarty params
     *                      * module    : Module where template is located, no dP ugly prefix required
     *                      * style     : Style where template is located
     *                      * $template : Template name (no extension)
     * @param self  $smarty The Smarty object
     *
     * @return void
     */
    public function mb_include($params, &$smarty): void
    {
        $template      = CMbArray::extract($params, "template");
        $ignore_errors = CMbArray::extract($params, "ignore_errors");

        // Module précisé
        if ($module = CMbArray::extract($params, "module")) {
            $module = CModule::prefixModuleName($module);

            $template = "../../../modules/$module/templates/$template";
        }

        // Style précisé
        if ($style = CMbArray::extract($params, "style")) {
            $template = "../../../style/$style/templates/$template";
        }

        // Use defined extension or add tpl if no extension
        $path = pathinfo($template, PATHINFO_EXTENSION) ? $template : "$template.tpl";

        if ($ignore_errors) {
            $resource_base_path = ($params['resource_base_path']) ?? $this->template_dir;

            if (!file_exists($resource_base_path . $path)) {
                return;
            }
        }

        $tpl_vars = $smarty->_tpl_vars;
        $smarty->_smarty_include(
            [
                'smarty_include_tpl_file' => $path,
                'smarty_include_vars'     => $params,
            ]
        );
        $smarty->_tpl_vars = $tpl_vars;
    }

    /**
     * @param string      $tpl
     * @param string|null $module
     *
     * @return bool
     */
    public function tpl_exist(string $tpl, ?string $module): bool
    {
        if ($module) {
            $module = CModule::prefixModuleName($module);
            $tpl    = "../../../modules/$module/templates/$tpl";
        }

        // Use defined extension or add tpl if no extension
        $path = pathinfo($tpl, PATHINFO_EXTENSION) ? $tpl : "$tpl.tpl";

        $resource_base_path = $this->template_dir;

        return file_exists($resource_base_path . $path);
    }

    /**
     * Include buttons from the plugin system
     *
     * @param array $params Smarty parameters
     * @param self  $smarty The Smarty object
     *
     * @return void
     * @throws Exception
     */
    public function mb_include_buttons($params, &$smarty): void
    {
        $location = CMbArray::extract($params, 'location', null, true);

        if ($location === null) {
            return;
        }

        $vars = [];
        foreach ($params as $key => $value) {
            if (preg_match('/^var\d+/', $key)) {
                $vars[] = $value;
            }
        }

        $plugin_manager = ButtonPluginManager::get();
        $buttons        = $plugin_manager->getButtonsForLocation($location, ...$vars);

        $params['module']   = 'system';
        $params['template'] = 'include_buttons_for_location';
        $params['buttons']  = $buttons;

        $this->mb_include($params, $smarty);
    }


    /**
     * Assigns a unique id to a variable
     *
     * @param array $params Smarty params
     *                      - var: Name of the var
     * @param self  $smarty The Smarty object
     *
     * @return void
     */
    function unique_id($params, &$smarty)
    {
        $var     = CMbArray::extract($params, "var", null, true);
        $numeric = CMbArray::extract($params, "numeric", null);

        if ($numeric) {
            $smarty->assign($var, rand(0, (int)pow(2, 31)));

            return;
        }

        // The dot is removed to get valide CSS ID identifiers
        $smarty->assign($var, str_replace(".", "", uniqid("", true)));
    }

    /**
     * Get application version key
     *
     * @return string
     */
    function app_version_key()
    {
        return CApp::getVersion()->getKey();
    }

    /**
     * Get the first element from an arry
     *
     * @param array $array The array to get the first element from
     *
     * @return mixed
     */
    function first($array)
    {
        if (!is_array($array)) {
            $this->trigger_error("The variable is not a an array");
        }

        return reset($array);
    }

    /**
     * Get the last element from an arry
     *
     * @param array $array The array to get the last element from
     *
     * @return mixed
     */
    function last($array)
    {
        if (!is_array($array)) {
            $this->trigger_error("The variable is not a an array");
        }

        return end($array);
    }

    /**
     * Highlight the code using the choosen language
     *
     * @param string $code     The code to highlight
     * @param string $language The language
     * @param string $class    CSS class
     * @param string $style    CSS style
     *
     * @return mixed
     */
    function highlight($code, $language, $class = null, $style = null)
    {
        return CMbString::highlightCode($language, $code, $class, $style, false);
    }

    /**
     * Executes & displays the template results
     *
     * @param string $resource_name Resource name
     * @param string $cache_id      Cache identifier
     * @param string $compile_id    Compile identifier
     *
     * @return void
     */
    function display($resource_name, $cache_id = null, $compile_id = null)
    {
        $matches = [];
        if (!preg_match('/\.tpl/', $resource_name, $matches)) {
            $resource_name = "{$resource_name}.tpl";
        }


        $base_tpl = [
            "login.tpl",
            "common.tpl",
            "header.tpl",
            "footer.tpl",
            "tabbox.tpl",
            "ajax_errors.tpl",
            "inc_unlocalized_strings.tpl",
        ];
        if (isset($this->_tpl_vars['nodebug'])
            || CDevtools::isActive() !== true
            || in_array(basename($resource_name), $base_tpl)
        ) {
            parent::display($resource_name, $cache_id, $compile_id);

            return;
        }

        // Only at debug time
        echo "\n<!-- Start display: $resource_name -->\n";
        parent::display($resource_name, $cache_id, $compile_id);
        echo "\n<!-- Stop display: $resource_name -->\n";
    }

    /**
     * @inheritDoc
     */
    function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false)
    {
        if (!preg_match('/\.tpl/', $resource_name)) {
            $resource_name = "{$resource_name}.tpl";
        }

        return parent::fetch($resource_name, $cache_id, $compile_id, $display);
    }


    /**
     * Create a <img> tag with a thumbnail for source
     *
     * @param array $params Params to pass to the function (one of (document_id + document_class) or file_id or
     *                      document is required):
     *                      - profile(required)     : Profile to use to get the size of the thumb (enum
     *                      small|medium|large default|medium)
     *                      - document_id(required) : file_id or compte_rendu_id to get thumb from
     *                      - document_class        : Class of the CDocumentItem (enum CFile|CCompteRendu
     *                      default|CFile)
     *                      - file_id               : ID of the file to get a thumb of
     *                      - document              : CFile|CCompteRendu to get thumb of
     *                      - page                  : Page of the file to get thumb of
     *                      - rotation              : Rotation of the thumb (enum 0|90|180|270)
     *                      - crop                  : Crop the thumbnail
     *                      - quality               : Quality of the displayed thumb (1->100)
     *                      - default_size          : use the default style for the thumb size
     *                      - style                 : Style to add to <img>
     *                      - extra                 : Any xml attributes to add to <img>. Fields like data-* must be
     *                      data_*
     *
     * @return void
     */
    public function thumbnail($params): void
    {
        $profile        = CMbArray::extract($params, 'profile');
        $document_id    = CMbArray::extract($params, 'document_id');
        $document_class = CMbArray::extract($params, 'document_class');
        $file_id        = CMbArray::extract($params, 'file_id');
        $document       = CMbArray::extract($params, 'document');
        $page           = CMbArray::extract($params, 'page');
        $rotate         = CMbArray::extract($params, 'rotate');
        $crop           = CMbArray::extract($params, 'crop');
        $quality        = CMbArray::extract($params, 'quality');
        $default_size   = CMbArray::extract($params, 'default_size', 0);
        $style          = CMbArray::extract($params, 'style');
        $class          = CMbArray::extract($params, 'class');

        $datas = [];
        foreach ($params as $_key => $_value) {
            $regexp = '/data\_([a-zA-Z0-9]+)/';
            if (preg_match($regexp, $_key, $matches)) {
                $datas["data-$matches[1]"] = $_value;
                unset($params[$_key]);
            }
        }

        $extra = CMbArray::makeXmlAttributes($params) . CMbArray::makeXmlAttributes($datas);

        if ($file_id) {
            $document_id    = $file_id;
            $document_class = 'CFile';
        }

        if ($document) {
            $document_class = $document->_class;
            $document_id    = $document->_id;
        }

        // Check the last modification time
        $file = null;
        if ($document_class == 'CCompteRendu') {
            $cr = new CCompteRendu();
            $cr->load($document_id);
            if ($cr && $cr->_id && $cr->getPerm(CPermModule::READ)) {
                $cr->makePDFpreview();
                $file = $cr->_ref_file;
            }
        } else {
            $file = new CFile();
            $file->load($document_id);
        }

        if (!$file || !$file->_id || !file_exists($file->_file_path)) {
            $_ts = 0;
        } else {
            // Add rotation to the _ts to force requesting image in case of change
            $_ts = filemtime($file->_file_path) . $file->rotation;
        }

        // Display a thumbnail (create it if necessary)
        if ($document_class == 'medifile') {
            $balise = "<img src='images/pictures/medifile.png'";
        } else {
            // Create the <img> tag for the thumbnail
            $balise = "<img src='?m=dPfiles&amp;raw=thumbnail&amp;document_guid=$document_class-$document_id" .
                "&amp;profile=$profile&amp;_ts=$_ts";

            if ($page) {
                $balise .= "&amp;page=$page";
            }
            if ($crop) {
                $balise .= "&amp;crop=$crop";
            }
            if ($quality !== null) {
                $balise .= "&amp;quality=$quality";
            }
            if ($rotate != 0) {
                $balise .= "&amp;rotate=$rotate";
            }
        }

        $profile_data = CMbArray::get(CThumbnail::PROFILES, $profile, CThumbnail::PROFILE_MEDIUM);
        // Get the width and height to display
        $width  = $profile_data[CThumbnail::DISPLAY_WIDTH];
        $height = $profile_data[CThumbnail::DISPLAY_HEIGHT];

        $balise .= "' style='background-color: white;";
        if ($default_size) {
            $balise .= "max-height:{$height}px; max-width:{$width}px; height: auto; width: auto;";
        }

        $balise .= " $style'";

        $balise .= " class='me-thumbnail $class'";

        $balise .= " $extra />";

        echo $balise;
    }

    /**
     * Create a <a> link around $content to download a CDocumentItem
     *
     * @param array  $params  Params to pass to the function :
     *                        - document_id    : CDocumentItem id to download
     *                        - document_class : Class of the document (CFile|CCompteRendu)
     *                        - document       : CDocumentItem object to download (if this field is not null,
     *                        document_id and document_item won't be used)
     *                        - extra          : Any HTML attribute to add to the tag
     * @param string $content The content to put between <a> and </a>
     *
     * @return void
     */
    function thumblink($params, $content = null)
    {
        if (!is_null($content)) {
            $document_id    = CMbArray::extract($params, 'document_id');
            $document_class = CMbArray::extract($params, 'document_class', 'CFile');
            $document       = CMbArray::extract($params, 'document');
            $download_raw   = CMbArray::extract($params, 'download_raw', 0);
            $page           = CMbArray::extract($params, 'page');
            $length         = CMbArray::extract($params, 'length', 1);

            $extra = CMbArray::makeXmlAttributes($params);

            if ($document) {
                $document_class = $document->_class;
                $document_id    = $document->_id;
            } else {
                /** @var CDocumentItem $document */
                $document = new $document_class;
                $document->load($document_id);
            }

            $balise = "<a href='?m=dPfiles&raw=thumbnail&document_id=$document_id&document_class=$document_class&thumb=0"
                . "&download_raw=$download_raw";

            if ($page && $page > 0) {
                $balise .= "&page=$page&length=$length";
            }

            $balise .= "' target='_blank' $extra>";
            $balise .= $content . '</a>';

            echo $balise;
        }
    }

    /**
     * @param string|object $class Class name or object to get shortname for
     *
     * @return string
     * @throws Exception
     */
    function getShortName($class)
    {
        return CClassMap::getInstance()->getShortName($class);
    }
}
