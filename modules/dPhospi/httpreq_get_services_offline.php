<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

// Liste des Etablissements selon Permissions
$etablissements = new CMediusers();
$etablissements = $etablissements->loadEtablissements(PERM_READ);

// Récupération des services
$order              = "group_id, nom";
$where              = array();
$where["group_id"]  = CSQLDataSource::prepareIn(array_keys($etablissements));
$where["cancelled"] = "= '0'";
$services           = new CService();
$services           = $services->loadList($where, $order);


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("etablissements", $etablissements);
$smarty->assign("services", $services);

$smarty->display("httpreq_get_services_offline.tpl");
