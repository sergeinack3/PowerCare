<?php
/**
 * @package Mediboard\Core\ResourceLoaders
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\ResourceLoaders;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Ox\Core\Module\CModule;
use Ox\Mediboard\System\CTranslationOverwrite;

/**
 * Utility class to handle Javascript loading in an HTML document
 */
abstract class CJSLoader extends CHTMLResourceLoader
{
    public static $files = [];

    public static $additionnal_files = [];

    /**
     * Creates an HTML script tag to load a Javascript file
     *
     * @param string $file  The Javascript file name
     * @param string $cc    An IE conditional comment
     * @param string $build A build number
     * @param string $type  The mime type to put in the HTML tag
     *
     * @return string The HTML script tag
     */
    static function loadFile($file, $cc = null, $build = null, $type = "text/javascript")
    {
        $tag = self::getTag(
            "script",
            [
                "type" => $type ? $type : "text/javascript",
                "src"  => "$file?" . self::getBuild($build),
            ],
            null,
            false
        );

        return self::conditionalComments($tag, $cc);
    }

    /**
     * register & load all files
     * @return string
     */
    static function loadAllFiles($with_fallback = true)
    {
        self::registerFiles();

        if ($with_fallback && CAppUI::pref('FALLBACK_LOCALE') && CAppUI::pref('FALLBACK_LOCALE') != CAppUI::pref('LOCALE')) {
            self::$files[] = self::getLocaleFile(null, null, 'FALLBACK_LOCALE');
        }

        return self::loadFiles();
    }

    /**
     * Loads a list of Javascript files, with or without minification
     *
     * @param string $type The mime type to use to include the Javascript files
     *
     * @return string A list or a single HTML script tag
     */
    static function loadFiles($type = "text/javascript")
    {
        $result   = "";
        $compress = CAppUI::conf("minify_javascript");

        $last_update = null;

        /**
         * There is a speed boost on the page load when using concatenation in a single file
         * between the top of the head and the dom:loaded event of about 25%.
         * This is because of parse time that is reduced (compare the global __pageLoad variable)
         * The number of requests from a regular page goes down from 100 to 70.
         * The total size of the JS goes down from 300kB to 230kB (gzipped).
         */
        if ($compress) {
            $files    = self::$files;
            $excluded = [];
            $uptodate = false;

            // We exclude files already in the tmp dir
            foreach ($files as $index => $file) {
                if (strpos($file, "tmp/") === 0) {
                    $excluded[] = $file;
                    unset($files[$index]);
                }
            }

            $hash      = self::getHash(implode("", $files));
            $cachefile = "tmp/$hash.js";

            // If it exists, we check if it is up to date
            if (file_exists($cachefile)) {
                $uptodate    = true;
                $last_update = self::getLastChange($cachefile);

                foreach ($files as $file) {
                    if (self::getLastChange($file) > $last_update) {
                        $uptodate = false;
                        break;
                    }
                }
            }

            if (!$uptodate) {
                $all_scripts = "";
                foreach ($files as $file) {
                    $_script = file_get_contents($file);
                    if (strpos($_script, chr(0xEF) . chr(0xBB) . chr(0xBF)) === 0) {
                        $_script = substr($_script, 3);
                    }
                    $all_scripts .= $_script . "\n";
                }

                file_put_contents($cachefile, $all_scripts);
                $last_update = time();
            }

            foreach ($excluded as $file) {
                $result .= self::loadFile($file, null, self::getLastChange($file), $type) . "\n";
            }

            $result .= self::loadFile($cachefile, null, $last_update, $type) . "\n";
        } else {
            foreach (self::$files as $file) {
                $result .= self::loadFile($file, null, self::getLastChange($file), $type) . "\n";
            }
        }

        return $result;
    }

