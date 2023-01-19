<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Hprim21\CEchangeHprim21;

/**
 * Rafraichissement d'un échange Hprim21
 */
CCanDo::checkRead();

$echg_hprim21_id = CValue::get("echange_hprim21_id");

// Chargement de l'objet
$echg_hprim21 = new CEchangeHprim21();
$echg_hprim21->load($echg_hprim21_id);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("object", $echg_hprim21);
$smarty->display("inc_echange_hprim21.tpl");

