<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Board\Exception\TdbStatsException;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\PlanningOp\COperation;

class TdbStats
{
    private CSQLDataSource $ds;

    private CConsultation $filter_consultation;

    private const ORDER              = "date, debut";
    private const STEP_PRESCRIPTEURS = 20;

    private const DEFAULT_STATS_VIEWS = [
        "viewSejoursInterventions",
        "viewStatsConsultations",
        "viewPrescripteurs",
    ];

    private const VIEW_STATS_PRESCRIPTION = "viewStatsPrescriptions";

    private const VIEW_TRACES_COTES                = "viewTraceCotes";
    private const REQUETE_CREATE_TEMP_PRAT_PATIENT = "CREATE TEMPORARY TABLE prat_patient (
        patient_id INT(11) UNSIGNED,
        medecin_id INT(11) UNSIGNED,
        origin ENUM('consultation','sejour')
    )";

    private const REQUETE_PRESCRIPTEURS = "SELECT medecin_id, COUNT(DISTINCT(patient_id)) AS nb_patients
    FROM prat_patient
    WHERE medecin_id IS NOT NULL
    GROUP BY medecin_id
    ORDER BY nb_patients DESC";

    private const REQUETE_TOTAL_PRESCRIPTEURS = "SELECT COUNT(DISTINCT(medecin_id))
        FROM prat_patient
        WHERE medecin_id IS NOT NULL";

    private const REQUETES_STATS_PRESCRIPTEURS = [];

    public function __construct()
    {
        $this->ds = CSQLDataSource::get("std");
    }

    /**
     * @param string $selected_view
     *
     * @return string[]
     * @throws TdbStatsException
     * @throws Exception
     */
    public function getAllStatsViews(string $selected_view): array
    {
        $stats_views = self::DEFAULT_STATS_VIEWS;
        if (CModule::getActive("dPprescription")) {
            $stats_views[] = self::VIEW_STATS_PRESCRIPTION;
        }

        if (CAppUI::conf("dPplanningOp COperation verif_cote")) {
            $stats_views[] = self::VIEW_TRACES_COTES;
        }

        if (!in_array($selected_view, $stats_views)) {
            throw TdbStatsException::viewNotFound($selected_view);
        }

        return $stats_views;
    }

    /**
     * @throws Exception
     */
    public function getVerificationCotesStats(CMediusers $prat, string $date): array
    {
        $where = [
            "date"    => $this->ds->prepare("= ?", $date),
            "chir_id" => $this->ds->prepare("= ?", $prat->_id),
        ];

        $listPlages = (new CPlageOp())->loadIds($where, self::ORDER);

        $interv = new COperation();
        $where  = [];
        $where[] = "(plageop_id " . $this->ds->prepareIn($listPlages) .
            " OR (operations.date " . $this->ds->prepare("= ?", $date)
            . " AND operations.chir_id " . $this->ds->prepare("= ?", $prat->_id) . "))";

        /** @var COperation[] $listIntervs */
        $listIntervs = $interv->loadList($where);

        foreach ($listIntervs as &$_interv) {
            $consult_anesth = $_interv->loadRefsConsultAnesth();
            $consult_anesth->countDocItems();

            $consultation = $consult_anesth->loadRefConsultation();
            $consultation->countDocItems();
            $consultation->canRead();
            $consultation->canEdit();

            $_interv->loadRefPlageOp();
            $_interv->loadExtCodesCCAM();

            $_interv->loadRefChir()->loadRefFunction();
            $_interv->loadRefPatient();
            $_interv->updateView();
        }

        return $listIntervs;
    }

    public function getGraphsConsultations(string $date_min, string $date_max, CMediusers $praticien): array
    {
        $filterConsultation = new CConsultation();

        $filterConsultation->_date_min = $date_min;
        $rectif                        = CMbDT::transform("+0 DAY", $filterConsultation->_date_min, "%d") - 1;
        $filterConsultation->_date_min = CMbDT::date("-$rectif DAYS", $filterConsultation->_date_min);

        $filterConsultation->_date_max     = $date_max;
        $rectif                            = CMbDT::transform("+0 DAY", $filterConsultation->_date_max, "%d") - 1;
        $filterConsultation->_date_max     = CMbDT::date("-$rectif DAYS", $filterConsultation->_date_max);
        $filterConsultation->_date_max     = CMbDT::date("+ 1 MONTH", $filterConsultation->_date_max);
        $filterConsultation->_date_max     = CMbDT::date("-1 DAY", $filterConsultation->_date_max);
        $filterConsultation->_praticien_id = $praticien->_id;

        $this->filter_consultation = $filterConsultation;

        CAppUI::requireModuleFile('dPstats', 'graph_consultations');

        return [
            graphConsultations(
                $filterConsultation->_date_min,
                $filterConsultation->_date_max,
                $filterConsultation->_praticien_id
            ),
        ];
    }

    /**
     * @return CConsultation
     */
    public function getFilterConsultation(): CConsultation
    {
        return $this->filter_consultation;
    }

    /**
     * @param CMediusers $prat
     * @param int        $start_prescripteurs
     *
     * @return array
     * @throws Exception
     */
    public function getStatsPrescripteurs(CMediusers $prat, int $start_prescripteurs = 0): array
    {
        $this->ds->exec(self::REQUETE_CREATE_TEMP_PRAT_PATIENT);

        $this->ds->exec(
            "INSERT INTO prat_patient (patient_id, medecin_id, origin)
                    SELECT DISTINCT(sejour.patient_id), patients.medecin_traitant, 'sejour'
                    FROM sejour
                    LEFT JOIN patients ON sejour.patient_id = patients.patient_id
                    WHERE praticien_id = $prat->_id"
        );

        $this->ds->exec(
            "INSERT INTO prat_patient (patient_id, medecin_id, origin)
                SELECT DISTINCT(consultation.patient_id), patients.medecin_traitant, 'consultation'
                FROM consultation
                LEFT JOIN plageconsult ON consultation.plageconsult_id = plageconsult.plageconsult_id
                LEFT JOIN patients ON consultation.patient_id = patients.patient_id
                WHERE plageconsult.chir_id = $prat->_id"
        );

        $prescripteurs = $this->ds->loadHashList(
            self::REQUETE_PRESCRIPTEURS .
            " LIMIT $start_prescripteurs, " . self::STEP_PRESCRIPTEURS
        );

        $total_prescripteurs = $this->ds->loadResult(self::REQUETE_TOTAL_PRESCRIPTEURS);

        $where    = [
            "medecin_id" => $this->ds->prepareIn(array_keys($prescripteurs)),
        ];
        $medecins = (new CMedecin())->loadList($where);

        return
            [
                $prescripteurs,
                $total_prescripteurs,
                $medecins,
            ];
    }
}
