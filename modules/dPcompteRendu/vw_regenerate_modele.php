<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;

CCanDo::checkAdmin();
CView::checkin();

$file = new CFile();
$file->object_class = "CCompteRendu";
$file->doc_size = 0;

$files_empty = $file->countMatchingList();

$smarty = new CSmartyDP();
$smarty->assign("files_empty", $files_empty);
$smarty->display("vw_regenerate_modele");