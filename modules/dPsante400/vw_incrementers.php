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

// Liste des incrémenteurs
$incrementer  = new CIncrementer();
$incrementers = $incrementer->loadMatchingList();

// Récupération due l'incrementeur à ajouter/editer 
$incrementer = new CIncrementer;
$incrementer->load($incrementer_id);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("incrementers", $incrementers);
$smarty->assign("incrementer", $incrementer);
$smarty->display("vw_incrementers.tpl");