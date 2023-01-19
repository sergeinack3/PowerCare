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
use Ox\Mediboard\Files\CFilesCategory;

CCanDo::checkEdit();
$file_id = CView::get("file_id", "num");
CView::checkin();

$file = new CFile();
$file->load($file_id);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("file"            , $file);
$smarty->assign("files_categories", CFilesCategory::listCatClass($file->object_class));

$smarty->display("inc_edit_file.tpl");