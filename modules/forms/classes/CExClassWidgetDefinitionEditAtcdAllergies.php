<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms;

use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Description
 */
class CExClassWidgetDefinitionEditAtcdAllergies extends CExClassWidgetDefinition {
  public $name = "EditAtcdAllergies";

  public $template_name = "inc_edit_atcd_allergies";

  public $default_dimensions = array(
    "width"  => 300,
    "height" => 150,
  );

  /**
   * @inheritdoc
   */
  function prepareTemplate(CExObject $ex_object, $mode = "normal") {
    $tpl = $this->template;

    $sejour  = $ex_object->getReferenceObject("CSejour");
    $patient = $ex_object->getReferenceObject("CPatient") ?: new CPatient();

    $tpl->assign("antecedent", new CAntecedent());
    $tpl->assign("patient", $patient);
    $tpl->assign("_is_anesth", CMediusers::get()->isAnesth());
    $tpl->assign("sejour_id", $sejour ? $sejour->_id : null);
    $tpl->assign("addform", mt_rand());
  }
}
