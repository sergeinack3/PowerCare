<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CAnesthPerop;
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;
use Ox\Mediboard\SalleOp\CGestePerop;
use Ox\Mediboard\SalleOp\CGestePeropPrecision;

/**
 * Legacy Controller perop gestures
 */
class GestePeropController extends CLegacyController
{
    /**
     * Edit the perop event
     *
     * @return void
     * @throws Exception
     */
    public function ajax_edit_evenement_perop(): void
    {
        $this->checkPermEdit();

        $evenement_guid = CView::get("evenement_guid", "str");
        $operation_id   = CView::get("operation_id", "ref class|COperation");
        $datetime       = CView::get("datetime", "dateTime");
        $type           = CView::get("type", "str default|perop");

        CView::checkin();

        $interv = COperation::findOrNew($operation_id);

        CAccessMedicalData::logAccess($interv);

        $interv->loadRefAnesth();
        $interv->loadRefPatient();

        if (!$datetime) {
            $datetime = CMbDT::date($interv->_datetime) . " " . CMbDT::time();
        }

        [$evenement_class, $evenement_id] = explode("-", $evenement_guid);

        /** @var CAnesthPerop $evenement */
        $evenement               = CAnesthPerop::findOrNew($evenement_id);
        $evenement->operation_id = $interv->_id;

        $evenement->loadRefsNotes();
        $evenement->loadRefCategorie();
        $geste_perop = $evenement->loadRefGestePerop();
        $geste_perop->loadRefPrecisions();
        $evenement_precision = $evenement->loadRefGestePeropPrecision();
        $evenement_precision->loadRefValeurs();

        if (!$evenement->_id) {
            $evenement->datetime = $datetime;
        }

        $evenement_category   = new CAnesthPeropCategorie();
        $evenement_categories = $evenement_category->loadGroupList();

        foreach ($evenement_categories as $_categorie) {
            $_categorie->loadRefFile();
        }

        // Lock add new or edit event
        $limit_date_min = null;

        if ($interv->entree_reveil && ($type == 'sspi')) {
            $limit_date_min = $interv->entree_reveil;
        }

        $this->renderSmarty(
            "inc_edit_evenement_perop",
            [
                "evenement"            => $evenement,
                "evenement_categories" => $evenement_categories,
                "datetime"             => $datetime,
                "operation"            => $interv,
                "limit_date_min"       => $limit_date_min,
            ]
        );
    }

    /**
     * View the precisions of a perop gesture
     *
     * @return void
     */
    public function vwGestePrecisions(): void
    {
        $this->checkPermEdit();

        $geste_perop_id = CView::get("geste_perop_id", "ref class|CGestePerop");
        CView::checkin();

        $geste_perop = CGestePerop::findOrNew($geste_perop_id);

        $precisions = $geste_perop->loadRefPrecisions();

        $this->renderSmarty(
            "inc_vw_geste_precisions",
            [
                "precisions"   => $precisions,
                "evenement" => new CAnesthPerop(),
            ]
        );
    }

    /**
     * View the precision values of a perop gesture
     *
     * composer @return void
     */
    public function vwPrecisionValeurs(): void
    {
        $this->checkPermEdit();

        $geste_perop_precision_id = CView::get("geste_perop_precision_id", "ref class|CGestePeropPrecision");
        CView::checkin();

        $precision = CGestePeropPrecision::findOrNew($geste_perop_precision_id);

        $valeurs = $precision->loadRefValeurs();

        $this->renderSmarty(
            "inc_vw_precision_valeurs",
            [
                "valeurs"   => $valeurs,
                "evenement" => new CAnesthPerop(),
            ]
        );
    }
}
