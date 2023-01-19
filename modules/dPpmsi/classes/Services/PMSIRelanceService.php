<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi\Services;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CPDODataSource;
use Ox\Core\CSQLDataSource;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Pmsi\CRelancePMSI;

class PMSIRelanceService implements IShortNameAutoloadable
{

    /** @var array */
    private $ljoin = [];
    /** @var array */
    private $where = [];
    /** @var CPDODataSource|CSQLDataSource */
    private $ds;
    /** @var array */
    private $relances_by_prat = [];
    /** @var CMediusers[] */
    private $prats = [];

    public function __construct()
    {
        $this->ds = (new CRelancePMSI())->getDS();
    }

    /**
     * @param string $order_col
     * @param string $order_way
     *
     * @return string
     */
    public function computeOrder(string $order_col, string $order_way): string
    {
        switch ($order_col) {
            case 'chir_id':
                $this->ljoin['users_mediboard'] = 'users_mediboard.user_id = relance_pmsi.chir_id';
                $this->ljoin['users']           = 'users_mediboard.user_id = users.user_id';
                $order                          = 'users.user_last_name';
                break;
            case 'entree':
            case 'sortie':
            case 'sortie_reelle':
                $order = "sejour.$order_col";
                break;
            case 'urgence':
                $order = 'urgence';
                break;
            //nom
            default:
                $this->ljoin['patients'] = 'patients.patient_id = relance_pmsi.patient_id';
                $order                   = 'patients.nom';
        }

        return "$order $order_way";
    }

    /**
     * @param string $NDA
     *
     * @return CSejour
     */
    public function getSejourFromNDA(string $NDA): CSejour
    {
        $sejour = new CSejour();
        $sejour->loadFromNDA($NDA);
        $sejour->loadRefPatient();

        return $sejour;
    }

    /**
     * @throws Exception
     */
    public function addGroupFilter(): void
    {
        $this->where['sejour.group_id'] = $this->ds->prepare('= ?', CGroups::get()->_id);
    }

    public function joinSejour(): void
    {
        $this->ljoin["sejour"] = "sejour.sejour_id = relance_pmsi.sejour_id";
    }

    /**
     * @param string $date_min_sejour
     * @param string $date_max_sejour
     */
    public function addDatesFilter(string $date_min_sejour, string $date_max_sejour): void
    {
        $this->where[] = $this->ds->prepare('DATE(sejour.entree) < ?', $date_max_sejour);
        $this->where[] = $this->ds->prepare('DATE(sejour.sortie) > ?', $date_min_sejour);
    }

    /**
     * @param string $status
     */
    public function addStatusFilter(string $status): void
    {
        switch ($status) {
            case "non_cloturees":
                $this->where["datetime_cloture"] = $this->ds->prepare("IS NULL");
                break;
            case "datetime_creation":
                $this->where["datetime_relance"] = $this->ds->prepare("IS NULL");
                $this->where["datetime_cloture"] = $this->ds->prepare("IS NULL");
                break;
            case "datetime_relance":
                $this->where["datetime_relance"] = $this->ds->prepare("IS NOT NULL");
                $this->where["datetime_cloture"] = $this->ds->prepare("IS NULL");
                break;
            case "datetime_cloture":
                $this->where["datetime_cloture"] = $this->ds->prepare("IS NOT NULL");
                break;
            default:
        }
    }

    /**
     * @param string $urgence
     */
    public function addUrgenceFilter(string $urgence): void
    {
        $this->where["urgence"] = $this->ds->prepare('= ?', $urgence);
    }

    /**
     * @param string $type_doc
     */
    public function addDocTypeFilter(string $type_doc): void
    {
        $this->where[$type_doc] = $this->ds->prepare('= ?', '1');
    }

    /**
     * @param string $commentaire_med
     */
    public function addCommentFilter(string $commentaire_med): void
    {
        $this->where["commentaire_med"] = "IS " . ($commentaire_med == "1" ? "NOT" : "") . " NULL";
    }

    /**
     * @param int $chir_id
     */
    public function addChirFilter(int $chir_id): void
    {
        $this->where["chir_id"] = $this->ds->prepare('= ?', $chir_id);
    }

    /**
     * @param string $type_sejour
     */
    public function addTypeSejourFilter(string $type_sejour): void
    {
        $this->where["type"] = $this->ds->prepare('= ?', $type_sejour);
    }

