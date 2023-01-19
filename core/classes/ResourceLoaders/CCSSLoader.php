<?php
/**
 * @package Mediboard\Core\ResourceLoaders
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\ResourceLoaders;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbPath;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;

/**
 * CSS resource loader utility class
 */
abstract class CCSSLoader extends CHTMLResourceLoader {

  /**
   * Links a style sheet
   * Only to be called while in the HTML header
   *
   * @param string $file  Filename of the stylesheet
   * @param string $media A valid CSS media query
   * @param string $cc    An IE conditional comment
   * @param string $build A build number
   * @param string $type  The mime type to use to load the stylesheet
   *
   * @return string A <link> tag to load the stylesheet
   */
  static function loadFile($file, $media = null, $cc = null, $build = null, $type = "text/css") {
    $tag = self::getTag(
      "link",
      array(
        "type"  => $type,
        "rel"   => "stylesheet",
        "href"  => "$file?build=" . self::getBuild($build),
        "media" => $media,
      )
    );

    return self::conditionalComments($tag, $cc);
  }

  /**
   * Builds a list of HTML <link> tags to load the stylesheets of the theme
   *
   * @param string $theme        The theme name
   * @param string $media        A valid CSS media query
   * @param string $type         The mime type to load the stylesheets
   * @param string $css_list_add Additional entry for the css_list option
   *
   * @return string A list of HTML <link> tags
   */
  static function loadFiles($theme = "mediboard_ext", $media = "all", $type = "text/css", $css_list_add = null) {
    $cache_files = new Cache(
        'CCSSLoader.loadFiles',
        [$theme, $media, str_replace('/', '', $type), $css_list_add],
        Cache::INNER_OUTER
    );

    if (CModule::haveModulesChanged()) {
      $cache_files->rem();
    }

    $cache = $cache_files->get();
    if ($cache) {
      $files = $cache;
    }
    else {
      if ($theme === "modules") {
        $files = glob("modules/*/css/main.css");
        $files = array_merge($files, glob("modules/*/css/templates/main.css.tpl"));
      }
      else {
        if ($theme === "mobile") {
          $path = "mobile/style";
        }
        else {
          $path = "style/$theme";
        }

        if (file_exists("$path/css.list")) {
          $list = file("$path/css.list", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
          if ($css_list_add) {
            array_push($list, $css_list_add);
          }
          $list = array_map("trim", $list);
        }
        else {
          $list = array("main.css");
        }

        $files = array();
        foreach ($list as $_file) {
          $files[] = "$path/$_file";
        }
      }

      $cache_files->put($files);
    }

    $compress = CAppUI::conf("minify_css");

    $result   = "";
    $uptodate = false;

    $hash        = self::getHash(implode("", $files) . "-level-$compress");
    $cachefile   = "tmp/$hash-$theme.css";
    $last_update = null;

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
      $all = "";
      foreach ($files as $_file) {
        $_path = dirname($_file);

        // CSS templates
        if (substr($_file, -8) === ".css.tpl") {
          $template = new CSmartyDP(dirname(dirname($_file)));
          $content  = $template->fetch(basename($_file));
        }

        // Standard CSS
        else {
          $content = file_get_contents($_file);
          $content = preg_replace("/\@import\s+(?:url\()?[\"']?([^\"\'\)]+)[\"']?\)?;/i", "", $content); // remove @imports
        }

        // relative paths
        $content = preg_replace_callback(
          "/(url\s*\(\s*[\"\']?)([^\)]+)/",
          function ($matches) use ($_path) {
            if (strpos($matches[2], "data:") === false) {
              return $matches[1] . "../$_path/" . $matches[2];
            }
            else {
              return $matches[1] . $matches[2];
            }
          },
          $content
        );

        $all .= $content . "\n";
      }

      if ($compress == 2) {
        $all = self::minify($all);
      }

      file_put_contents($cachefile, $all);
      $last_update = time();
    }

    $result .= self::loadFile($cachefile, $media, null, $last_update, $type) . "\n";

    return $result;
  }

  /**
   * Simple home-made CSS minifier
   *
   * @param string $css The CSS code
   *
   * @return string The minified CSS code
   */
  static function minify($css) {
    $css = str_replace(array("\r\n", "\r", "\n", "\t"), "", $css); // whitespace
    $css = preg_replace("/\s+/", " ", $css); // multiple spaces
    $css = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $css); // comments
    $css = preg_replace("/\s*([\{\};:,>])\s*/", "$1", $css); // whitespace around { } ; : , >
    $css = str_replace(";}", "}", $css); // ;} >> }
    $css = str_replace("/./", "/", $css); // /./ >> /
    $css = CMbPath::reduce($css); // foo/../ >> /
    //$css = preg_replace("/#([0-9A-F])\\1([0-9A-F])\\2([0-9A-F])\\3/i", "#\\1\\2\\3", $css); // Reduce #6699FF to #69F
    return $css;
  }

  public static function loadAllFiles(): string {
      $mediboardStyle = "";
      if (CAppUI::isMediboardExtDark()) {
          $mediboardStyle .= CCSSLoader::loadFiles(
              CAppUI::MEDIBOARD_EXT_THEME,
              "not print",
              "text/css",
              "dark.css"
          );
          $mediboardStyle .= CCSSLoader::loadFiles(
              CAppUI::MEDIBOARD_EXT_THEME,
              "only print",
              "text/css",
              "standard.css"
          );
      } else {
          $mediboardStyle .= CCSSLoader::loadFiles(
              CAppUI::MEDIBOARD_EXT_THEME,
              "all",
              "text/css",
              "standard.css"
          );
      }
      return $mediboardStyle . CCSSLoader::loadFiles("modules");
  }
}
