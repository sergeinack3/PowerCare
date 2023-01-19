<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CListeChoix;

/**
 * Modification de liste de choix
 */
CCanDo::checkRead();

// Liste sélectionnée
$liste_id = CValue::getOrSession("liste_id");
$liste = new CListeChoix();
$liste->load($liste_id); 

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("liste", $liste);

$smarty->display("inc_list_choix");
