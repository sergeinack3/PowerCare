<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CActeNGAP;

CCanDo::checkRead();

$acte = new CActeNGAP;

$acte_ngap_id = CView::get('acte_ngap_id', 'ref class|CActeNGAP');
if ($acte_ngap_id) {
  $acte->load($acte_ngap_id);
}

$acte->quantite         = CView::get("quantite", "num default|1");
$acte->code             = CView::get("code", 'str');
$acte->coefficient      = CView::get("coefficient", "float default|1");
$acte->demi             = CView::get("demi", "bool default|0");
$acte->complement       = CView::get("complement", 'enum list|N|F|U');
$acte->executant_id     = CView::get('executant_id', 'ref class|CMediusers');
$acte->gratuit          = CView::get('gratuit', 'bool default|0');
$acte->execution        = CView::get('execution', array('dateTime', 'default' => CMbDT::dateTime()));
$acte->taux_abattement  = CView::get('taux_abattement', 'float');

$disabled = CView::get('disabled', 'bool default|0');
$view = CView::get('view', 'str');

CView::checkin();

$acte->checkEntentePrealable();
$acte->updateMontantBase();
$acte->getLibelle();
$acte->getLieu();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("acte"  , $acte);
$smarty->assign('disabled', $disabled);
$smarty->assign('view', $view);
$smarty->display("inc_vw_tarif_ngap.tpl");
