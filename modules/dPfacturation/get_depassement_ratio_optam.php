<?php 
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;

CCanDo::checkRead();

$user_id = CView::getRefCheckRead('user_id', 'ref class|CMediusers');
$date_souscription = CView::get('date_souscription_optam', 'date');

CView::checkin();

$ljoin = array(
  '`plageconsult` AS p ON p.`plageconsult_id` = c.`plageconsult_id`'
);

$where = array(
  "p.`chir_id` = $user_id",
  "p.`date` >= '$date_souscription'"
);

$ds = CSQLDataSource::get('std');

$query = new CRequest();
$query->addSelect(array('SUM(c.`secteur2`) AS depassements', 'SUM(c.`secteur1`) AS bases'));
$query->addTable('`consultation` AS c');
$query->addLJoin($ljoin);
$query->addWhere($where);
$total = $ds->loadHash($query->makeSelect());

$query = new CRequest();
$query->addSelect('COUNT(*) as total');
$query->addTable('`consultation` AS c');
$query->addLJoin($ljoin);
$query->addWhere($where);
$query->addWhere('c.`secteur2` = 0');
$count_tarif_base = $ds->loadResult($query->makeSelect());

$query = new CRequest();
$query->addSelect('COUNT(*) as total');
$query->addTable('`consultation` AS c');
$query->addLJoin($ljoin);
$query->addWhere($where);
$count_consult = $ds->loadResult($query->makeSelect());

$ratio_montant_depassement = 0;
if ($total['depassements'] > 0) {
  $ratio_montant_depassement = round(($total['depassements'] / $total['bases']) * 100, 2);
}

$ratio_consult_tarif_base = 0;
if ($count_consult > 0) {
  $ratio_consult_tarif_base = round(($count_tarif_base / $count_consult) * 100, 2);
}

$seuil_ratio_montant_depassement = CMbDT::date() >= '2018-01-01' ? 31.60 : 32.00;
$seuil_ratio_consult_tarif_base = 38.30;

$smarty = new CSmartyDP();
$smarty->assign('ratio_montant_depassement', $ratio_montant_depassement);
$smarty->assign('ratio_consult_tarif_base', $ratio_consult_tarif_base);
$smarty->assign('seuil_ratio_montant_depassement', $seuil_ratio_montant_depassement);
$smarty->assign('seuil_ratio_consult_tarif_base', $seuil_ratio_consult_tarif_base);
$smarty->display('depassement_ratio_optam');
