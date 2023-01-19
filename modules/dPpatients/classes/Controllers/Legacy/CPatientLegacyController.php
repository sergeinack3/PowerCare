<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\OpenData\CImportConflict;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientState;
use Ox\Mediboard\Patients\Services\PatientSearchService;
use Ox\Mediboard\PlanningOp\CSejour;

class CPatientLegacyController extends CLegacyController
{
    public function searchPatient(): void
    {
        $this->checkPermRead();

        // Chargement du patient sélectionné
        $patient_id = CView::get("patient_id", 'ref class|CPatient', true);
        $patient    = new CPatient();

        if (CView::get("new", 'bool default|0')) {
            $patient->load();
            CView::setSession("patient_id", null);
            CView::setSession("selClass", null);
            CView::setSession("selKey", null);
        } else {
            $patient->load($patient_id);
        }


        // Récuperation des patients recherchés
        $patient_nom             = trim(CView::get("nom", 'str', true));
        $patient_prenom          = trim(CView::get("prenom", 'str', true));
        $patient_ville           = CView::get("ville", 'str');
        $patient_cp              = CView::get("cp", 'str');
        $patient_day             = CView::get("Date_Day", 'str', true);
        $patient_month           = CView::get("Date_Month", 'str', true);
        $patient_year            = CView::get("Date_Year", 'str', true);
        $patient_naissance       = null;
        $patient_ipp             = CView::get("patient_ipp", 'str');
        $patient_nda             = trim(CView::get("patient_nda", 'str'));
        $patient_matricule       = CView::get("matricule", "str");
        $useVitale               = CView::get(
            "useVitale",
            'bool default|' . (CModule::getActive("fse") && CAppUI::pref('LogicielLectureVitale') !== 'none' ? 1 : 0)
        );
        $parturiente             = CView::get("parturiente", "bool default|0");
        $prat_id                 = CView::get("prat_id", 'ref class|CMediusers');
        $patient_sexe            = CView::get("sexe", 'enum list|m|f');
        $see_link_prat           = CView::get("see_link_prat", 'bool default|0');

        $start = (int)CView::get('start', 'num default|0');
        $mode  = CView::get("mode", 'enum list|search|board|selector default|search');

        $patient_search_service = new PatientSearchService();

        $paginate = CAppUI::gconf('dPpatients CPatient search_paging');

        $patient_nom_search    = null;
        $patient_prenom_search = null;

        $mediuser = CMediusers::get();

        $total     = 0;

        $curr_group_id = CGroups::loadCurrent()->_id;

        $patVitale = new CPatient();

        $limit_char_search = null;


        if ($patient_ipp || $patient_nda) {
            $patient = new CPatient();

            $patient->getByIPPNDA($patient_ipp, $patient_nda);

            if ($patient->_id) {
                CView::setSession("patient_id", $patient->_id);
                $patient_search_service->addPatient($patient);
            }

            CView::checkin();
        } else {
            $use_function_distinct = CAppUI::isCabinet() && !$mediuser->isAdmin();
            $use_group_distinct    = CAppUI::isGroup() && !$mediuser->isAdmin();

            $function_id = $use_function_distinct ? CFunctions::getCurrent()->_id : null;
            // Recheche par traits classiques
            if (
                $useVitale && CAppUI::pref('LogicielLectureVitale') === 'none'
                && CModule::getActive("fse") && CAppUI::pref('LogicielFSE') !== 'jfse'
            ) {
                // Champs vitale
                $cv = CFseFactory::createCV();

                if ($cv) {
                    $cv->getPropertiesFromVitale($patVitale);
                    $patVitale->updateFormFields();
                    $patient_nom    = $patVitale->nom;
                    $patient_prenom = $patVitale->prenom;
                    CView::setSession("nom", $patVitale->nom);
                    CView::setSession("prenom", $patVitale->prenom);
                    $cv->loadFromIdVitale($patVitale);
                }
            }

            /* The checkin is made after some data has been put on session, and before the SQL query */
            CView::checkin();

            $lenSearchConfig        = false; // Not enough char in string to perform the limited search

            // Because of \w and \W don't match characters with diacritics
            $patient_prenom_search = $patient_search_service->reformatResearchValue($patient_prenom);
            $patient_nom_search    = $patient_search_service->reformatResearchValue($patient_nom);

            // Limitation de la recherche par config :
            $patient_nom_search_limited    = $patient_nom_search;
            $patient_prenom_search_limited = $patient_prenom_search;

            if ($limit_char_search = CAppUI::gconf("dPpatients CPatient limit_char_search")) {
                // Not enough characters
                if (strlen($patient_prenom_search) < $limit_char_search && strlen($patient_nom_search) < $limit_char_search) {
                    $lenSearchConfig = true;
                }

                $patient_nom_search_limited    = substr($patient_nom_search, 0, $limit_char_search);
                $patient_prenom_search_limited = substr($patient_prenom_search, 0, $limit_char_search);
            }

            if ($patient_nom_search) {
                $patient_search_service->addLastNameFilter($patient_nom_search, $patient_nom_search_limited, $patient_nom);
                $patient_search_service->setOrder("LOCATE('$patient_nom_search', nom) DESC, nom, prenom, naissance");
            } else {
                $patient_search_service->setOrder('nom, prenom, naissance');
            }

            if ($patient_prenom_search) {
                $patient_search_service->addFirstNameFilter($patient_prenom_search, $patient_prenom_search_limited);
            }

            if ($patient_year || $patient_month || $patient_day) {
                $patient_naissance =
                    CValue::first($patient_year, "%") . "-" .
                    CValue::first($patient_month, "%") . "-" .
                    CValue::first($patient_day, "%");

                $patient_search_service->addBirthFilter($patient_naissance);
            }

            // Ajout des clauses where concernant les parturientes seulement s"il y a déjà des filtres renseignés
            if ($parturiente && count($patient_search_service->getWhere())) {
                $patient_search_service->addParturientFilter();
            }

            if ($patient_ville) {
                $patient_search_service->addVilleFilter($patient_ville);
            }

            if ($patient_cp) {
                $patient_search_service->addCpFilter($patient_cp);
            }

            if ($prat_id && !$see_link_prat) {
                $patient_search_service->addPraticienFilter($prat_id);
            }

            if ($patient_sexe && $patient_search_service->getWhere()) {
                $patient_search_service->addSexFilter($patient_sexe);
            }

            if ($patient_matricule) {
                $patient_search_service->addCardFilter(str_replace(' ', '', $patient_matricule));
            }

            // Chargement des patients
            if ($patient_search_service->getWhere()) {
                $patient_search_service->queryPatients($use_function_distinct, $use_group_distinct, $function_id, $curr_group_id, $prat_id, $see_link_prat, $patient_nda, $start, $paginate);
            }

            // Par soundex
            if ($patient_search_service->getWhereSoundex() && (!$paginate || ($paginate && !$start))) {
                $patient_search_service->queryPatientsSoundex($use_function_distinct, $use_group_distinct, $function_id, $curr_group_id, $prat_id, $see_link_prat, $patient_nda);
            }

            // Par recherche limitée
            if ($patient_search_service->getWhereLimited() && $limit_char_search && !$lenSearchConfig && (!$paginate || ($paginate && !$start))) {
                $patient_search_service->queryPatientsLimited($use_function_distinct, $use_group_distinct, $function_id, $curr_group_id, $prat_id, $see_link_prat, $patient_nda);
            }

            $patients = $patient_search_service->getPatients();

            // Sélection du premier de la liste si aucun n'est sélectionné
            if (!$patient->_id && count($patients) === 1) {
                $patient = reset($patients);
            }

            // Patient vitale associé trouvé : prioritaire
            if ($patVitale->_id) {
                $patient = $patVitale;

                // Au cas où il n'aurait pas été trouvé grâce aux champs
                $patient_search_service->addPatient($patient);
            }
        }

        // Vérification du droit de lecture
        $patient_search_service->filterByReadingRight();

        // Charge des éléments du patients
        $patient_search_service->loadRefsFromAllPatients($see_link_prat, $prat_id, $mode);

        // Si la configuration "Limiter la recherche" est activée (n'afficher qu'un résultat pour éviter les doublons)
        if ($limit_char_search) {
            $patient_search_service->removeDuplicatesOfSoundexFromLimited();
        }

        /** @var CPatient[] $all_patients */
        $all_patients = $patient_search_service->getAllPatients();

        $sejours_avenir  = CSejour::checkIncomingSejours($all_patients);
        $sejours_encours = CSejour::checkIncomingSejours($all_patients, true);

        $patients = $patient_search_service->getPatients();

        CStoredObject::massLoadFwdRef($patients, 'medecin_traitant');
        foreach ($patients as $_patient) {
            $_patient->loadRefMedecinTraitant();
        }

        $tpl_vars = [
            'canPatients'   => CModule::getCanDo('dPpatients'),
            'canAdmissions' => CModule::getCanDo('dPadmissions'),
            'canPlanningOp' => CModule::getCanDo('dPplanningOp'),
            'canCabinet'    => CModule::getCanDo('dPcabinet'),

            'nom'           => $patient_nom,
            'prenom'        => $patient_prenom,
            'naissance'     => $patient_naissance,
            'ville'         => $patient_ville,
            'cp'            => $patient_cp,
            'nom_search'    => $patient_nom_search,
            'prenom_search' => $patient_prenom_search,
            'sexe'          => $patient_sexe,
            'prat_id'       => $prat_id,

            'useVitale'       => $useVitale,
            'patVitale'       => $patVitale,
            'patients'        => $patients,
            'patientsLimited' => $patient_search_service->getPatientsLimited(),
            'patientsSoundex' => $patient_search_service->getPatientsSoundex(),

            'patient'         => $patient,
            'mode'            => $mode,
            'patient_ipp'     => $patient_ipp,
            'patient_nda'     => $patient_nda,
            'sejours_avenir'  => $sejours_avenir,
            'sejours_encours' => $sejours_encours,
        ];

        if (!$prat_id && $paginate) {
            $tpl_vars = array_merge($tpl_vars, [
                'step'  => $patient_search_service->getLimit(),
                'start' => $start,
                'total' => $patient_search_service->getTotal(),
            ]);
        }
        $this->renderSmarty('inc_search_patients', $tpl_vars);
    }

