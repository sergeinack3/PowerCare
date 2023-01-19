<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CLegalEntity;
use Ox\Mediboard\Etablissement\CLegalStatus;

CCanDo::checkRead();

// Récupération de l'entité juridique sélectionée
$legal_entity = new CLegalEntity();
$legal_entity->load(CValue::getOrSession("legal_entity_id"));
$legal_entity->loadRefUser();

$legal_status = array();
if (CSQLDataSource::get('sae', true)) {
  $legal_status = new CLegalStatus();
  $legal_status = $legal_status->loadList();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("legal_entity" , $legal_entity);
$smarty->assign("legal_status" , $legal_status);

$smarty->display("inc_vw_legal_entity.tpl");