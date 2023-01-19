<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Labo\CCatalogueLabo;

CCanDo::checkRead();

$catalogue_labo_id = CValue::getOrSession("catalogue_labo_id");

// Chargement du catalogue demandé
$catalogue = new CCatalogueLabo();
$catalogue->load($catalogue_labo_id);
$catalogue->loadRefs();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("search", 0);
$smarty->assign("catalogue", $catalogue);

$smarty->display("inc_vw_examens_catalogues");
