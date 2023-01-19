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
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Mediboard\Files\CDocumentItem;

/**
 * HTML resource loader utility class
 */
abstract class CHTMLResourceLoader {
  static $build;

  private static $_stylesheet_path = null;

  private static $_aio = false;

  /**
   * @var resource
   */
  private static $_fp_in;

  /**
   * @var resource
   */
  private static $_fp_out;

  /**
   * @var string Path to save the exported files to
   */
  private static $_path;

  /**
   * IE Conditional comments
   *
   * <!--[if IE]>Si IE<![endif]-->
   * <!--[if gte IE 5]> pour réserver le contenu à IE 5.0 et plus récents (actuellement E5.5, IE6.0 et IE7.0) <![endif]-->
   * <!--[if IE 5.0]> pour IE 5.0 <![endif]-->
   * <!--[if IE 5.5000]> pour IE 5.5 <![endif]-->
   * <!--[if IE 6]> pour IE 6.0 <![endif]-->
   * <!--[if gte IE 5.5000]> pour IE5.5 et supérieur <![endif]-->
   * <!--[if lt IE 6]> pour IE5.0 et IE5.5 <![endif]-->
   * <!--[if lt IE 7]> pour IE inférieur à IE7 <![endif]-->
   * <!--[if lte IE 6]> pour IE5.0, IE5.5 et IE6.0 mais pas IE7.0<![endif]-->
   *
   * @param string $content Content to put between IE conditional comments
   * @param string $cc      The conditional comment
   *
   * @return string The content inside conditional comments
   */
  static function conditionalComments($content, $cc) {
    if ($cc) {
      $content = "\n<!--[if $cc]>$content\n<![endif]-->";
    }
    return $content;
  }

  /**
   * Returns the current app build, or the specified build
   *
   * @param mixed $build [optional]
   *
   * @return mixed The build
   */
  static function getBuild($build = null) {
    if (!$build) {
      $build = self::$build;
    }
    return $build;
  }

  /**
   * Returns the hash of a string
   *
   * @param string $string The string to hash
   *
   * @return string The hashed string
   */
  static function getHash($string) {
    return dechex(crc32($string));
  }

  /**
   * Gets the last change of a file
   *
   * @param string $file The file of which we want the last change
   *
   * @return int The timestamp of the last change of the file
   */
  static function getLastChange($file) {
    $stat_cache = stat($file);
    return max($stat_cache[9], $stat_cache[10]);
  }

  /**
   * Returns an HTML tag
   *
   * @param string  $tagName    The tag name
   * @param array   $attributes [optional]
   * @param string  $content    [optional]
   * @param boolean $short      [optional]
   *
   * @return string The HTML source code
   */
  static function getTag($tagName, $attributes = array(), $content = "", $short = true) {
    $tag = "<$tagName";
    foreach ($attributes as $key => $value) {
      $tag .= " $key=\"".CMbString::htmlEntities($value).'"';
    }
    if ($content != "") {
      $tag .= ">$content</$tagName>";
    }
    else {
      if ($short) {
        $tag .= " />";
      }
      else {
        $tag .= "></$tagName>";
      }
    }

    return $tag;
  }

  /**
   * Initialize output handler
   *
   * @param bool $aio Embed everything inside a single file
   *
   * @return void
   */
  static function initOutput($aio){
    self::$_aio = $aio;

    if (self::$_aio) {
      self::$_fp_in = CMbPath::getTempFile();
      ob_start(array(CHTMLResourceLoader::class, "outputToFile"), 8192);
    }
    else {
      ob_start();
    }
  }

  static $flushed_output_length = 0;

  /**
   * Output the content to the standard output
   *
   * @param array $options Options
   *
   * @return void
   */
  static function output($options = array()){
    if (self::$_aio) {
      $path = CAppUI::getTmpPath("embed-".md5(uniqid("", true)));

      if (self::$_aio === "savefile") {
        $str = self::allInOne($path);
        file_put_contents("$path/index.html", $str);

        $zip_path = "$path.zip";
        CMbPath::zip($path, $zip_path);

        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=\"".basename($zip_path)."\";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($zip_path));
        readfile($zip_path);

        unlink($zip_path);
        CMbPath::remove($path);

        CApp::rip();
      }
      else {
        self::allInOne(null, $options);
      }
    }
    else {
      // Flush zero-ifies ob_get_length
      self::$flushed_output_length += ob_get_length();
      ob_end_flush();
    }
  }


  /**
   * Write a string to the cache file
   *
   * @param string $str   The string to write
   * @param int    $flags Flags (not used)
   *
   * @return string An empty string
   */
  static function outputToFile($str, $flags) {
    fwrite(self::$_fp_in, $str);
    return "";
  }

  /**
   * Get real memory peak usage or its placeholder
   *
   * @param bool $real Return the real memory peak usage or the placeholder
   *
   * @return string A formatted memory peak usage view
   */
  static function getOutputMemory($real = false){
    if ($real || !self::$_aio) {
      return CMbString::toDecaBinary(memory_get_peak_usage(true));
    }

    return "[[AIO-memory]]";
  }

