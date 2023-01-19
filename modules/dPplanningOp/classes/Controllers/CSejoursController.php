<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Controllers;

use DateTime;
use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\Kernel\Exception\ControllerException;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\FieldsSIH;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CSejoursController
 */
class CSejoursController extends CController
{
    use FieldsSIH;

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
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function listSejours(RequestApi $request_api): Response
    {
        $sejour  = new CSejour();
        $sejours = $sejour->loadListFromRequestApi($request_api);

        //@todo : Traitement back massload etc

        $total = $sejour->countListFromRequestApi($request_api);

        $resource = Collection::createFromRequest($request_api, $sejours);
        $resource->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $total);

        return $this->renderApiResponse($resource);
    }

    /**
     * @param RequestApi $request_api
     * @param CSejour     $sejour
     *
     * @return Response
     * @throws ApiException
     * @api
     */
    public function showSejour(RequestApi $request_api, CSejour $sejour): Response
    {
        $resource = Item::createFromRequest($request_api, $sejour);

        return $this->renderApiResponse($resource);
    }

    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @api
     */
    public function addSejour(RequestApi $request_api, CSejour $sejour = null): Response
    {
        $fields = $request_api->getContent(true, "windows-1252");

        $ext_patient_id = $request_api->getRequest()->get("tamm_patient_id");
        $ext_cabinet_id = $request_api->getRequest()->get("sih_cabinet_id");
        $group_id       = $request_api->getRequest()->get("sih_group_id");

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

        foreach (self::$patient_fields as $_patient_field) {
            $patient->{preg_replace("/patient_/", "", $_patient_field)} = ${$_patient_field};
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

        if ($msg = $patient->check()) {
            (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
        }

        if ($msg = $sejour->check()) {
            (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
        }

        if ($msg = $sejour->store()) {
            (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
        }

        if ($msg = $patient->store()) {
            (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
        }

        $this->formatFormFields($sejour);

        $resource = Item::createFromRequest($request_api, $sejour)->setModelFieldsets(
            [
                CSejour::FIELDSET_DEFAULT,
                CSejour::FIELDSET_ADMISSION,
            ]
        );

        return $this->renderApiResponse($resource)->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param RequestApi $request_api
     * @param CSejour     $sejour
     *
     * @return Response
     * @api
     */
    public function modifySejour(RequestApi $request_api, CSejour $sejour): Response
    {
        $sejour->_date_entree_prevue = null;
        $sejour->_date_sortie_prevue = null;

        return $this->addSejour($request_api, $sejour);
    }

    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @api
     */

    public function getFields(RequestApi $request_api): Response
    {
        $sejour = CSejour::findOrNew($request_api->getRequest()->get('sejour_id'));

        $template = new CTemplateManager();

        if (!$sejour->_id) {
            $template->valueMode = false;
        }

        $sejour->fillTemplate($template);

        $fields = $this->computeFields($template->sections);

        return $this->renderJsonResponse(json_encode($fields));
    }

    /**
     * @param CSejour $sejour
     */
    protected function formatFormFields(CSejour $sejour): void
    {
        [$sejour->_libelle, $libelle_other] = CSejour::getLibelles($sejour);
    }
}
