<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$mode_class = CView::get("mode_class", "str");

CView::checkin();

$csv = new CCSVFile();

$csv->writeLine(
  array(
    "Code",
    "Libellé",
    "Mode",
    "Actif",
      ($mode_class === "CModeEntreeSejour") ? "Provenance" : null
  )
);

$mode = new $mode_class();

foreach ($mode->loadGroupList() as $_mode) {
  $csv->writeLine(
    array(
      $_mode->code,
      $_mode->libelle,
      $_mode->mode,
      $_mode->actif,
      ($mode_class === "CModeEntreeSejour") ? $_mode->provenance : null
    )
  );
}

$group = CGroups::get();

$csv->stream(CAppUI::tr($mode_class) . " - {$group->_view}");