  /**
   * Get output length or its placeholder
   *
   * @return string A formatted output length view
   */
  static function getOutputLength(){
    if (!self::$_aio) {
      return CMbString::toDecaBinary(ob_get_length());
    }

    return "[[AIO-length]]";
  }

  /**
   * Get the content of a file
   *
   * @param string $filename The name of the file
   *
   * @return string The content of the file
   */
  private static function getFileContents($filename) {
    if (file_exists($filename)) {
      return file_get_contents($filename);
    }

    return null;
  }

  /**
   * Get embeddable URL
   *
   * @param string $src     Original URL
   * @param string $content File content
   * @param string $subpath Subpath to store the file to
   *
   * @return string
   */
  private static function getEmbedURL($src, $content, $subpath) {
    $hash = md5($src);
    $subpath .= "/";
    CMbPath::forceDir(self::$_path.$subpath);
    file_put_contents(self::$_path.$subpath.$hash, $content);
    return "$subpath$hash";
  }

  /**
   * Replace <script> src attribute with the contents of the script
   *
   * @param array $matches The matches of the regular expression
   *
   * @return string The <script> tag
   */
  private static function replaceScriptSrc($matches) {
    $src = $matches[1];
    $orig_src = $src;
    $src = preg_replace('/(\?.*)$/', '', $src);
    $script = self::getFileContents($src);

    if (self::$_path) {
      return '<script type="text/javascript" src="'.self::getEmbedURL($orig_src, $script, "script").'"></script>';
    }
    else {
      return '<script type="text/javascript">'.$script.'</script>';
    }
  }

  /**
   * Replace <img> src attribute with the contents of the image (base 64 encoded)
   *
   * @param array $matches The matches of the regular expression
   *
   * @return string The <img> tag
   */
  private static function replaceImgSrc($matches) {
    $src       = $matches[2];
    $orig_src  = $src;
    $is_base64 = false;
    $src       = preg_replace('/(\?.*)$/', '', $src);

    if ($src) {
      if ($src[0] === "/") {
        $src = $_SERVER['DOCUMENT_ROOT'] . $src;
      }

      $ext = CMbPath::getExtension($src);
      if (preg_match('/data:image\/(?<ext>\w+);base64/', $src, $matches_ext)) {
        $mime      = "image/" . $matches_ext["ext"];
        $is_base64 = true;
      }
      else {
        $mime = "image/$ext";
      }

      $img = self::getFileContents($src);
    }

    // Url avec des arguments (phpthumb par exemple)
    else {
      return null;
      /* // Ne fonctionne pas bien
      $session_name = CAppUI::$instance->session_name;
      $session_id = session_id();
      $src = CApp::getBaseUrl()."/".$matches[2];
      $context = stream_context_create(array(
        "http" => array(
          "method" => "GET",
          "header" => "Cookie: $session_name=$session_id\r\n"
        )
      ));


      $mime = "image/png";
      $img = file_get_contents($src, false, $context);

      foreach($http_response_header as $header) {
        if (preg_match("/^Content-Type: ([a-z\/]+)/", $header, $matches)) {
          $mime = $matches[1];
          break;
        }
      }*/
    }

    $matches[3] = rtrim($matches[3], " /");
    if ($is_base64) {
      $img = ' src="' . $src . '" ';
    }
    else {
      if (self::$_path) {
        $img = " src=\"" . self::getEmbedURL($orig_src, $img, "img") . "\" ";
      }
      else {
        $img = " src=\"data:$mime;base64," . base64_encode($img) . "\" ";
      }
    }

    return '<img ' . $matches[1] . $img . $matches[3] . ($matches[4] ? ' />' : "");
  }

  /**
   * Replace <img> src attribute with the contents of the image (base 64 encoded)
   *
   * @param array $matches The matches of the regular expression
   *
   * @return string|null The <img> tag
   */
  private static function replaceAEmbed($matches) {
    $src = $matches[2];
    $orig_src = $src;
    $src = preg_replace('/(\?.*)$/', '', $src);

    if (!$src) {
      return null;
    }

    if ($src[0] === "/") {
      $src = $_SERVER['DOCUMENT_ROOT'] . $src;
    }

    $sub_matches = array();
    if (preg_match("/^(\w+),(\d+)$/", $src, $sub_matches)) {
      list($all, $class, $id) = $sub_matches;

      /** @var CDocumentItem $obj */
      $obj = new $class;
      $obj->load($id);
      if (!$obj->canRead()) {
        return null;
      }

      $file = $obj->getBinaryContent();
    }
    else {
      $file = self::getFileContents($src);
    }

    $href = " href=\"".self::getEmbedURL($orig_src, $file, "embed")."\" ";

    return '<a '.$matches[1].$href.$matches[3].'>';
  }

