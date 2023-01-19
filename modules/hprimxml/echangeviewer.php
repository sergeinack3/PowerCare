<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Hprimxml\CEchangeHprim;
use Ox\Interop\Hprimxml\Event\CHPrimXMLEventPatient;

/**
 * Exchange viewer
 */
CCanDo::checkRead();

$echange_hprim_id = CValue::get("echange_hprim_id");

$echange_hprim = new CEchangeHprim();
$echange_hprim->load($echange_hprim_id);

$evt             = new CHPrimXMLEventPatient();
$domGetEvenement = $evt->getHPrimXMLEvenements($this->_message);
$domGetEvenement->formatOutput = true;
$doc_errors_msg  = @$domGetEvenement->schemaValidate(null, true, false);

$echange_hprim->_message = utf8_encode($domGetEvenement->saveXML());

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("echange_hprim", $echange_hprim);
$smarty->display("echangeviewer.tpl");

