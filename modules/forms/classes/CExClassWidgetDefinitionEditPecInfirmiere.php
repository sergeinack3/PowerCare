<?php

/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms;

use Exception;
use Ox\Core\CAppUI;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Forms\CExObject;
use Ox\Mediboard\Urgences\CRPU;

class CExClassWidgetDefinitionEditPecInfirmiere extends CExClassWidgetDefinition
{
    public $name = "EditPecInfirmiere";

    public $template_name = "inc_edit_pec_infirmiere";

    public $default_dimensions = [
        "width"  => 300,
        "height" => 200,
    ];

    /**
     * @inheritdoc
     * @throws Exception
     */
    function prepareTemplate(CExObject $ex_object, $mode = "normal")
    {
        $tpl = $this->template;

        /** @var CRPU $rpu */
        $rpu = $ex_object->getReferenceObject("CRPU") ?: new CRPU();
        $rpu->loadRefSejour();
        $rpu->loadRefIDEResponsable();
        $rpu->loadRefIOA();

        $group        = CGroups::get();
        $user         = CMediusers::get();
        $responsables = CAppUI::conf("dPurgences only_prat_responsable") ?
            $user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true) :
            $user->loadListFromType(null, PERM_READ, $group->service_urgences_id, null, true, true);

        $tpl->assign("modal", false);
        $tpl->assign("view_mode", "infirmier");
        $tpl->assign("listResponsables", $responsables);
        $tpl->assign("submit_ajax", "");
        $tpl->assign("rpu", $rpu);
    }
}
