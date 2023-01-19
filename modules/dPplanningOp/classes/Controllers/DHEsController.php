<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Controllers;

use Exception;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\Kernel\Exception\ControllerException;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DHEsController
 */
class DHEsController extends CController
{
    /** @var string[] */
    protected static $intervention_fields = [
        "intervention_chir_id",
        "intervention_protocole_id",
        "intervention_plageop_id",
        //    "intervention_actes",
        "intervention_cote",
        "intervention_temp_operation",
        "intervention_date",
        "intervention_horaire_voulu",
        "intervention_libelle",
        "intervention_urgence",
        "intervention_materiel",
        "intervention_rques",
        //    "intervention_preop",
        //    "intervention_postop",
        //    "intervention_nettoyage",
        "intervention_duree_bio_nettoyage",
        "intervention_duree_uscpo",
        "intervention_exam_extempo",
        "intervention_examen",
        "intervention_exam_per_op",
        "intervention_conventionne",
        "intervention_depassement",
        "intervention_forfait",
        "intervention_fournitures",
        "intervention_reglement_dh_chir",
        "intervention_anesth_id",
        "intervention_type_anesth",
    ];

    /** @var string[] */
    protected static $sejour_fields = [
        "sejour_entree",
        "sejour_sortie",
        "sejour_type",
        "sejour_libelle",
        "sejour_patient_id",
        "sejour_praticien_id",
        "sejour_service_id",
        "sejour_charge_id",
        "sejour_uf_medicale_id",
        "sejour_uf_soins_id",
        "sejour_rques",
        "sejour_facturable",
        "sejour_ald",
        "sejour_aide_organisee",
        "sejour_handicap",
        "sejour_presence_confidentielle",
        "sejour_frais_sejour",
        "sejour_reglement_frais_sejour",
        "sejour_isolement",
        "sejour_nuit_convenance",
        "sejour_hospit_de_jour",
        "sejour_consult_accomp",
        "sejour_ATNC",
        "sejour_convalescence",
    ];

    /** @var string[] */
    protected static $patient_fields = [
        "patient_tutelle",
    ];

    /**
     * @param RequestApi     $request_api
     * @param COperation|null $intervention
     * @param CSejour|null    $sejour
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function addDHE(RequestApi $request_api, COperation $intervention = null, CSejour $sejour = null): Response
    {
        $fields = $request_api->getContent(true, "windows-1252");

        $ext_patient_id = $request_api->getRequest()->get("tamm_patient_id");
        $ext_cabinet_id = $request_api->getRequest()->get("sih_cabinet_id");
        $group_id       = $request_api->getRequest()->get("sih_group_id");

        $intervention   = $intervention ?: new COperation();
        $plageop_id     = $intervention->plageop_id;

        foreach (self::$intervention_fields as $_intervention_field) {
            ${$_intervention_field} = isset($fields[$_intervention_field]) ? $fields[$_intervention_field] : "";
        }

        foreach (self::$sejour_fields as $_sejour_field) {
            ${$_sejour_field} = isset($fields[$_sejour_field]) ? $fields[$_sejour_field] : "";
        }

        foreach (self::$patient_fields as $_patient_field) {
            ${$_patient_field} = isset($fields[$_patient_field]) ? $fields[$_patient_field] : "";
        }
        $patient = CPatient::findOrNew($sejour_patient_id);

        if (!$patient->_id) {
            (new ControllerException(Response::HTTP_BAD_REQUEST, CAppUI::tr("CPatient-Patient not found")))->throw();
        }

        $sejour = $sejour ?: new CSejour();

        foreach (self::$sejour_fields as $_sejour_field) {
            $sejour->{preg_replace("/sejour_/", "", $_sejour_field)} = ${$_sejour_field};
        }

        $sejour->group_id        = $group_id;
        $sejour->entree_prevue   = $sejour->entree;
        $sejour->sortie_prevue   = $sejour->sortie;
        $sejour->_ext_patient_id = $ext_patient_id;
        $sejour->_ext_cabinet_id = $ext_cabinet_id;

        $intervention = $intervention ?: new COperation();

        foreach (self::$intervention_fields as $_intervention_field) {
            $intervention->{preg_replace("/intervention_/", "", $_intervention_field)} = ${$_intervention_field};
        }
        $intervention->presence_preop  = isset($fields["intervention_presence_preop"]) ? $fields["intervention_presence_preop"] : "";
        $intervention->presence_postop = isset($fields["intervention_presence_postop"]) ? $fields["intervention_presence_postop"] : "";
        $intervention->codes_ccam      = isset($fields["intervention_actes"]) ? $fields["intervention_actes"] : "";
        $intervention->_time_op        = null;
        $intervention->_codes_ccam     = [];
        $intervention->_ext_patient_id = $ext_patient_id;
        $intervention->_ext_cabinet_id = $ext_cabinet_id;
        $intervention->plageop_id      = $intervention->plageop_id ?: $plageop_id;
        $intervention->date            = $intervention->loadRefPlageOp()->date;
        $intervention->salle_id        = $intervention->_ref_plageop->salle_id;

        if ($intervention->_id) {
            // Pour le check du séjour alors que l'intervention n'est pas encore modifiée,
            // on injecte l'intervention avec la nouvelle date dans la collection d'interventions du séjour
            $intervention->updateDatetimes();
            $sejour->_ref_operations[$intervention->_id] = $intervention;
        }

        foreach (self::$patient_fields as $_patient_field) {
            $patient->{preg_replace("/patient_/", "", $_patient_field)} = ${$_patient_field};
        }

        if ($msg = $sejour->check()) {
            (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
        }

        $intervention->_ref_sejour = $sejour;
        if ($msg = $intervention->warningBounds()) {
            (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
        }

        if ($msg = $patient->check()) {
            (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
        }

        if ($msg = $sejour->store()) {
            (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
        }

        $intervention->sejour_id = $sejour->_id;

        if ($msg = $intervention->store()) {
            (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
        }

        if ($msg = $patient->store()) {
            (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
        }

        $this->formatFormFields($intervention, $sejour);

        $resource = Item::createFromRequest($request_api, $intervention);

        return $this->renderApiResponse($resource)->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param RequestApi $request_api
     * @param COperation  $operation
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function modifyDHE(RequestApi $request_api, COperation $operation): Response
    {
        $sejour = $operation->loadRefSejour();

        $sejour->_date_entree_prevue = null;
        $sejour->_date_sortie_prevue = null;

        return $this->addDHE($request_api, $operation, $sejour);
    }

    /**
     * @param RequestApi $request_api
     * @param COperation  $operation
     *
     * @return Response
     * @api
     */
    public function showDHE(RequestApi $request_api, COperation $operation): Response
    {
        return $this->renderApiResponse(Item::createFromRequest($request_api, $operation));
    }

    /**
     * @param COperation $intervention
     * @param CSejour    $sejour
     */
    protected function formatFormFields(COperation $intervention, CSejour $sejour): void
    {
        [$intervention->_libelle_interv, $intervention->_libelle_sejour] = CSejour::getLibelles(
            $intervention,
            $sejour
        );
        $intervention->_entree_sejour = $sejour->entree_prevue;
    }
}
