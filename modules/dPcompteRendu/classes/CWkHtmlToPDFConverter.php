<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\Logger\LoggerLevels;

/**
 * WkHtmlToPDF Converter
 */
class CWkHtmlToPDFConverter extends CHtmlToPDFConverter {
  public $file;
  public $width;
  public $height;
  public $format;
  public $orientation;
  public $header;
  public $header_height;
  public $header_spacing = 0;
  public $footer;
  public $footer_height;
  public $footer_spacing = 0;
  public $body;
  public $margins;
  public $temp_name;

  /**
   * @see parent::prepare()
   */
  function prepare($format, $orientation) {
    global $rootName;

    $this->html = "<!DOCTYPE html>" . $this->html;

    // Changer les srs pour les images
    $this->html = preg_replace("/src=\"\/" . $rootName . "/", "src=\"../", $this->html);

    // Changer le src pour vertical.css
    $this->html = str_replace("vertical.css", CAppUI::conf("root_dir") . "/style/mediboard_ext/vertical.css", $this->html);

    // Suppression de l'opacité sur les éléments (cela cause une erreur de segmentation)
    $this->html = preg_replace("/opacity\s*:\s*[0-9.]+;/", "", $this->html);

    if (is_array($format)) {
      $this->width  = $format[2];
      $this->height = $format[3];
    }
    else {
      $this->format      = $format;
      $this->orientation = $orientation;
    }

    // Racine du ou des fichiers html
    $this->temp_name = tempnam("./tmp", "wkhtmltopdf");

    // Extraire les marges
    preg_match(
      "/@page\s*{\s*margin-top:\s*([0-9.]+)cm;\s*" .
      "margin-right:\s*([0-9.]+)cm;\s*" .
      "margin-bottom:\s*([0-9.]+)cm;\s*margin-left:\s*([0-9.]+)cm;/",
      $this->html,
      $matches
    );

    if (count($matches)) {
      // Le facteur 10 est pour la conversion en mm
      $this->margins = array(
        "top"    => $matches[1] * 10,
        "right"  => $matches[2] * 10,
        "bottom" => $matches[3] * 10,
        "left"   => $matches[4] * 10,
      );

      $pos_header = strpos($this->html, "<div id=\"header\"");
      $pos_footer = strpos($this->html, "<div id=\"footer\"");
      $pos_body   = strpos($this->html, "<div id=\"body\">");

      /* header / footer sans body */
      if (!$pos_body) {
        $pos_body = strlen($this->html) - 16;
      }

      $header               = null;
      $footer               = null;
      $header_footer_common = null;
      $page_number          = "<script type='text/javascript'>
        function subst() {
          var vars = {},
              x = document.location.search.substring(1).split('&');
          for (var i in x) {
            var z = x[i].split('=', 2);
            vars[z[0]] = decodeURI(z[1]);
          }
          x = ['page', 'topage'];
          for (var j in x) {
            z = x[j];
            var y = document.getElementsByClassName(z);
            for (var k = 0; k < y.length; ++k) {
              y[k].textContent = vars[z];
            }
          }
        }
      </script>";
      // Extraire l'entête
      if ($pos_header) {
        $header_footer_common = substr($this->html, 0, $pos_header);
        if ($pos_footer) {
          $header = substr($this->html, $pos_header, $pos_footer - $pos_header);
        }
        else {
          $header = substr($this->html, $pos_header, $pos_body - $pos_header);
        }

        // On trouve la taille du header dans le style
        preg_match("/#header\s*\{\s*height:\s*([0-9]+[\.0-9]*)px;/", $this->html, $matches);
        $this->header_height = $matches[1];
      }

      // Extraire le pied de page
      if ($pos_footer) {
        if (!$pos_header) {
          $header_footer_common = substr($this->html, 0, $pos_footer);
        }
        $footer = substr($this->html, $pos_footer, $pos_body - $pos_footer);

        $this->html = str_replace($footer, '', $this->html);

        preg_match("/#footer\s*{\s*height:\s*([0-9]+[\.0-9]*)px;/", $this->html, $matches);
        $this->footer_height = $matches[1];
      }

      // Supprimer le padding-top du hr et le margin-top du body
      $this->html = preg_replace("/body\s*\{\s*margin-top:\s*[0-9]*px;\s*\}/", "", $this->html);
      if ($header_footer_common != null) {
        $header_footer_common = preg_replace("/body\s*\{\s*margin-top:\s*[0-9]*px;\s*\}/", "", $header_footer_common);
        $header_footer_common = preg_replace("/<body>/", "<body onload='subst()'>", $header_footer_common);
      }
      $this->html = preg_replace("/hr.pagebreak\s*{\s*padding-top:\s*[0-9]*px;\s*}/", "", $this->html);

      // Supprimer le margin-bottom du body
      $this->html = preg_replace("/body\s*\{\s*margin-bottom:\s*[0-9]*px;\s*\}/", "", $this->html);

      if ($header_footer_common != null) {
        $header_footer_common = preg_replace("/body\s*\{\s*margin-bottom:\s*[0-9]*px;\s*\}/", "", $header_footer_common);
      }

      // Suppression de la balise script pour l'impression
      $this->html = preg_replace("/(<script type=[\'\"]text\/javascript[\'\"]>.*<\/script>)/msU", "", $this->html);

      // Supression du margin: 0 et padding: 0
      $this->html = preg_replace("/body\s*{([a-zA-Z0-9:;\-\n\s\t]*)(margin:\s*0;[\n\t\s]*padding:\s*0;)/", 'body { $1', $this->html);

      // Suppression du position fixed du header et du footer
      if ($header_footer_common) {
        $header_footer_common = preg_replace("/position:\s*fixed;/", "", $header_footer_common);
        $header_footer_common = preg_replace("/(<script type=[\'\"]text\/javascript[\'\"]>.*<\/script>)/msU", "", $header_footer_common);
      }

      // Store de l'entête / pied de page
      if ($header) {
        // On supprime l'entête que maintenant sinon les positions de chaînes seront erronées
        $this->html   = str_replace($header, '', $this->html);
        $this->header = $this->temp_name . "-header.html";

        // Erreur de segmentation en 0.12.3 lorsque le header est display none
        $header = str_replace(
            '<div id="header" style="display: none;">',
            '<div id="header" style="display: block;">',
            $header
        );

        file_put_contents($this->header, $header_footer_common . $page_number . $header . "</body></html>");
      }
      if ($footer) {
        $this->footer = $this->temp_name . "-footer.html";

        // Erreur de segmentation en 0.12.3 lorsque le footer est display none
        $footer = str_replace(
            '<div id="footer" style="display: none;">',
            '<div id="footer" style="display: block;">',
            $footer
        );

        file_put_contents($this->footer, $header_footer_common . $page_number . $footer . "</body></html>");
      }

      if (!$pos_body) {
        $this->html = $header_footer_common . $page_number . "</body></html>";
      }
    }

    $this->file = $this->temp_name . ".html";
    file_put_contents($this->file, $this->html);
  }

