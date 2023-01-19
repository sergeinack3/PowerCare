<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Files\CThumbnail;

CCanDo::check();

$document_class = CView::get('document_class', 'enum list|CFile|CCompteRendu default|CFile');
$document_id    = CView::get('document_id', 'ref class|CDocumentItem meta|document_class');
$document_guid  = CView::get('document_guid', 'guid class|CDocumentItem');
$profile        = CView::get('profile', 'enum list|' . implode('|', array_keys(CThumbnail::PROFILES)) . ' default|' . CThumbnail::PROFILE_MEDIUM);
$rotate         = CView::get('rotate', 'enum list|0|90|180|270 default|0');
$page           = CView::get('page', 'num');
$crop           = CView::get('crop', 'bool default|0');
$quality        = CView::get('quality', 'enum list|low|medium|high|full default|high');
$thumb          = CView::get('thumb', 'bool default|1');
$disposition    = CView::get('force_dl', 'bool default|0');
$download_raw   = CView::get('download_raw', 'bool');
$length         = CView::get('length', 'num');

CView::checkin();

if ($document_guid) {
  [$document_class, $document_id] = explode('-', $document_guid);
}

if ($thumb) {
  CThumbnail::makeThumbnail($document_id, $document_class, $profile, $page, $rotate, $crop, $quality, null, true);
}
else {
  CThumbnail::makePreview($document_id, $document_class, $page, $disposition, $download_raw, $length);
}
