<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;

CCanDo::checkAdmin();

$csv = new CCSVFile();

$csv->writeLine(
  array(
    "Code",
    "Libellé",
    "Type de séjour",
    "Type de prise en charge",
    "Actif"
  )
);

$charge = new CChargePriceIndicator();

foreach ($charge->loadGroupList() as $_charge) {
  $csv->writeLine(
    array(
      $_charge->code,
      $_charge->libelle,
      $_charge->type,
      $_charge->type_pec,
      $_charge->actif
    )
  );
}

$group = CGroups::get();

$csv->stream(CAppUI::tr("CChargePriceIndicator") ." - {$group->_view}");