<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Services;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CPDODataSource;
use Ox\Core\CSoundex2;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPaysInsee;

/**
 * Description
 */
class PatientSearchService
{
    /** @var array */
    private $patients = [];
    /** @var array */
    private $patientsSoundex = [];
    /** @var array */
    private $patientsLimited = [];
    /** @var array */
    private $where = [];
    /** @var array */
    private $whereLimited = [];
    /** @var array */
    private $whereSoundex = [];
    /** @var array */
    private $ljoin = [];
    /** @var string */
    private $groupBy = null;
    /** @var CSoundex2 */
    private $soundexObj = null;
    /** @var string */
    private $order = null;
    /** @var int */
    private $total = 0;
    /** @var int */
    private $totalSoundex = 0;
    /** @var CPDODataSource|CSQLDataSource */
    private $ds = null;
    /** @var int  */
    private $limit = 30;

    public function __construct()
    {
        $this->ds         = (new CPatient())->getDS();
        $this->soundexObj = new CSoundex2();
    }


    public function reformatResearchValue($value): string
    {
        return preg_replace('/[^\w%_]+/', '_', CMbString::removeDiacritics(trim($value)));
    }

    /**
     * @param string $patient_nom_search
     * @param string $patient_nom_search_limited
     * @param string $patient_nom
     */
    public function addLastNameFilter($patient_nom_search, $patient_nom_search_limited, $patient_nom): void
    {
        $patient_nom_soundex = $this->soundexObj->build($patient_nom_search);
        $patient_nom_ext     = str_replace(" ", "%", $patient_nom);

        $this->where[]        = $this->ds->prepare(
                "`nom` LIKE ?1 OR `nom_jeune_fille` LIKE ?1",
                "$patient_nom_search%"
            )
            . ($patient_nom_ext === "" ? "" : $this->ds->prepare(
                " OR `nom` LIKE ?1 OR `nom_jeune_fille` LIKE ?1",
                "$patient_nom_ext%"
            ));
        $this->whereLimited[] = $this->ds->prepare(
            "`nom` LIKE ?1 OR `nom_jeune_fille` LIKE ?1",
            "$patient_nom_search_limited%"
        );
        $this->whereSoundex[] = $this->ds->prepare(
            "`nom_soundex2` LIKE ?1 OR `nomjf_soundex2` LIKE ?1",
            "$patient_nom_soundex%"
        );
    }

    /**
     * @param string $patient_prenom_search
     * @param string $patient_prenom_search_limited
     */
    public function addFirstNameFilter($patient_prenom_search, $patient_prenom_search_limited): void
    {
        $patient_prenom_soundex                = $this->soundexObj->build($patient_prenom_search);
        $this->where[]                         = $this->ds->prepare(
            "prenom LIKE ?1 OR prenoms LIKE ?2 OR prenom_usuel LIKE ?1",
            "$patient_prenom_search%", "%$patient_prenom_search%"
        );
        $this->whereLimited["prenom"]          = $this->ds->prepareLike("$patient_prenom_search_limited%");
        $this->whereSoundex["prenom_soundex2"] = $this->ds->prepareLike("$patient_prenom_soundex%");
    }

    /**
     * @param string $patient_naissance
     */
    public function addBirthFilter($patient_naissance): void
    {
        $this->where["naissance"]        = $this->ds->prepareLike($patient_naissance);
        $this->whereSoundex["naissance"] = $this->ds->prepareLike($patient_naissance);
        $this->whereLimited["naissance"] = $this->ds->prepareLike($patient_naissance);
    }

    /**
     *
     */
    public function addParturientFilter(): void
    {
        $_expr = "naissance <= '" . CMbDT::date("-12 years", CMbDT::date()) . "'";

        $this->where["sexe"]        = $this->ds->prepare("= ?", 'f');
        $this->whereSoundex["sexe"] = $this->ds->prepare("= ?", 'f');
        $this->whereLimited["sexe"] = $this->ds->prepare("= ?", 'f');

        $this->where[]        = $_expr;
        $this->whereSoundex[] = $_expr;
        $this->whereLimited[] = $_expr;
    }

    /**
     * @param string $patient_ville
     */
    public function addVilleFilter($patient_ville): void
    {
        $this->where["ville"]        = $this->ds->prepareLike("$patient_ville%");
        $this->whereSoundex["ville"] = $this->ds->prepareLike("$patient_ville%");
        $this->whereLimited["ville"] = $this->ds->prepareLike("$patient_ville%");
    }

    /**
     * @param string $patient_cp
     */
    public function addCpFilter($patient_cp): void
    {
        $this->where["cp"]        = $this->ds->prepareLike("$patient_cp%");
        $this->whereSoundex["cp"] = $this->ds->prepareLike("$patient_cp%");
        $this->whereLimited["cp"] = $this->ds->prepareLike("$patient_cp%");
    }