    public function widgetCorrespondants(): void
    {
        $this->checkPermEdit();

        $patient_id = CView::get("patient_id", "ref class|CPatient", true);
        $widget_id  = CView::get("widget_id", "str");

        CView::checkin();

        $patient = CPatient::findOrNew($patient_id);

        if ($patient->_id) {
            $patient->loadRefsCorrespondants();

            $patient->_ref_medecin_traitant->getExercicePlaces();

            foreach ($patient->_ref_medecins_correspondants as $curr_corresp) {
                $curr_corresp->_ref_medecin->loadRefSpecCPAM();
                $curr_corresp->_ref_medecin->getExercicePlaces();
            }
        }

        $user = CMediusers::get();
        $user->isMedecin();

        $this->renderSmarty(
            'inc_widget_correspondants',
            [
                'patient'     => $patient,
                'widget_id'   => $widget_id,
                'user'        => $user,
            ]
        );
    }

    public function checkCorrespondantMedical(): void
    {

        $patient_id   = CView::get('patient_id', 'ref class|CPatient notNull');
        $object_class = CView::get('object_class', 'str notNull');
        $object_id    = CView::get('object_id', 'ref class|CStoredObject meta|object_class');
        $use_meff     = CView::get('use_meff', 'bool default|1');

        CView::checkin();

        /** @var CMbObject $object */
        $object = $object_class::findOrNew($object_id);

        $patient = CPatient::findOrFail($patient_id);
        $patient->loadRefMedecinTraitant();
        $patient->loadRefsCorrespondants();

        $correspondantsMedicaux = [];
        if ($patient->_ref_medecin_traitant->_id) {
            $correspondantsMedicaux["traitant"] = $patient->_ref_medecin_traitant;
        }

        foreach ($patient->_ref_medecins_correspondants as $correspondant) {
            $correspondantsMedicaux["correspondants"][] = $correspondant->_ref_medecin;
        }

        $medecin_adresse_par = "";

        $this->renderSmarty('inc_check_correspondant_medical', [
            'object'                 => $object,
            'correspondantsMedicaux' => $correspondantsMedicaux,
            'medecin_adresse_par'    => $medecin_adresse_par,
            'use_meff'               => $use_meff,
        ]);
    }

