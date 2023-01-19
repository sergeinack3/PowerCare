<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CIncrementer;

CCanDo::checkAdmin();

$incrementer_id = CValue::getOrSession("incrementer_id");

// Liste des incr�menteurs
$incrementer  = new CIncrementer();
$incrementers = $incrementer->loadMatchingList();

// R�cup�ration due l'incrementeur � ajouter/editer 
$incrementer = new CIncrementer;
$incrementer->load($incrementer_id);

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("incrementers", $incrementers);
$smarty->assign("incrementer", $incrementer);
$smarty->display("vw_incrementers.tpl");