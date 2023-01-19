<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CFile;

CCanDo::checkAdmin();

$start    = CView::post("start", "num default|0");
$step     = CView::post("step", "num default|10");
$continue = CView::post("continue", "num default|0");

CView::checkin();

set_time_limit(300);

$file = new CFile();
$where              = array(
  "doc_size"     => "= '0'",
  "object_class" => "= 'CCompteRendu'",
);

$cr_ids = $file->loadColumn("object_id", $where, null, "$start,$step");

$cr  = new CCompteRendu();
$crs = $cr->loadAll($cr_ids);

/** @var CCompteRendu $_cr */
foreach ($crs as $_cr) {
  $_cr->factory = "CWkHtmlToPDFConverter";
  if ($msg = $_cr->makePDFpreview(true)) {
    CAppUI::setMsg($msg, UI_MSG_WARNING);
  }
  else {
    CAppUI::setMsg("CCompteRendu-regenerated", UI_MSG_OK);
  }
}

if ($continue) {
  CAppUI::js("nextRegeneration()");
}