<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;
use Ox\Mediboard\Labo\CPackExamensLabo;

$do = new CDoObjectAddEdit("CPrescriptionLaboExamen");

$pack = new CPackExamensLabo();
$pack->load($_POST["_pack_examens_labo_id"]);
$pack->loadRefs();

foreach ($pack->_ref_items_examen_labo as $item) {
  $_POST["examen_labo_id"]       = $item->_ref_examen_labo->_id;
  $_POST["pack_examens_labo_id"] = $pack->_id;
  $do->doBind();
  $do->doStore();
}

$do->ajax = 1;
$do->doRedirect();
