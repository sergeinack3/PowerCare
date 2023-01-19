<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms;

use Ox\Core\Module\CModule;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Description
 */
class CExClassWidgetDefinitionEditTraitements extends CExClassWidgetDefinition {
  public $name = "EditTraitements";

  public $template_name = "inc_edit_traitements";

  public $default_dimensions = array(
    "width"  => 300,
    "height" => 200,
  );

  /**
   * @inheritdoc
   */
  function prepareTemplate(CExObject $ex_object, $mode = "normal") {
    $tpl = $this->template;

    $isPrescriptionInstalled = CModule::getActive("dPprescription") && CPrescription::isMPMActive();

    if ($isPrescriptionInstalled) {
      $tpl->assign("line", new CPrescriptionLineMedicament());
    }

    $user = CMediusers::get();

    $sejour  = $ex_object->getReferenceObject("CSejour");
    $patient = $ex_object->getReferenceObject("CPatient") ?: new CPatient();

    $tpl->assign("traitement", new CTraitement());
    $tpl->assign("patient", $patient);
    $tpl->assign("_is_anesth", $user->isAnesth());
    $tpl->assign("userSel", $user);
    $tpl->assign("sejour_id", $sejour ? $sejour->_id : null);
    $tpl->assign("addform", mt_rand());
  }
}
