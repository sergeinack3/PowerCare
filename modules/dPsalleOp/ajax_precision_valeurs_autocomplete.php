<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CPrecisionValeur;

CCanDo::checkRead();
$keywords    = CView::get('precision_valeur_id_view', 'str');
$object_guid = CView::get('object_guid', 'str');
CView::checkin();
CView::enableSlave();

$valeurs = array();
$where      = array();

if ($object_guid) {
  $object  = CMbObject::loadFromGuid($object_guid);
  $valeurs = $object->loadRefValeurs();
}

if ($keywords) {
  $where["libelle"] = " LIKE '%$keywords%'";
}

if ($valeurs) {
  $where[] = "precision_valeur_id " . CSQLDataSource::prepareNotIn(array_keys($valeurs));
}

$where["actif"] = " = '1'";

CApp::dump($where);

$order            = "libelle ASC";
$precision_valeur  = new CPrecisionValeur();
$precision_valeurs = $precision_valeur->loadGroupList($where, $order);

$smarty = new CSmartyDP();
$smarty->assign("matches", $precision_valeurs);
$smarty->display("CPrecisionValeur_autocomplete");