    /**
     * @param string $prat_id
     */
    public function addPraticienFilter($prat_id): void
    {
        $this->ljoin["consultation"] = "`consultation`.`patient_id` = `patients`.`patient_id`";
        $this->ljoin["plageconsult"] = "`plageconsult`.`plageconsult_id` = `consultation`.`plageconsult_id`";
        $this->ljoin["sejour"]       = "`sejour`.`patient_id` = `patients`.`patient_id`";

        // Leave it here because of if ($where) testing...
        $this->where['plageconsult.chir_id']        = $this->ds->prepare(
            "= ?1 OR sejour.praticien_id = ?1",
            $prat_id,
        );
        $this->whereLimited['plageconsult.chir_id'] = $this->ds->prepare(
            "= ?1 OR sejour.praticien_id = ?1",
            $prat_id,
        );
        $this->whereSoundex['plageconsult.chir_id'] = $this->ds->prepare(
            "= ?1 OR sejour.praticien_id = ?1",
            $prat_id,
        );

        $this->groupBy = "patient_id";
    }

    /**
     * @param string $patient_sexe
     */
    public function addSexFilter($patient_sexe): void
    {
        $this->where["sexe"]        = $this->ds->prepare('= ?', $patient_sexe);
        $this->whereSoundex["sexe"] = $this->ds->prepare('= ?', $patient_sexe);
        $this->whereLimited["sexe"] = $this->ds->prepare('= ?', $patient_sexe);
    }

    /**
     * @param string $code_insee
     */
    public function addPaysNaissanceInseeFilter(string $numeric): void
    {
        $this->where["pays_naissance_insee"]        = $this->ds->prepare('= ?', $numeric);
        $this->whereSoundex["pays_naissance_insee"] = $this->ds->prepare('= ?', $numeric);
    }

    /**
     * @param string $code_insee
     */
    public function addCommuneNaissanceInseeFilter(string $code_insee): void
    {
        $this->where["commune_naissance_insee"]        = $this->ds->prepare('= ?', $code_insee);
        $this->whereSoundex["commune_naissance_insee"] = $this->ds->prepare('= ?', $code_insee);
    }

    /**
     * @param string $cp
     */
    public function addCPNaissanceFilter(string $cp): void
    {
        $this->where["cp_naissance"]        = $this->ds->prepareLike("%$cp%");
        $this->whereSoundex["cp_naissance"] = $this->ds->prepareLike("%$cp%");
    }


    /**
     * @param string $lieu
     */
    public function addLieuNaissanceFilter($lieu): void
    {
        $this->where["lieu_naissance"]        = $this->ds->prepare('= ?', $lieu);
        $this->whereSoundex["lieu_naissance"] = $this->ds->prepare('= ?', $lieu);
    }

    /**
     * @param string $card_value
     */
    public function addCardFilter($card_value): void
    {
        $this->where["matricule"] = $this->ds->prepareLike("$card_value%");
    }

    public function addFunctionFilter($use_function, $use_group, $function_id, $curr_group_id, $whereProp): void
    {
        // Séparation des patients par fonction
        if (property_exists($this, $whereProp)) {
            if ($use_function) {
                $this->{$whereProp}["function_id"] = "= '$function_id'";
            } elseif ($use_group) {
                $this->{$whereProp}["patients.group_id"] = "= '$curr_group_id'";
            }
        }
    }

    public function removeFilter(string $key, bool $apply_to_soundex = true): void
    {
        if (isset($this->where[$key])) {
            unset($this->where[$key]);
        }

        if ($apply_to_soundex && isset($this->whereSoundex[$key])) {
            unset($this->whereSoundex[$key]);
        }
    }

