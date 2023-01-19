<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers;

use Exception;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\Kernel\Exception\ControllerException;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\Patients\CPaysInsee;
use Ox\Mediboard\Patients\Services\PatientSearchService;
use Ox\Mediboard\Sante400\CIdSante400;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CPatientsController
 */
class CPatientsController extends CController {
  protected static $patient_fields = [
    "nom",
    "prenom",
    "sexe",
    "naissance",
    "prenoms",
    "prenom_usuel",
    "nom_jeune_fille",
    "deces",
    "civilite",
    "rang_naissance",
    "cp_naissance",
    "lieu_naissance",
    "vip",
    "adresse",
    "ville",
    "cp",
    "pays",
    "phone_area_code",
    "tel",
    "tel2",
    "allow_sms_notification",
    "tel_pro",
    "tel_autre",
    "tel_autre_mobile",
    "email",
    "allow_email",
    "situation_famille",
    "mdv_familiale",
    "condition_hebergement",
    "niveau_etudes",
    "activite_pro",
    "profession",
    "csp",
    "fatigue_travail",
    "travail_hebdo",
    "transport_jour",
    "matricule",
    "qual_beneficiaire",
    "don_organes",
    "directives_anticipees",
    "rques",
    "tutelle",
    "commune_naissance_insee",
    "pays_naissance_insee"
  ];

    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function listPatients(RequestApi $request_api): Response
    {
        $nom                     = utf8_decode($request_api->getRequest()->get("nom"));
        $prenom                  = utf8_decode($request_api->getRequest()->get("prenom"));
        $sexe                    = $request_api->getRequest()->get("sexe");
        $naissance               = $request_api->getRequest()->get("naissance");
        $commune_naissance_insee = $request_api->getRequest()->get("communenaissanceinsee");
        $pays_naissance_insee    = $request_api->getRequest()->get("paysnaissanceinsee");
        $cp_naissance            = $request_api->getRequest()->get("cpnaissance");
        $commune_naissance       = utf8_decode($request_api->getRequest()->get("communenaissance"));
        $pays_naissance          = utf8_decode($request_api->getRequest()->get("paysnaissance"));
        $INS                     = $request_api->getRequest()->get("INS");
        $OID                     = $request_api->getRequest()->get("OID");
        $proche                  = $request_api->getRequest()->get("proche");
        $search                  = $request_api->getRequest()->get("search");

        $patient  = new CPatient();
        $patients = [];
        $offset   = $request_api->getOffset();
        $limit    = $request_api->getLimit();
        $total    = 0;

        if ($INS) {
            $ins_patient          = new CPatientINSNIR();
            $ins_patient->ins_nir = $INS;
            $ins_patient->oid     = $OID;
            if ($ins_patient->loadMatchingObject()) {
                $patient->load($ins_patient->patient_id);
            }
        }

        if ($search && !$prenom && !$nom) {
            $nom = $search;
        }

        if ($patient->_id) {
            $patients = [$patient];
            $total    = 1;
        } elseif ($nom || $prenom) {
            $patient_search_service = new PatientSearchService();

            $prenom_search = $patient_search_service->reformatResearchValue($prenom);
            $nom_search    = $patient_search_service->reformatResearchValue($nom);

            if ($nom_search) {
                $patient_search_service->addLastNameFilter(
                    $nom_search,
                    $nom_search,
                    $nom
                );
                $patient_search_service->setOrder("LOCATE('$nom_search', nom) DESC, nom, prenom, naissance");
            } else {
                $patient_search_service->setOrder('nom, prenom, naissance');
            }

            if ($prenom_search) {
                $patient_search_service->addFirstNameFilter($prenom_search, $prenom_search);
            }

            if ($sexe) {
                $patient_search_service->addSexFilter($sexe);
            }

            if ($naissance) {
                $patient_search_service->addBirthFilter($naissance);
            }

            if ($pays_naissance_insee) {
                $pays_num = (new CPaysInsee())->loadByInsee($pays_naissance_insee);
                if (isset($pays_num->numerique)) {
                    $patient_search_service->addPaysNaissanceInseeFilter($pays_num);
                }
            }

            if ($commune_naissance_insee) {
                $patient_search_service->addCommuneNaissanceInseeFilter($commune_naissance_insee);
            }

            if ($cp_naissance) {
                $patient_search_service->addCPNaissanceFilter($cp_naissance);
            }

            if ($commune_naissance) {
                $patient_search_service->addLieuNaissanceFilter($commune_naissance);
            }

            if ($pays_naissance) {
                $pays_num = CPaysInsee::getPaysNumByNomFR($pays_naissance);
                if ($pays_num && $pays_num != '000') {
                    $patient_search_service->addPaysNaissanceInseeFilter($pays_num);
                }
            }

            $patient_search_service->setLimit(intval($request_api->getLimit()));
            $patient_search_service->queryPatients();
            $patients = $patient_search_service->getPatients();
            $total    = $patient_search_service->getTotal();

            // Résultats proches
            if ($proche) {
                $patient_search_service->queryPatientsSoundex();
                $patients = $patient_search_service->getPatientsSoundex();
                $total    = $patient_search_service->getTotalSoundex();
                if (count($patients) === 0) {
                    $patient_search_service->removeFilter('cp_naissance');
                    $patient_search_service->removeFilter('commune_naissance_insee');
                    $patient_search_service->removeFilter('pays_naissance_insee');
                    $patient_search_service->removeFilter('lieu_naissance');
                    $patient_search_service->queryPatientsSoundex();
                    $patients = $patient_search_service->getPatientsSoundex();
                    $total    = $patient_search_service->getTotalSoundex();
                }
            }
        }

        $resource = Collection::createFromRequest($request_api, $patients);
        $resource->createLinksPagination($offset, $limit, $total);

        return $this->renderApiResponse($resource);
    }