    /**
     * Writes a locale file
     *
     * @param string $language The language code (fr, en, ...)
     * @param array  $locales  The locales
     * @param string $label    A code to istinguish different locales listes
     *
     * @return void
     */
    static function writeLocaleFile($language, $locales = [], $label = null, $type = 'LOCALE')
    {
        if (!$locales) {
            $localeFiles = array_merge(
                glob("./locales/$language/*.php"),
                glob("./modules/*/locales/$language.php"),
                glob("./mobile/modules/*/locales/$language.php")
            );

            foreach ($localeFiles as $localeFile) {
                if (basename($localeFile) !== "meta.php") {
                    include $localeFile;
                }
            }
        }

        // Updating the current locales with the overwritten locales
        $overwrite = new CTranslationOverwrite();
        if ($overwrite->isInstalled()) {
          $locales = $overwrite->transformLocales($locales, $language);
        }

        $path =
            ($type == 'FALLBACK_LOCALE') ? self::getFallbackLocaleFilePath($language, $label) : self::getLocaleFilePath(
                $language,
                $label
            );

        if ($fp = fopen($path, 'w')) {
            $locales = is_array($locales) ? $locales : [];
            $locales = CMbString::filterEmpty($locales);
            // TODO: change the invalid keys (with accents) of the locales to simplify this
            $keys   = array_map('utf8_encode', array_keys($locales));
            $values = array_map('utf8_encode', array_values($locales));

            foreach ($values as &$_value) {
                $_value = CMbString::unslash($_value);
            }

            $compress = false;

            if ($compress) {
                $delim = "/([\.-])/";
                $arr   = new \stdClass;
                foreach ($keys as $_pos => $_key) {
                    $parts = preg_split($delim, $_key, -1, PREG_SPLIT_DELIM_CAPTURE);

                    $_arr     = $arr;
                    $last_key = count($parts) - 1;

                    foreach ($parts as $i => $_token) {
                        $last = ($i == $last_key);
                        if ($_token === "") {
                            $_token = '_$_';
                        }

                        if ($last) {
                            $_arr->{$_token} = (object)['$' => $values[$_pos]];
                            break;
                        } elseif (!isset($_arr->{$_token})) {
                            $_arr->{$_token} = new \stdClass;
                        }

                        $_arr = $_arr->{$_token};
                    }
                    //unset($_arr);
                }

                self::clearLocalesKeys($arr);
                $json = $arr;
            } else {
                $json = array_combine($keys, $values);

                // Add the translations overwrite for the keys
                $trans = new CTranslationOverwrite();

                if ($trans->isInstalled()) {
                    $trans->language = $language;
                    $translations    = $trans->loadMatchingListEsc();

                    /** @var CTranslationOverwrite $_trans */
                    foreach ($translations as $_trans) {
                        $json[utf8_encode($_trans->source)] = utf8_encode($_trans->translation);
                    }
                }
            }

            $var_name = 'locales';
            if ($type == 'FALLBACK_LOCALE') {
                $var_name = 'fallback_locales';
            }

            $script = '//' . (CApp::getVersion()->getBuild()) . "\nwindow.$var_name=" . json_encode($json) . ";";

            fwrite($fp, $script);
            fclose($fp);
        }
    }

    /**
     * Recursive function to reduce locales keys
     *
     * @param object $object An array of locales
     *
     * @return void
     */
    static function clearLocalesKeys($object)
    {
        foreach ($object as $key => &$value) {
            if (!is_object($value)) {
                continue;
            }

            $keys = get_object_vars($value);

            if (count($keys) === 1 && isset($keys['$'])) {
                $object->$key = $keys['$'];
            } else {
                self::clearLocalesKeys($object->$key);
            }
        }
    }

    /**
     * Creates a JSON locales file
     *
     * @param array  $locales The locales array
     * @param string $label   The locales label
     * @param string $type    Type of locales to get : LOCALE or FALLBACK_LOCALE
     *
     * @return string The path to the JSON locales file
     */
    static function getLocaleFile($locales = null, $label = null, $type = "LOCALE")
    {
        $language = CAppUI::pref($type);

        if ($type == 'FALLBACK_LOCALE') {
            $path = self::getFallbackLocaleFilePath($language, $label);
        } else {
            $path = self::getLocaleFilePath($language, $label);
        }

        if (!is_file($path)) {
            self::writeLocaleFile($language, $locales, $label, $type);
        }

        return $path;
    }

    /**
     * Returns the JSON locales file path
     *
     * @param string $language The language code (fr, en, ...)
     * @param string $label    The locales label
     *
     * @return string The JSON file path
     */
    static function getLocaleFilePath($language, $label = null)
    {
        return "tmp/locales" . ($label ? ".$label" : "") . "-$language.js";
    }

    static function getFallbackLocaleFilePath($language, $label = null)
    {
        return "tmp/fb_locales" . ($label ? ".$label" : "") . "-$language.js";
    }


