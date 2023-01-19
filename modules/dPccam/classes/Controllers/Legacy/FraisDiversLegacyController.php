<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CFraisDivers;
use Ox\Mediboard\Ccam\CFraisDiversType;

class FraisDiversLegacyController extends CLegacyController
{
    public function viewFraisDiversTypes(): void
    {
        $this->checkPermAdmin();
        $frais_divers_type_id = CView::get("frais_divers_type_id", 'ref class|CFraisDiversType', true);
        CView::checkin();

        $type = new CFraisDiversType();
        $type->load($frais_divers_type_id);

        $list_types = $type->loadList(null, "code");

        $this->renderSmarty('vw_idx_frais_divers_types.tpl', [
            'type' => $type,
            'list_types' => $list_types,
        ]);
    }

    public function refreshFraisDivers(): void
    {
        $this->checkPermEdit();

        $object_guid = CView::get("object_guid", 'str');

        CView::checkin();

        /* @var CCodable $object*/
        $object = CMbObject::loadFromGuid($object_guid);

        CAccessMedicalData::logAccess($object);

        $object->loadRefsFraisDivers();

        $frais_divers = new CFraisDivers();
        $frais_divers->setObject($object);
        $frais_divers->quantite = 1;
        $frais_divers->coefficient = 1;
        $frais_divers->num_facture = 1;
        if ($object->_class == "CConsultation" && $object->valide) {
            $object->loadRefFacture();
            $frais_divers->num_facture = count($object->_ref_factures) + 1;
        }
        $frais_divers->loadListExecutants();
        $frais_divers->loadExecution();

        $this->renderSmarty('inc_form_add_frais_divers.tpl', [
            'object' => $object,
            'frais_divers' => $frais_divers,
        ]);
    }
}
