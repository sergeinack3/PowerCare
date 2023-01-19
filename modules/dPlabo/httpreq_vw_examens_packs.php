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
use Ox\Mediboard\Labo\CPackExamensLabo;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$user = CMediusers::get();

$pack_examens_labo_id = CValue::getOrSession("pack_examens_labo_id");

// Chargement du pack demandé
$pack = new CPackExamensLabo();
$pack->load($pack_examens_labo_id);
$pack->loadRefs();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("pack", $pack);

$smarty->display("inc_vw_examens_packs");