  /**
   * Get the contents of a stylesheet
   *
   * @param array $matches The matches of the regular expression
   *
   * @return string The stylesheet contents
   */
  private static function replaceStylesheetImport($matches) {
    return self::getFileContents(self::$_stylesheet_path."/".$matches[1]);
  }

  /**
   * Replace the url(...) in a stylsheet with the base 64 encoded content of the stylesheet
   *
   * @param array $matches The regex matches
   *
   * @return string The url(...) string
   */
  private static function replaceStylesheetUrl($matches) {
    $src = $matches[1];

    if (strpos($src, "data:") === 0) {
      return $src;
    }

    $src = preg_replace('/(\?.*)$/', '', $src);
    $ext = CMbPath::getExtension($src);
    $url = self::getFileContents(self::$_stylesheet_path."/".$src);
    return "url(data:image/$ext;base64,".base64_encode($url).")";
  }

  /**
   * Embed the stylesheets' content
   *
   * @param array $matches The matches of the regular expression
   *
   * @return string The <style> tag
   */
  private static function replaceStylesheet($matches) {

    $line = $matches[0];
    // If the link doesn't contains href attributes, the line is not modify
    if (!preg_match("/href\s*=\s*[\"'](?<href>[^\"']+)[\"']/", $line, $match_src)) {
        return $line;
    }
    $orig_src = $match_src["href"];
    $src = preg_replace('/(\?.*)$/', '', $orig_src);
    $stylesheet = self::getFileContents($src);

    self::$_stylesheet_path = dirname($src);

    $media = null;
    if (preg_match("/media\s*=\s*[\"'](?<media>[^\"']+)[\"']/", $line, $match_media)) {
        $media = sprintf('media="%s"', $match_media["media"]);
    }

    // @import
    $re = "/\@import\s+(?:url\()?[\"']?([^\"\'\)]+)[\"']?\)?;/i";
    $stylesheet = preg_replace_callback($re, array('self', 'replaceStylesheetImport'), $stylesheet);

    // url(foo)
    $re = "/url\([\"']?([^\"\'\)]+)[\"']?\)?/i";
    $stylesheet = preg_replace_callback($re, array('self', 'replaceStylesheetUrl'), $stylesheet);

    if (self::$_path) {
      return '<link rel="stylesheet" href="'.self::getEmbedURL($orig_src, $stylesheet, "css").'" '
          . $media . '>';
    }
    else {
      return '<style type="text/css" ' . $media . '>'.$stylesheet.'</style>';
    }
  }

  /**
   * Embed all the external resources of the current output buffer inside a single file and outputs it.
   *
   * @param string $path    Path to save the files to
   * @param array  $options Options (ignore_scripts)
   *
   * @return string
   */
  private static function allInOne($path = null, $options = array()) {
    if ($path) {
      self::$_path = rtrim($path, "/\\")."/";
    }

    CApp::setMemoryLimit("256M");

    self::$_fp_out = CMbPath::getTempFile();

    $re_img    = "/<img([^>]*)src\s*=\s*[\"']([^\"']+)[\"']([^>]*)(>|$)/i";
    $re_link   = "/<link[^>]*rel=\"stylesheet\"[^>]*>/i";
    $re_script = "/<script[^>]*src\s*=\s*[\"']([^\"']+)[\"'][^>]*>\s*<\/script>/i";
    $re_a      = "/<a([^>]*)href\s*=\s*[\"']embed:([^\"']+)[\"']([^>]*)>/i";

    $ignore_scripts = !empty($options["ignore_scripts"]);

    // End Output Buffering
    ob_end_clean();

    ob_start();

    rewind(self::$_fp_in);
    while (!feof(self::$_fp_in)) {
      $line = fgets(self::$_fp_in);

      $line = preg_replace_callback($re_img,    array('self', 'replaceImgSrc'), $line);
      $line = preg_replace_callback($re_link,   array('self', 'replaceStylesheet'), $line);

      if (!$ignore_scripts) {
        $line = preg_replace_callback($re_script, array('self', 'replaceScriptSrc'), $line);
      }

      if (self::$_path) {
        $line = preg_replace_callback($re_a, array('self', 'replaceAEmbed'), $line);
      }

      fwrite(self::$_fp_out, $line);
    }

    ob_end_clean();

    $length = 0;
    rewind(self::$_fp_out);

    $full_str = "";

    while (!feof(self::$_fp_out)) {
      $line = fgets(self::$_fp_out);

      $length += strlen($line);

      $line = str_replace("[[AIO-length]]", CMbString::toDecaBinary($length), $line);

      if (strpos($line, "[[AIO-memory]]") !== false) {
        $line = str_replace("[[AIO-memory]]", self::getOutputMemory(true), $line);
      }

      if ($path) {
        $full_str .= $line;
      }
      else {
        echo $line;
      }
    }

    return $full_str;
  }
}

if (PHP_SAPI !== "cli") {
  CHTMLResourceLoader::$build = CApp::getVersion()->getKey();
}