  /**
   * @param RequestApi $request_api
   * @param CPatient    $patient
   *
   * @return Response
   * @throws Exception
   * @api
   */
  public function showPatient(RequestApi $request_api, CPatient $patient): Response {
    $patient->updateBMRBHReStatus();
    $patient->loadRefPatientState();
    if ($patient->pays_naissance_insee) {
        $patient->_pays_naissance_insee = CPaysInsee::getPaysByNumerique($patient->pays_naissance_insee)->nom_fr;
    }
    return $this->renderApiResponse(Item::createFromRequest($request_api, $patient));
  }

  /**
   * @param RequestApi $request_api
   *
   * @return Response
   * @api
   */
  public function showPatientById400SIH(RequestApi $request_api): Response {
    $patient_id = $request_api->getRequest()->get("patient_id");
    $cabinet_id = $request_api->getRequest()->get("cabinet_id");

    $id400 = CIdSante400::getMatch("CPatient", "ext_patient_id-{$cabinet_id}", $patient_id);

    $patient = new CPatient();
    $patient->load($id400->object_id);

    return $this->showPatient($request_api, $patient);
  }

  /**
   * @param RequestApi $request_api
   *
   * @return Response|null
   * @throws Exception
   * @api
   */
  public function addPatient(RequestApi $request_api): ?Response {
    $fields = $request_api->getContent(true, "windows-1252");

    $patient = new CPatient();

    foreach (self::$patient_fields as $_patient_field) {
      $patient->$_patient_field = isset($fields[$_patient_field]) ? $fields[$_patient_field] : "";
    }

    if ($patient->commune_naissance_insee) {
        $patient->pays_naissance_insee = CPaysInsee::NUMERIC_FRANCE;
    } else {
        if (isset($fields["pays_naissance_insee"])) {
            $patient->pays_naissance_insee = (new CPaysInsee())->loadByInsee($fields["pays_naissance_insee"])->numerique;
        }
    }

    if (isset($fields["fictif"])) {
        $patient->_fictif = $fields["fictif"];
    }

    if (isset($fields["douteux"])) {
        $patient->_douteux = $fields["douteux"];
    }

    if ($msg = $patient->store()) {
      (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
    }

    $response = $this->showPatient($request_api, $patient);
    $response->setStatusCode(Response::HTTP_CREATED);

    return $response;
  }

  /**
   * @param RequestApi $request_api
   * @param CPatient    $patient
   *
   * @return Response|null
   * @throws Exception
   * @api
   */
  public function modifyPatient(RequestApi $request_api, CPatient $patient): ?Response {
    $patient->nom       = $request_api->getRequest()->get("name") ?: $patient->nom;
    $patient->prenom    = $request_api->getRequest()->get("firstname") ?: $patient->prenom;
    $patient->naissance = $request_api->getRequest()->get("birth") ?: $patient->naissance;

    if ($msg = $patient->store()) {
      (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
    }

    return $this->showPatient($request_api, $patient);
  }

  /**
   * @param RequestApi $request_api
   * @param CPatient    $patient
   *
   * @return Response
   * @throws Exception
   * @api
   */
  public function deletePatient(RequestApi $request_api, CPatient $patient): ?Response {
    if ($msg = $patient->delete()) {
      (new ControllerException(Response::HTTP_BAD_REQUEST, $msg))->throw();
    }

    return $this->renderResponse(null, Response::HTTP_NO_CONTENT);
  }
}