    public function vwCorrectOldVali(): void
    {
        $this->checkPermEdit();

        $page = CView::get('page', 'num default|0');

        CView::checkin();

        $patient = new CPatient();

        $where = [
            'status' => $patient->getDS()->prepare('= ?', CPatientState::STATE_VALI),
        ];

        $where[] = 'source_identite_id IS NULL OR
        (commune_naissance_insee IS NULL AND pays_naissance_insee IS NULL AND cp_naissance IS NULL)';

        $patients = $patient->loadList(
            $where,
            'patients.nom_jeune_fille, patients.nom, patients.prenom',
            "{$page},50"
        );

        $total = $patient->countList($where);

        $this->renderSmarty(
            'vw_correct_old_vali',
            [
                'patients' => $patients,
                'page'     => $page,
                'total'    => $total
            ]
        );
    }

    public function updateOldVali(): void
    {
        $this->checkPermAdmin();

        $ds = CSQLDataSource::get('std');

        $ds->exec(
            "UPDATE `patients`
             SET `status` = 'PROV'
             WHERE (`status` = 'VALI' AND `source_identite_id` IS NULL)
             OR (
               `status` = 'VALI'
               AND (`commune_naissance_insee` IS NULL AND `pays_naissance_insee` IS NULL AND cp_naissance IS NULL)
             )"
        );

        CAppUI::setMsg('CPatient-Count old vali affected', UI_MSG_OK, $ds->affectedRows());

        echo CAppUI::getMsg();
    }
}
