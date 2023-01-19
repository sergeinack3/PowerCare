<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\Files\CFile;

CCanDo::checkAdmin();

$limit = CView::get("limit", "num default|100");

CView::checkin();

CApp::setTimeLimit(2 * $limit);

$shrink_level = CAppUI::gconf("dPcompteRendu CCompteRendu shrink_pdf");

if (!$shrink_level) {
  CAppUI::stepAjax("Configurez un niveau de compression dans le module Modèles");
  CApp::rip();
}

$file = new CFile();

$where = array(
  "object_class" => "= 'CCompteRendu'",
  "compression"  => "IS NULL",
);

/** @var CFile[] $files */
$files = $file->loadList($where, "file_id DESC", $limit);

foreach ($files as $_file) {
  // Si le PDF n'existe pas, on passe par la méthode classique (génération du PDF et shrink automatique)
  if (!is_file($_file->_file_path) || file_get_contents($_file->_file_path) == "") {
    /** @var CCompteRendu $doc */
    $doc = $_file->loadTargetObject();
    // Pour éviter le double chargement du CFile, on le place dans la backref du document
    $doc->_count["files"] = 1;
    $doc->_back["files"] = array($_file);
    $doc->makePDFpreview(true);
    continue;
  }

  CFile::shrinkPDF($_file->_file_path, null, $shrink_level);
  $_file->compression = CHtmlToPDF::$shrink_levels[$shrink_level];
  $_file->doc_size = strlen(file_get_contents($_file->_file_path));
  $_file->rawStore();
}