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

CCanDo::checkEdit();

$file_id = CView::get("file_id", "num");

$file = new CFile();
$file->load($file_id);

CView::checkin();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("file", $file);

$smarty->display("inc_rename_file.tpl");