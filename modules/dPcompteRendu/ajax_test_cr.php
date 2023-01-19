<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\Files\CFile;

CCanDo::checkRead();

$cr_id   = CView::get("cr_id", "ref class|CCompteRendu");
$sens    = CView::get("sens", "enum list|left|right default|left");
$factory = CView::get("factory_$sens", "str");

CView::checkin();

$cr = new CCompteRendu();
$cr->load($cr_id);

if (!$cr->_id) {
  return;
}

$cr->loadContent();

$margins = array (
  $cr->margin_top,
  $cr->margin_right,
  $cr->margin_bottom,
  $cr->margin_left
);

$header  = ""; $sizeheader = 0;
$footer  = ""; $sizefooter = 0;
$preface = "";
$ending  = "";
if ($cr->header_id) {
  $component = new CCompteRendu;
  $component->load($cr->header_id);
  $component->loadContent();
  $header = $component->_source;
  $sizeheader = $component->height;
}
if ($cr->preface_id) {
  $component = new CCompteRendu;
  $component->load($cr->preface_id);
  $component->loadContent();
  $preface = $component->_source;
}
if ($cr->ending_id) {
  $component = new CCompteRendu;
  $component->load($cr->ending_id);
  $component->loadContent();
  $ending = $component->_source;
}
if ($cr->footer_id) {
  $component = new CCompteRendu;
  $component->load($cr->footer_id);
  $component->loadContent();
  $footer = $component->_source;
  $sizefooter = $component->height;
}

$content = $cr->loadHTMLcontent(
  $cr->_source,
  "modele",
  $margins,
  CCompteRendu::$fonts[$cr->font],
  $cr->size,
  null,
  "body",
  $header,
  $sizeheader,
  $footer,
  $sizefooter,
  $preface,
  $ending
);

$options = array(
  "autoprint" => false
);

$param_factory = strpos($factory, "C") === 0 ? $factory : null;

$htmltopdf = new CHtmlToPDF($param_factory, $options);

$htmltopdf->generatePDF($content, 1, $cr, new CFile());