    public static function registerFiles()
    {
        self::$files = self::getCommonFiles();

        self::$files[] = self::getLocaleFile();

        // Vue, Webpack
        $app_dist = "javascript/dist/js/appbar.js";
        if (file_exists($app_dist)) {
            CJSLoader::$files[] = $app_dist;
        }
        $runtime = "javascript/dist/js/runtime.js";
        if (file_exists($runtime)) {
            CJSLoader::$files[] = $runtime;
        }
        $chunks = "javascript/dist/js/chunk-vendors.js";
        if (file_exists($chunks)) {
            CJSLoader::$files[] = $chunks;
        }
        $common = "javascript/dist/js/chunk-common.js";
        if (file_exists($common)) {
            CJSLoader::$files[] = $common;
        }

        $support = "modules/support/javascript/support.js";
        if (file_exists($support) && CModule::getActive("support")) {
            self::$files[] = $support;
        }

        $erp = 'modules/oxERP/javascript/ox.erp.js';
        if (file_exists($erp) && CModule::getActive('oxERP')) {
            self::$files[] = $erp;
        }

        $monitor_client = 'modules/monitorClient/javascript/monitor.client.js';
        if (file_exists($monitor_client) && CModule::getActive('monitorClient')) {
            self::$files[] = $monitor_client;
        }

        if (self::$additionnal_files) {
            self::$files = array_merge(self::$files, self::$additionnal_files);
        }
    }

    public static function getCommonFiles()
    {
        return [
            // User timing
            "includes/javascript/vendor/usertiming.js",
            "includes/javascript/performance.js",

            "includes/javascript/vendor/printf.js",
            "includes/javascript/vendor/stacktrace.js",
            //"lib/dshistory/dshistory.js",

            "style/mediboard_ext/javascript/prototype_fork.js",
            "lib/scriptaculous/src/scriptaculous.js",

            "includes/javascript/console.js",

            // We force the download of the dependencies
            "lib/scriptaculous/src/builder.js",
            "lib/scriptaculous/src/effects.js",
            "lib/scriptaculous/src/dragdrop.js",
            "lib/scriptaculous/src/controls.js",
            "lib/scriptaculous/src/slider.js",
            "lib/scriptaculous/src/sound.js",

            "includes/javascript/prototypex.js",

            // Datepicker
            "includes/javascript/date.js",
            "lib/datepicker/datepicker.js",
            "lib/datepicker/datepicker-locale-fr_FR.js",
            "lib/datepicker/datepicker-locale-de_DE.js",

            // Livepipe UI
            "lib/livepipe/livepipe.js",
            "lib/livepipe/tabs.js",
            "lib/livepipe/window.js",

            // Growler
            //"lib/growler/build/Growler-compressed.js",

            // TreeView
            "includes/javascript/treeview.js",

            // Flotr
            "lib/flotr/flotr.js",
            "lib/flotr/lib/canvastext.js",

            // JS Expression eval
            "lib/jsExpressionEval/parser.js",

            //JS Store.js
            "lib/store.js/store.js",

            "includes/javascript/common.js",
            "includes/javascript/functions.js",
            "includes/javascript/tooltip.js",
            "includes/javascript/controls.js",
            "includes/javascript/vendor/cookies.js",
            "includes/javascript/url.js",
            "includes/javascript/forms.js",
            "includes/javascript/checkForms.js",
            "includes/javascript/aideSaisie.js",
            "includes/javascript/exObject.js",
            "includes/javascript/tag.js",
            "includes/javascript/mbObject.js",
            "includes/javascript/vendor/bowser.min.js",
            "includes/javascript/configuration.js",
            "includes/javascript/vendor/plugin.js",
            "includes/javascript/xdr.js",
            "includes/javascript/usermessage.js",
            "includes/javascript/reglette.js",

            // Doctolib
            "includes/javascript/doctolib.js",

            // require js
            "lib/requirejs/require.js",

            // jQuery
            "lib/flot/jquery.min.js",
            "includes/javascript/vendor/no_conflicts.js",

            // jQuery event (for jquery.flot.navigate)
            "lib/flot/jquery.event.drag.js",

            //Flot
            "lib/flot/jquery.flot.min.js",
            "lib/flot/jquery.flot.JUMlib.js",
            "lib/flot/jquery.flot.mouse.js",
            "lib/flot/jquery.flot.symbol.min.js",
            "lib/flot/jquery.flot.crosshair.min.js",
            "lib/flot/jquery.flot.resize.js",
            "lib/flot/jquery.flot.stack.min.js",
            "lib/flot/jquery.flot.pyramid.js",
            "lib/flot/jquery.flot.bandwidth.js",
            "lib/flot/jquery.flot.gantt.js",
            "lib/flot/jquery.flot.time.min.js",
            "lib/flot/jquery.flot.pie.min.js",
            "lib/flot/jquery.flot.fillbetween.js",
            "lib/flot/jquery.flot.curvedlines.js",
            "lib/flot/jquery.flot.dashes.js",
            // Navigate (drag plot with mouse)
            "lib/flot/jquery.flot.navigate.js",

            // Touch (pan/zoom on touch devices)
            "lib/flot/jquery.flot.touch.js",

            // Paste.js
            "lib/paste.js/paste.js",

            // Prism.js
            "lib/prismjs/prism.js",
            "lib/prismjs/prism.custom_languages.js",
        ];
    }
}
