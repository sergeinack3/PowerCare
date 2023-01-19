<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use DOMPDF;
use Font_Metrics;
use Ox\Core\CApp;
use Ox\Core\CAppUI;

/**
 * Frontend permettant la conversion html to pdf via dompdf
 * Cette classe n'est pas un MbObject et les objets ne sont pas enregistrés en base
 */
class CDomPDFConverter extends CHtmlToPDFConverter {
  CONST CONFIG_FILE = 'vendor/openxtrem/dompdf/dompdf_config.inc.php';

  /** @var DOMPDF */
  public $dompdf;

  /**
   * @return bool
   */
  private function requireLibrary() {
    $root = CAppUI::conf('root_dir');
    $file = $root . DIRECTORY_SEPARATOR . self::CONFIG_FILE;
    if (!file_exists($file)) {
      self::setMsg("common-msg-Library %s is not installed", UI_MSG_ERROR, 'dompdf');
      CApp::rip();
    }

    if (defined("DOMPDF_DIR")) {
      return true;
    }

    return include $file;
  }

  /**
   * Préparation de dompdf pour la conversion
   *
   * @param string $format      format de la page
   * @param string $orientation orientation de la page
   *
   * @return void
   */
  function prepare($format, $orientation) {
    CAppUI::requireModuleFile("dPcompteRendu", "dompdf_config");
    $this->requireLibrary();

    $this->dompdf = new dompdf();
    $this->dompdf->set_base_path(realpath(__DIR__ . "/../../../../"));
    $this->dompdf->set_paper($format, $orientation);
    if (CAppUI::gconf("dPcompteRendu CCompteRendu dompdf_host")) {
      $this->dompdf->set_protocol(isset($_SERVER["HTTPS"]) ? "https://" : "http://");
      $this->dompdf->set_host($_SERVER["SERVER_NAME"]);
    }
  }

  /**
   * Effectue le rendu du contenu html en pdf
   *
   * @return void
   */
  function render() {
    $this->dompdf->load_html($this->html);
    $this->dompdf->render();
    if (CHtmlToPDFConverter::$_page_ordonnance) {
      $this->dompdf->get_canvas()->page_text(273, 730, "Page {PAGE_NUM} / {PAGE_COUNT}", Font_Metrics::get_font("arial"), 10);
    }

    $this->result = CWkHtmlToPDFConverter::addAutoPrint($this->dompdf->output());
  }
}
