<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Ccam\CModelCodage;

CCanDo::checkEdit();

$codage_id = CValue::get('codage_id');

/** @var CModelCodage $codage */
$codage = CModelCodage::loadFromGuid("CModelCodage-{$codage_id}");

$data = $codage->getTarifData();

echo json_encode($data);