    /**
     * @param string $date_min_relance
     * @param string $date_max_relance
     */
    public function addRelanceDatesFilter(string $date_min_relance, string $date_max_relance): void
    {
        $this->where[] = $this->ds->prepare(
            'DATE(relance_pmsi.datetime_creation) BETWEEN ?1 AND ?2',
            $date_min_relance,
            $date_max_relance
        );
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
    public function getLJoin(): array
    {
        return $this->ljoin;
    }

    /**
     * @param CRelancePMSI[] $relances
     * @param string         $date_min_relance
     * @param string         $date_max_relance
     *
     * @throws Exception
     */
    public function export(array $relances, string $date_min_relance, string $date_max_relance): void
    {
        $csv = new CCSVFile();

        $titles = [
            CAppUI::tr("CPatient-NDA"),
            CAppUI::tr("CPatient"),
            CAppUI::tr("CSejour-_date_entree"),
            CAppUI::tr("CRelancePMSI-Statistics-court"),
            CAppUI::tr("CRelancePMSI-Responsible Physician-court"),
            CAppUI::tr("CRelancePMSI-Restate Status"),
            CAppUI::tr("CRelancePMSI-cro"),
            CAppUI::tr("CRelancePMSI-crana"),
            CAppUI::tr("CRelancePMSI-cra"),
            CAppUI::tr("CRelancePMSI-ls"),
            CAppUI::tr("CRelancePMSI-cotation"),
            CAppUI::tr("CRelancePMSI-autre"),
            CAppUI::tr("CRelancePMSI-commentaire_dim"),
            CAppUI::tr("CRelancePMSI-commentaire_med-court"),
            CAppUI::tr("CRelancePMSI-Level"),
        ];
        $csv->writeLine($titles);

        foreach ($relances as $_relance) {
            $sejour         = $_relance->_ref_sejour;
            $patient        = $_relance->_ref_patient;
            $praticien      = $_relance->_ref_chir;
            $statut_relance = "Relance";

            if ($_relance->datetime_cloture) {
                $statut_relance = "Clôturée";
            } elseif ($_relance->datetime_relance) {
                $statut_relance = "2ème relance";
            }

            $data_line = [
                $sejour->_NDA,
                $patient->_view,
                CMbDT::format($sejour->sortie, CAppUI::conf("datetime")),
                $sejour->sortie_reelle ? "Term." : "En cours",
                $praticien->_view,
                $statut_relance,
            ];

            foreach (CRelancePMSI::$docs as $_doc) {
                if (CAppUI::gconf("dPpmsi relances $_doc")) {
                    $data_line[] = $_relance->$_doc ? "X" : "";
                }
            }

            $data_line[] = $_relance->commentaire_dim;
            $data_line[] = $_relance->commentaire_med;
            $data_line[] = $_relance->urgence;

            $csv->writeLine($data_line);
        }

        $period = "du_" . CMbDT::format($date_min_relance, "%d_%m_%Y") . "_" . "_au_" . CMbDT::format(
            $date_max_relance,
            "%d_%m_%Y"
        );

        $csv->stream("relances_" . $period);
        CApp::rip();
    }

    /**
     * @param CRelancePMSI[] $relances
     */
    public function sortRelancesByPrat(array $relances): void
    {
        foreach ($relances as $_relance) {
            if (!isset($this->relances_by_prat[$_relance->chir_id])) {
                $this->relances_by_prat[$_relance->chir_id] = [];
                $this->prats[$_relance->chir_id]            = $_relance->_ref_chir;
            }

            $this->relances_by_prat[$_relance->chir_id][] = $_relance;
        }
    }

    /**
     * @param CRelancePMSI[] $relances
     *
     * @return array
     */
    public function getRelancesByPrat(array $relances): array
    {
        if (empty($this->relances_by_prat)) {
            $this->sortRelancesByPrat($relances);
        }

        return $this->relances_by_prat;
    }

    /**
     * @param CRelancePMSI[] $relances
     *
     * @return CMediusers[]
     */
    public function getPrats(array $relances): array
    {
        if (empty($this->prats)) {
            $this->sortRelancesByPrat($relances);
        }

        return $this->prats;
    }
}