  /**
   * Tells if we are under Windows
   *
   * @return bool
   */
  protected static function isWindows() {
    return stripos(PHP_OS, "WIN") === 0;
  }

  /**
   * @see parent::render()
   */
  function render() {
    $bin = CWkhtmlToPDF::getExecutable($this->options);

    $autoprint = CMbArray::extract($this->options, "autoprint", true);

    $command = "$bin -q ";

    $result  = tempnam("./tmp", "result");
    $options = "--print-media-type ";

    // Entête
    if ($this->header) {
      $this->margins["top"] += (25.4 * $this->header_height) / 96;
      $options              .= "--header-html " . escapeshellarg($this->header) . " --header-spacing " . escapeshellarg($this->header_spacing) . " ";
    }

    // Pied de page
    if ($this->footer) {
      $this->margins["bottom"] += ((25.4 * $this->footer_height) / 96 + $this->footer_spacing);
      $options                 .= "--footer-html " . escapeshellarg($this->footer) . " --footer-spacing " . escapeshellarg($this->footer_spacing) . " ";
    }

    // Marges
    if ($this->margins) {
      foreach ($this->margins as $key => $_marge) {
        $options .= "--margin-$key " . escapeshellarg($_marge) . " ";
      }
    }

    // Format de la page
    if ($this->format && $this->orientation) {
      $options .= "--page-size " . escapeshellarg($this->format) . " --orientation " . escapeshellarg($this->orientation) . " ";
    }

    if ($this->width && $this->height) {
      // Conversion en mm
      $width   = (25.4 * $this->width) / 72;
      $height  = (25.4 * $this->height) / 72;
      $options .= "--page-width " . escapeshellarg($width) . " --page-height " . escapeshellarg($height) . " ";
    }

    $options .= escapeshellarg($this->file) . " " . escapeshellarg($result);

    if (!self::isWindows()) {
      $options .= " 2> /dev/null";
    }

    exec($command . $options, $output, $return_code);
    if ($return_code !== 0) {
      CApp::log("WkHtmlToPDF error " . $return_code, null, LoggerLevels::LEVEL_DEBUG);
    }

    $this->result = file_get_contents($result);

    // Ajout de l'auto-print (en attendant la gestion au niveau de la lib)
    if ($autoprint) {
      $this->result = self::addAutoPrint($this->result);
    }

    // Suppression des fichiers temporaires
    $files = array(
      $this->temp_name,
      $this->header,
      $this->footer,
      $this->file,
      $result,
    );

    CMbArray::removeValue("", $files);

    foreach ($files as $_file) {
      try {
        unlink($_file);
      }
      catch (CMbException $e) {
        CApp::log("Failed to delete file $_file : " . $e->getMessage());
      }
    }
  }

