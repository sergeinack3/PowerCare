<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

/**
 * Description
 */
class CPrinceXMLConverter extends CHtmlToPDFConverter {
  /**
   * @see parent::prepare()
   */
  function prepare($format, $orientation) {
    global $rootName;

    // Changer les src pour les images
    $this->html = preg_replace("/src=\"\/" . $rootName . "/", "src=\"../", $this->html);

    // Suppression du margin-top sur le body et le padding-top sur le hr
    $this->html = preg_replace("/body\s*\{\s*margin-top.*\s*\}\s*hr.pagebreak\s*\{\s*padding-top.*\s*\}/m", "", $this->html);

    // Ajout du style pour gestion de l'entête et du pied de page
    $header_height = $footer_height = $margin_top = $margin_bottom = 0;

    preg_match("/#header\s*\{\s*height:\s*([0-9]+[\.0-9]*)px;/", $this->html, $matches);
    if (isset($matches[1])) {
      $header_height = $matches[1];
    }

    preg_match("/#footer\s*{\s*height:\s*([0-9]+[\.0-9]*)px;/", $this->html, $matches);
    if (isset($matches[1])) {
      $footer_height = $matches[1];
    }

    preg_match(
      "/@page\s*{\s*margin-top:\s*([0-9.]+)cm;\s*" .
      "margin-right:\s*([0-9.]+)cm;\s*" .
      "margin-bottom:\s*([0-9.]+)cm;\s*margin-left:\s*([0-9.]+)cm;/",
      $this->html,
      $matches
    );
    if (count($matches)) {
      $margin_top    = $matches[1];
      $margin_bottom = $matches[4];
    }

    // Format et orientation
    $size = $format;
    if (is_array($format)) {
      $width  = $format[2] * 25.4 / 72;
      $height = $format[3] * 25.4 / 72;
      $size   = "{$width}mm {$height}mm";
    }

    $size .= " $orientation";

    $style = "
      @page {
        size:           $size;";

    if ($header_height) {
      $style .= "margin-top:    {$header_height}px;";
    }

    if ($footer_height) {
      $style .= "margin-bottom: {$footer_height}px;";
    }

    if ($header_height) {
      $style .= "
        @top {
          content: flow(header);
          padding-top: {$margin_top}cm;
        }";
    }
    if ($footer_height) {
      $style .= "
        @bottom {
          content: flow(footer);
          padding-bottom: {$margin_bottom}cm;
        }";
    }
    $style .= "}";

    if ($header_height) {
      $style .= "
        div#header {
          flow: static(header);
        }";
    }

    if ($footer_height) {
      $style .= "
        div#footer {
          flow: static(footer);
        }";
    }

    // Autoprint
    $style .= "
    @prince-pdf {
      prince-pdf-open-action: print
    }";

    $pos_end_style = strpos($this->html, "</style>");

    $begin_doc = substr($this->html, 0, $pos_end_style);
    $end_doc   = substr($this->html, $pos_end_style);

    $this->html = $begin_doc.$style.$end_doc;
    $this->temp_name = tempnam("./tmp", "princexml");
    $this->file = $this->temp_name.".html";

    file_put_contents($this->file, $this->html);
  }

  /**
   * @see parent::render()
   */
  function render() {
    exec("prince $this->file");

    $result = $this->temp_name.".pdf";
    $this->result = file_get_contents($result);

    unlink($result);
    unlink($this->file);
    unlink($this->temp_name);
  }
}
