<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\Transformations\CTransformation;

/**
 * Actor domain aed
 */
CCanDo::checkAdmin();

$transformation_id_move = CValue::post("transformation_id_move");
$direction              = CValue::post("direction");

$transformation = new CTransformation();
$transformation->load($transformation_id_move);

switch ($direction) {
  case "up":
    $transformation->rank--;
    break;

  case "down":
    $transformation->rank++;
    break;

  default:
}

$transf_to_move = new CTransformation();
$transf_to_move->actor_class = $transformation->actor_class;
$transf_to_move->actor_id    = $transformation->actor_id;
$transf_to_move->rank        = $transformation->rank;
$transf_to_move->loadMatchingObject();

if ($transf_to_move->_id) {
  $direction == "up" ? $transf_to_move->rank++ : $transf_to_move->rank--;
  $transf_to_move->store();
}

$transformation->store();

/** @var CInteropActor $actor */
$actor = new $transformation->actor_class;
$actor->load($transformation->actor_id);

/** @var CTransformation[] $transformations */
$transformations = $actor->loadBackRefs("actor_transformations", "rank");

$i = 1;
foreach ($transformations as $_transformation) {
  $_transformation->rank = $i;
  $_transformation->store();
  $i++;
}

CAppUI::stepAjax("CTransformation-msg-Move rank done");

CApp::rip();