  static $from_autoprint_add = "/Pages ([0-9]) 0 R";
  static $to_autoprint_add = "/Pages $1 0 R\n/OpenAction << /Type /Action /S /Named /N /Print >>";

  static function addAutoPrint($content) {
    $content = preg_replace("#" . self::$from_autoprint_add . "#", self::$to_autoprint_add, $content);

    return self::ajustXref($content);
  }

  static $from_autoprint_remove = "/Pages ([0-9]) 0 R\n/OpenAction << /Type /Action /S /Named /N /Print >>";
  static $to_autoprint_remove = "/Pages $1 0 R";

  static function removeAutoPrint($content) {
    $content_length = strlen($content);
    $content        = preg_replace("#" . self::$from_autoprint_remove . "#", self::$to_autoprint_remove, $content);

    return $content_length === strlen($content) ? $content : self::ajustXref($content);
  }

  static function ajustXref($content) {
    if (!$content) {
        return $content;
    }

    preg_match_all("/^[0-9]+ 0 obj$/m", $content, $matches, PREG_OFFSET_CAPTURE);
    $matches_obj = $matches[0];

    preg_match_all('/^(\d{10}) 00000/m', $content, $matches_xref);
    $matches_xref = $matches_xref[1];

    usort(
      $matches_obj,
      function ($obj1, $obj2) {
        $split1 = explode(" ", $obj1[0]);
        $split1 = intval($split1[0]);

        $split2 = explode(" ", $obj2[0]);
        $split2 = intval($split2[0]);

        return $split1 <=> $split2;
      }
    );

    $matches_new_obj = array();
    foreach ($matches_obj as $_match) {
      $matches_new_obj[] = str_pad($_match[1], 10, "0", STR_PAD_LEFT);
    }

    // Remplacement des références dans la table xref
    $content = str_replace($matches_xref, $matches_new_obj, $content);

    // Remplacement de la position d'indication du début de la table xref
    preg_match("/^xref/m", $content, $match, PREG_OFFSET_CAPTURE);
    $content = preg_replace("/startxref\n([0-9]+)/", "startxref\n" . $match[0][1], $content);

    return $content;
  }
}
