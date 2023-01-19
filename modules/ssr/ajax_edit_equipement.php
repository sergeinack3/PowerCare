<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Ssr\CEquipement;
use Ox\Mediboard\Ssr\CPlateauTechnique;

CCanDo::checkRead();

// Plateau du contexte
$plateau = new CPlateauTechnique;
$plateau->load(CValue::get("plateau_id"));
$plateau->loadRefsEquipements(false);

// Equipement à editer
$equipement = new CEquipement;
$equipement->load(CValue::get("equipement_id"));
$equipement->plateau_id = $plateau->_id;
$equipement->loadRefsNotes();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("equipement", $equipement);
$smarty->assign("plateau", $plateau);

$smarty->display("inc_edit_equipement");
