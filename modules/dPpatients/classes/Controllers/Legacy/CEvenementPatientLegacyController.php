<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CEvenementAlerteUser;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CRegleAlertePatient;
use Ox\Mediboard\Patients\CTypeEvenementPatient;
use Ox\Mediboard\Patients\Services\EvenementPatientDHEService;

/**
 * Description
 */
class CEvenementPatientLegacyController extends CLegacyController
{
    /**
     * @throws Exception|CMbException
     */
    public function printDHE(): void
    {
        $this->checkPermRead();

        $evenement_id = CView::get('evenement_id', 'ref class|CEvenementPatient');

        CView::checkin();

        $evenement_patient_dhe = new EvenementPatientDHEService($evenement_id);

        $sih_id = $evenement_patient_dhe->getSIHId();
        [$dhe_class, $dhe_id] = explode('-', $evenement_patient_dhe->getDHEGuid());
        $evenement_patient_dhe->prepareContext($dhe_class);

        if (!$sih_id || !$dhe_id) {
            throw new CMbException('CAppelSIH-no_synchronize_dhe');
        }

        $sih_type = $evenement_patient_dhe->getSIHType($sih_id);
        $data     = $evenement_patient_dhe->requestDHE($sih_id, $sih_type);
        $evenement_patient_dhe->prepareResources($data);
        $evenement_patient_dhe->printDHE($dhe_class);
    }

