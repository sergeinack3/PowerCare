<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::check();

// Chargement de la liste des icones presents dans le fichier
$icones = CAppUI::readFiles("modules/dPcabinet/images/categories", ".png");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("icones", $icones);
$smarty->display("icone_selector.tpl");