    /**
     * @param $use_function_distinct
     * @param $use_group_distinct
     * @param $function_id
     * @param $curr_group_id
     * @param $prat_id
     * @param $see_link_prat
     * @param $patient_nda
     * @param $start
     * @param $paginate
     *
     * @throws Exception
     */
    public function queryPatients(
        $use_function_distinct = null,
        $use_group_distinct = null,
        $function_id = null,
        $curr_group_id = null,
        $prat_id = null,
        $see_link_prat = null,
        $patient_nda = null,
        $start = null,
        $paginate = null
    ): void {
        $this->addFunctionFilter($use_function_distinct, $use_group_distinct, $function_id, $curr_group_id, 'where');
        $pat = new CPatient();
        // Séparation en deux requêtes
        if ($prat_id && !$see_link_prat) {
            $patients_consults = [];

            if (!$patient_nda) {
                // Consultations
                $ljoin_consults = $this->ljoin;
                $where_consults = $this->where;

                unset($ljoin_consults['sejour']);
                $where_consults['plageconsult.chir_id'] = $this->ds->prepare('= ?', $prat_id);

                $patients_consults = $pat->loadList(
                    $where_consults,
                    $this->order,
                    $this->limit,
                    $this->groupBy,
                    $ljoin_consults,
                    null,
                    null,
                    false
                );
            }

            // Séjours
            $ljoin_sejours = $this->ljoin;
            $where_sejours = $this->where;

            unset($ljoin_sejours['consultation']);
            unset($ljoin_sejours['plageconsult']);
            unset($where_sejours['plageconsult.chir_id']);
            $where_sejours['sejour.praticien_id'] = $this->ds->prepare('= ?', $prat_id);

            $patients_sejours = $pat->loadList(
                $where_sejours,
                $this->order,
                $this->limit,
                $this->groupBy,
                $ljoin_sejours,
                null,
                null,
                false
            );
            $this->patients   = $patients_consults + $patients_sejours;
        } else {
            if ($paginate) {
                $limit          = "$start, " . $this->limit;
                $this->patients = $pat->loadList(
                    $this->where,
                    $this->order,
                    $limit,
                    $this->groupBy,
                    $this->ljoin,
                    null,
                    null,
                    false
                );
            } else {
                $this->patients = $pat->loadList(
                    $this->where,
                    $this->order,
                    $this->limit,
                    $this->groupBy,
                    $this->ljoin,
                    null,
                    null,
                    false
                );
            }

            $this->total = $pat->countList($this->where, $this->groupBy, $this->ljoin);
        }
    }

    /**
     * @param $use_function_distinct
     * @param $use_group_distinct
     * @param $function_id
     * @param $curr_group_id
     * @param $prat_id
     * @param $see_link_prat
     * @param $patient_nda
     *
     * @throws Exception
     */
    public function queryPatientsSoundex(
        $use_function_distinct = null,
        $use_group_distinct = null,
        $function_id = null,
        $curr_group_id = null,
        $prat_id = null,
        $see_link_prat = null,
        $patient_nda = null
    ): void {
        // Séparation des patients par fonction
        $this->addFunctionFilter(
            $use_function_distinct,
            $use_group_distinct,
            $function_id,
            $curr_group_id,
            'whereSoundex'
        );
        $pat = new CPatient();

        if ($prat_id && !$see_link_prat) {
            $patients_consults = [];

            if (!$patient_nda) {
                // Consultations
                $ljoin_consults = $this->ljoin;
                $where_consults = $this->whereSoundex;

                unset($ljoin_consults['sejour']);
                $where_consults['plageconsult.chir_id'] = $this->ds->prepare('= ?', $prat_id);

                $patients_consults = $pat->loadList(
                    $where_consults,
                    $this->order,
                    $this->limit,
                    $this->groupBy,
                    $ljoin_consults,
                    null,
                    null,
                    false
                );
            }

            // Séjours
            $ljoin_sejours = $this->ljoin;
            $where_sejours = $this->whereSoundex;

            unset($ljoin_sejours['consultation']);
            unset($ljoin_sejours['plageconsult']);
            unset($where_sejours['plageconsult.chir_id']);
            $where_sejours['sejour.praticien_id'] = $this->ds->prepare('= ?', $prat_id);

            $patients_sejours      = $pat->loadList(
                $where_sejours,
                $this->order,
                $this->limit,
                $this->groupBy,
                $ljoin_sejours,
                null,
                null,
                false
            );
            $this->patientsSoundex = $patients_consults + $patients_sejours;
        } else {
            $this->patientsSoundex = $pat->loadList(
                $this->whereSoundex,
                $this->order,
                $this->limit,
                $this->groupBy,
                $this->ljoin,
                null,
                null,
                false
            );
        }

        $this->patientsSoundex = array_diff_key($this->patientsSoundex, $this->patients);
        $this->totalSoundex    = $pat->countList($this->whereSoundex, $this->groupBy, $this->ljoin);
    }

