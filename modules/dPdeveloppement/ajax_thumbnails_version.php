<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Files\CThumbnail;

CCanDo::checkAdmin();

CView::checkin();

// Vérification des versions
exec("magick --version", $ret);
if (!$ret) {
  exec("convert --version", $ret);
}
$imagick_exists = extension_loaded('Imagick');
$gd_exists      = extension_loaded('GD');
$imagine        = CThumbnail::checkImagineExists();

$smarty = new CSmartyDP();
$smarty->assign('version_imagemagick', $ret);
$smarty->assign('imagine', $imagine);
$smarty->assign('imagick_exists', $imagick_exists);
$smarty->assign('gd_exists', $gd_exists);
$smarty->display('inc_version_thumbnail_tester.tpl');