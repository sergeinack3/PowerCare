<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Files\CThumbnail;

CCanDo::check();

$file_id     = CView::get('file_id', 'ref class|CFile');
$cr_id       = CView::get('compte_rendu_id', 'ref class|CCompteRendu');
$disposition = CView::get('force_dl', 'bool default|0');
$thumb       = CView::get('phpThumb', 'bool default|1');
$width       = CView::get('w', 'num');
$height      = CView::get('h', 'num');
$crop        = CView::get('zc', 'bool default|0');
$page        = CView::get('sfn', 'num default|1');

CView::checkin();

$profile = 'small';
if ($width > CThumbnail::PROFILES['small']['w'] || $height > CThumbnail::PROFILES['small']['h']) {
  $profile = 'medium';
}
if ($width > CThumbnail::PROFILES['medium']['w'] || $height > CThumbnail::PROFILES['medium']['w']) {
  $profile = 'large';
}

$document_id = null;
$document_class = '';

// Si rien de précisé affichage de medifile
if (!$file_id && !$cr_id) {
  $document_class = 'medifile';
}
elseif ($file_id) {
  $document_id = $file_id;
  $document_class = 'CFile';
}
else {
  $document_id = $cr_id;
  $document_class = 'CCompteRendu';
}

if ($thumb) {
  CThumbnail::makeThumbnail($document_id, $document_class, $profile, $page, 0, $crop, 'high');
}
else {
  CThumbnail::makePreview($document_id, $document_class, $page, $disposition);
}

