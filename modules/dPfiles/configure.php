<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Files\CFile;

CCanDo::checkAdmin();

$file = new CFile();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("listNbFiles", range(1, 10));
$smarty->assign("today", CMbDT::date());
$smarty->assign("nb_files", $file->countList() / 100);
$smarty->display("configure.tpl");