    /**
     * @param $use_function_distinct
     * @param $use_group_distinct
     * @param $function_id
     * @param $curr_group_id
     * @param $prat_id
     * @param $see_link_prat
     * @param $patient_nda
     *
     * @throws Exception
     */
    public function queryPatientsLimited(
        $use_function_distinct = null,
        $use_group_distinct = null,
        $function_id = null,
        $curr_group_id = null,
        $prat_id = null,
        $see_link_prat = null,
        $patient_nda = null
    ): void {
        // Séparation des patients par fonction
        $this->addFunctionFilter(
            $use_function_distinct,
            $use_group_distinct,
            $function_id,
            $curr_group_id,
            'whereLimited'
        );
        $pat = new CPatient();

        if ($prat_id && !$see_link_prat) {
            $patients_consults = [];

            if (!$patient_nda) {
                // Consultations
                $ljoin_consults = $this->ljoin;
                $where_consults = $this->whereLimited;

                unset($ljoin_consults['sejour']);
                $where_consults['plageconsult.chir_id'] = $this->ds->prepare('= ?', $prat_id);

                $patients_consults = $pat->loadList(
                    $where_consults,
                    $this->order,
                    $this->limit,
                    $this->groupBy,
                    $ljoin_consults,
                    null,
                    null,
                    false
                );
            }

            // Séjours
            $ljoin_sejours = $this->ljoin;
            $where_sejours = $this->whereLimited;

            unset($ljoin_sejours['consultation']);
            unset($ljoin_sejours['plageconsult']);
            unset($where_sejours['plageconsult.chir_id']);
            $where_sejours['sejour.praticien_id'] = $this->ds->prepare('= ?', $prat_id);

            $patients_sejours      = $pat->loadList(
                $where_sejours,
                $this->order,
                $this->limit,
                $this->groupBy,
                $ljoin_sejours,
                null,
                null,
                false
            );
            $this->patientsLimited = $patients_consults + $patients_sejours;
        } else {
            $this->patientsLimited = $pat->loadList(
                $this->whereLimited,
                $this->order,
                $this->limit,
                $this->groupBy,
                $this->ljoin,
                null,
                null,
                false
            );
        }

        $this->patientsLimited = array_diff_key($this->patientsLimited, $this->patients);
    }

    /**
     *
     */
    public function filterByReadingRight(): void
    {
        foreach ($this->getAllPatients() as $key => $_patient) {
            if (!$_patient->canDo()->read) {
                unset($this->patients[$key]);
                unset($this->patientsSoundex[$key]);
                unset($this->patientsLimited[$key]);
            }
        }
    }

    /**
     * @param $see_link_prat
     * @param $prat_id
     * @param $mode
     *
     * @throws Exception
     */
    public function loadRefsFromAllPatients($see_link_prat, $prat_id, $mode): void
    {
        $all_patients = $this->getAllPatients();
        CPatient::massLoadIPP($all_patients);

        CStoredObject::massLoadBackRefs($all_patients, "correspondants");
        CStoredObject::massLoadBackRefs($all_patients, "notes");
        CStoredObject::massLoadBackRefs($all_patients, "bmr_bhre");

        foreach ($all_patients as $_patient) {
            $_patient->loadRefsNotes();
            $_patient->updateBMRBHReStatus();
            if ($see_link_prat) {
                $_patient->countConsultationPrat($prat_id);
            }

            if ($mode === "selector") {
                $today = CMbDT::date();

                // Chargement des consultations du jour
                $where     = [
                    "plageconsult.date" => $this->ds->prepare('= ?', $today),
                ];
                $_consults = $_patient->loadRefsConsultations($where);
                foreach ($_consults as $_consult) {
                    $_consult->loadRefPraticien()->loadRefFunction();
                }

                // Chargement des admissions du jour
                $where    = [
                    "entree" => $this->ds->prepareLike("$today __:__:__"),
                ];
                $_sejours = $_patient->loadRefsSejours($where);
                foreach ($_sejours as $_sejour) {
                    $_sejour->loadRefPraticien()->loadRefFunction();
                }
            }
        }
    }

    /**
     *
     */
    public function removeDuplicatesOfSoundexFromLimited()
    {
        foreach ($this->patientsLimited as $_pat_limited) {
            foreach ($this->patientsSoundex as $_pat_soundex) {
                if ($_pat_limited->_id == $_pat_soundex->_id) {
                    unset($this->patientsSoundex[$_pat_limited->_id]);
                }
            }
        }
    }

    /**
     * @param CPatient $patient
     */
    public function addPatient($patient): void
    {
        $this->patients[$patient->_id] = $patient;
    }

    /**
     * @return array
     */
    public function getWhere(): array
    {
        return $this->where;
    }

    /**
     * @return array
     */
    public function getWhereSoundex(): array
    {
        return $this->whereSoundex;
    }

    /**
     * @return array
     */
    public function getWhereLimited(): array
    {
        return $this->whereLimited;
    }

    /**
     * @return array
     */
    public function getPatients(): array
    {
        return $this->patients;
    }

    /**
     * @return array
     */
    public function getAllPatients(): array
    {
        // Ne pas utiliser array_merge, les clés sont perdues
        return $this->patients + $this->patientsSoundex + $this->patientsLimited;
    }

    /**
     * @return array
     */
    public function getPatientsSoundex(): array
    {
        return $this->patientsSoundex;
    }

    /**
     * @return array
     */
    public function getPatientsLimited(): array
    {
        return $this->patientsLimited;
    }

    /**
     * @param $order
     */
    public function setOrder($order): void
    {
        $this->order = $order;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getTotalSoundex(): int
    {
        return $this->totalSoundex;
    }

    public function emptyWhere(): void
    {
        $this->where        = [];
        $this->whereSoundex = [];
        $this->whereLimited = [];
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
