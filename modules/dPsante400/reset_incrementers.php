<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CIncrementer;

CCanDo::checkAdmin();

$incrementer_id = CValue::get("incrementer_id");
$new_value      = CValue::get("new_value");

$incrementer = new CIncrementer();
$incrementer->load($incrementer_id);

// Si incrémenteur pas trouvé ou qu'il a déjà une valeur supérieure à ce qu'on veut lui mettre (évite le reset en double)
if (!$incrementer->_id || $incrementer->value >= $new_value) {
  echo "Impossible de reset l'incrémenteur #$incrementer_id de la valeur '$incrementer->value' à la valeur '$new_value'\n";

  return;
}

$incrementer->value = $new_value;
$incrementer->store();