    /**
     * @throws Exception
     */
    public function ajax_generate_alertes_evt(): void
    {
        $function_id = CView::get("function_id", "ref class|CFunctions");

        CView::checkin();

        $user     = CMediusers::get();
        $group_id = CGroups::loadCurrent()->_id;

        $functions     = $function_id ? [] : $user->loadFonctions(PERM_EDIT, $group_id);
        $functions_ids = array_keys($functions);

        $now = CMbDT::date();

        // Récupération des règles
        $where          = [];
        $where["actif"] = " = '1'";
        $where[]        = " group_id = '$group_id' OR function_id " . CSQLDataSource::prepareIn(
                $functions_ids,
                $function_id
            );
        $regle          = new CRegleAlertePatient();
        $regles         = $regle->loadList($where);

        $users_alerte_regle = CStoredObject::massLoadBackRefs($regles, "users_alert_evt");
        CStoredObject::massLoadFwdRef($users_alerte_regle, "user_id");

        $patient         = new CPatient();
        $dossier_medical = new CDossierMedical();

        CStoredObject::massLoadFwdRef($regles, 'function_id');

        /** @var CRegleAlertePatient $_regle */
        foreach ($regles as $_regle) {
            $_regle->loadRefsUsers();

            $ds = $_regle->getDS();

            $where = [
                'patients.group_id' => $ds->prepare('= ?', $_regle->group_id ?: $_regle->loadRefFunction()->group_id)
            ];

            $ljoin = [];

            // Sexe
            if ($_regle->sexe) {
                $where["sexe"] = $ds->prepare('= ?', $_regle->sexe);
            }

            // Age
            if ($_regle->age_valeur) {
                $date_naissance              = CMbDT::transform("-$_regle->age_valeur years", $now, "%Y-%m-%d");
                $operateur                   = $_regle->age_operateur == "sup" ? "<" : ">";
                $where["patients.naissance"] = $ds->prepare($operateur . ' ?', $date_naissance);
            }

            // CIM
            if ($_regle->diagnostics || $_regle->pathologies) {
                $ljoin = [
                    "dossier_medical" => "dossier_medical.object_class = 'CPatient' AND dossier_medical.object_id = patients.patient_id",
                ];

                $where["dossier_medical.dossier_medical_id"] = "IS NOT NULL";
            }

            // Programme clinique
            if ($_regle->programme_clinique_id) {
                $ljoin["inclusion_programme"]                       = "inclusion_programme.patient_id = patients.patient_id";
                $where["inclusion_programme.programme_clinique_id"] = $ds->prepare('= ?', $_regle->programme_clinique_id);
            }

            // ALD
            if ($_regle->ald) {
                $where['patients.ald'] = "= '1'";
            }

            $patients = $patient->loadIds($where, null, null, "patients.patient_id", $ljoin);

            $date_min = CMbDT::date("-" . ($_regle->periode_refractaire + $_regle->nb_anticipation) . " days");

            foreach ($patients as $_patient_id) {
                $dossier_medical_id = CDossierMedical::dossierMedicalId($_patient_id, "CPatient");

                // Il ne faut pas prendre en compte les patients ayant des évenements avec la règle durant la période réfractaire
                $where                       = [];
                $where["dossier_medical_id"] = $ds->prepare('= ?', $dossier_medical_id);
                $where["regle_id"]           = $ds->prepare('= ?', $_regle->_id);
                $where["date"]               = $ds->prepare('>= ?', $date_min);
                $where['alerter']            = "= '1'";
                $evt                         = new CEvenementPatient();
                $evt->loadObject($where);

                if ($evt->_id) {
                    continue;
                }

                // L'intégralité des codes cim paramétré doivent être présent dans le dossier médical du patient pour le prendre en compte
                if ($_regle->diagnostics) {
                    $dossier_medical->load($dossier_medical_id);
                    $all_cim_in_dm = true;

                    foreach ($_regle->_ext_diagnostics as $code_cim => $_cim_regle) {
                        if (!isset($dossier_medical->_ext_codes_cim[$code_cim])) {
                            $all_cim_in_dm = false;
                        }
                    }
                    if (!$all_cim_in_dm) {
                        continue;
                    }
                }

                // L'intégralité des pathologies paramétrées doivent être présentes dans le dossier médical du patient pour le prendre en compte
                if ($_regle->pathologies) {
                    $dossier_medical->load($dossier_medical_id);
                    $codes_cim_pathologies_dm = CMbArray::pluck($dossier_medical->loadRefsPathologies(), 'code_cim10');
                    $all_cim_in_dm = true;

                    foreach ($_regle->_ext_pathologies as $code_cim => $_cim_regle) {
                        if (!in_array($code_cim, $codes_cim_pathologies_dm)) {
                            $all_cim_in_dm = false;
                        }
                    }

                    if (!$all_cim_in_dm) {
                        continue;
                    }
                }

                // Création de l'évenement pateint
                $evt                     = new CEvenementPatient();
                $evt->libelle            = $_regle->name;
                $evt->dossier_medical_id = $dossier_medical_id;
                $evt->date               = CMbDT::date("+$_regle->nb_anticipation days");
                $evt->alerter            = '1';
                $evt->regle_id           = $_regle->_id;
                $evt->store();


                if ($evt->_id) {
                    // Ajout de la liste des utilisateurs à alerter
                    foreach ($_regle->_ref_users as $_user) {
                        $alerte_user               = new CEvenementAlerteUser();
                        $alerte_user->object_id    = $evt->_id;
                        $alerte_user->object_class = $evt->_class;
                        $alerte_user->user_id      = $_user->_id;
                        $alerte_user->store();
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function editTypeEvenement(): void
    {
        $this->checkPermEdit();

        /** @var int $type_id */
        $type_id = CView::get('type_evenement_patient_id', 'num');

        CView::checkin();

        $type = CTypeEvenementPatient::findOrNew($type_id);

        $functions = (new CFunctions())->loadListWithPerms(PERM_EDIT, null, 'text');

        $cr = new CCompteRendu();
        $ds = $cr->getDS();

        $functions_ids = CSQLDataSource::prepareIn(CMbArray::pluck($functions, '_id'));
        $user_id       = CMediusers::get()->_id;
        $group_id      = CGroups::get()->_id;

        $where = [
            $ds->prepare('user_id = ?', $user_id) . " OR function_id $functions_ids OR " . $ds->prepare('group_id = ?', $group_id),
            'object_id'    => 'IS NULL',
            'type'         => "= 'body'",
            'actif'        => "= '1'",
            'object_class' => CSQLDataSource::prepareIn(['CEvenementPatient', 'CPatient', 'CConsultation']),
        ];

        $models = $cr->loadList($where);

        $this->renderSmarty(
            'inc_edit_types_evenement_patient',
            [
                'type'           => $type,
                'functions'      => $functions,
                'mailing_models' => $models,
            ]
        );
    }
}
