<?php

/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stats;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 *
 * Statistics of patients per type of hospitalisation
 *
 * Refactored from file dPstats\graph_patpartypehospi
 */
class GraphePatient
{
    private const TYPE_DATA_PREVUE = "prevue";

    private const TYPE_DATA_REAL = "reelle";

    private const TYPE_DATA = [
        self::TYPE_DATA_PREVUE,
        self::TYPE_DATA_REAL,
    ];
    /** @var int */
    public int $septique;
    /** @var CSQLDataSource */
    private CSQLDataSource $ds;
    /** @var array $options */
    private array $options = [];
    /** @var array $series */
    private array $series = [];
    /** @var array $serie_total */
    private array $serie_total = [];
    /** @var array $graph_data */
    private array $graph_data = [];
    /** @var array $ticks */
    private array $ticks = [];
    /** @var int */
    private int $total = 0;
    /** @var CDiscipline */
    private CDiscipline $discipline;
    /**@var CService */
    private CService $service;
    /** @var string */
    private string $type_data;
    /** @var string */
    private string $type_adm;
    /** @var string */
    private string $codes_ccam;
    /** @var string */
    private string $debut;
    /** @var string */
    private string $fin;
    /** @var CMediusers */
    private CMediusers $praticien;

    /**
     * @throws Exception
     */
    public function __construct(
        string $debut = null,
        string $fin = null,
        int $prat_id = 0,
        int $service_id = 0,
        string $type_adm = "comp",
        int $discipline_id = 0,
        int $septique = 0,
        string $type_data = "prevue",
        string $codes_ccam = null
    ) {
        $this->debut = $debut ?? CMbDT::date("-1 YEAR");
        $this->fin   = $fin ?? CMbDT::date();

        $prat            = new CMediusers();
        $this->praticien = $prat->load($prat_id);

        $this->discipline = CDiscipline::findOrNew($discipline_id);

        $this->service    = CService::findOrNew($service_id);
        $this->codes_ccam = $codes_ccam;
        $this->type_adm   = $type_adm;
        $this->septique   = $septique;
        $this->type_data  = $type_data;
        $this->ds         = CSQLDataSource::get("std");

        $this->options = [
            "xaxis"       => [
                "labelsAngle" => 45,
            ],
            "yaxis"       => ["min" => 0, "autoscaleMargin" => 5],
            "bars"        => ["show" => true, "stacked" => true, "barWidth" => 0.8],
            "HtmlText"    => false,
            "legend"      => ["show" => true, "position" => "nw"],
            "grid"        => ["verticalLines" => false],
            "spreadsheet" => [
                "show"             => true,
                "csvFileSeparator" => ";",
                "decimalSeparator" => ",",
                "tabGraphLabel"    => CAppUI::tr("CFlotrGraph-spreadsheet-Graph"),
                "tabDataLabel"     => CAppUI::tr("CFlotrGraph-spreadsheet-Data"),
                "toolbarDownload"  => CAppUI::tr("CFlotrGraph-spreadsheet-Download CSV"),
                "toolbarSelectAll" => CAppUI::tr("CFlotrGraph-spreadsheet-Select table"),
            ],
        ];

        $this->serie_total = [
            "label"   => CAppUI::tr("Total"),
            "data"    => [],
            "markers" => ["show" => true],
            "bars"    => ["show" => false],
        ];
    }

    /**
     * @throws Exception
     */
    public function getData(): void
    {
        for ($i = $this->debut; $i <= $this->fin; $i = CMbDT::date("+1 MONTH", $i)) {
            $this->ticks[]               = [count($this->ticks), CMbDT::transform("+0 DAY", $i, "%m/%Y")];
            $this->serie_total["data"][] = [count($this->serie_total["data"]), 0];
        }
        $sejour     = new CSejour();
        $listHospis = [];
        foreach ($sejour->_specs["type"]->_locales as $key => $type) {
            if (
                (($key == "comp" || $key == "ambu") && $this->type_adm == 1) ||
                ($this->type_adm == $key) ||
                ($this->type_adm == null)
            ) {
                $listHospis[$key] = $type;
            }
        }

        foreach ($listHospis as $key => $type) {
            $query = new CRequest();
            $query->addColumn("COUNT(DISTINCT sejour.sejour_id)", "total");
            $query->addColumn("sejour.type");
            $query->addColumn("DATE_FORMAT(sejour.entree_$this->type_data, '%m/%Y')", "mois");
            $query->addColumn("DATE_FORMAT(sejour.entree_$this->type_data, '%Y%m')", "orderitem");
            $query->addTable("sejour");
            $query->addLJoinClause("users_mediboard", "sejour.praticien_id = users_mediboard.user_id");
            $query->addLJoinClause("affectation", "sejour.sejour_id = affectation.sejour_id");
            $query->addLJoinClause("service", "affectation.service_id = service.service_id");
            $query->addWhereClause(
                "sejour.entree_$this->type_data",
                $this->ds->prepareBetween("$this->debut 00:00:00", "$this->fin 23:59:59")
            );
            $query->addWhereClause("sejour.group_id", $this->ds->prepare("= ?", CGroups::loadCurrent()->_id));
            $query->addWhereClause("sejour.type", $this->ds->prepare("= ?", $key));
            $query->addWhereClause("sejour.annule", $this->ds->prepare("= '0'"));

            if ($this->service->_id) {
                $query->addWhereClause("service.service_id", $this->ds->prepare("= ?", $this->service->_id));
            }
            if ($this->praticien->_id) {
                $query->addWhereClause("sejour.praticien_id", $this->ds->prepare("= ?", $this->praticien->_id));
            }
            if ($this->discipline->_id) {
                $query->addWhereClause(
                    "users_mediboard.discipline_id",
                    $this->ds->prepare("= ?", $this->discipline->_id)
                );
            }
            if ($this->septique) {
                $query->addWhereClause("sejour.septique", $this->ds->prepare("= ?", $this->septique));
            }

            if ($this->codes_ccam) {
                $query->addLJoinClause("operations", "operations.sejour_id = sejour.sejour_id");
                $whereOr = [
                    "sejour.codes_ccam " . $this->ds->prepareLike("%$this->codes_ccam%"),
                    "operations.codes_ccam " . $this->ds->prepareLike("%$this->codes_ccam%"),
                ];
                $query->addWhere(implode(" OR ", $whereOr));
            }

            $query->addGroup("mois");
            $query->addOrder("orderitem");

            $result = $sejour->_spec->ds->loadlist($query->makeSelect());
            $this->buildSerieData($result, $type);
        }

        $this->buildGraphData();
    }

    private function buildSerieData(array $result, string $type): void
    {
        $serie = [
            "label" => $type,
            "data"  => [],
        ];
        foreach ($this->ticks as $i => $tick) {
            $f = true;
            foreach ($result as $r) {
                if ($tick[1] == $r["mois"]) {
                    $serie["data"][]                  = [$i, $r["total"]];
                    $this->serie_total["data"][$i][1] += $r["total"];
                    $this->total                      += $r["total"];
                    $f                                = false;
                }
            }
            if ($f) {
                $serie["data"][] = [count($serie["data"]), 0];
            }
        }
        $this->series[] = $serie;
    }

    /**
     *
     */
    public function buildGraphData(): void
    {
        $this->series[] = $this->serie_total;

        $subtitle = "$this->total patients";
        if ($this->praticien->_id) {
            $subtitle .= " - Dr $this->praticien";
        }
        if ($this->discipline->_id) {
            $subtitle .= " - $this->discipline->_view";
        }
        if ($this->septique) {
            $subtitle .= " - " . CAppUI::tr("common-Septic|pl");
        }
        $this->options["title"] = CAppUI::tr("GraphePatient-title-nb admission per hospitalisation type") . " - "
            . $this->type_data;

        $this->options["subtitle"]       = $subtitle;
        $this->options["xaxis"]["ticks"] = $this->ticks;

        $this->graph_data = ["series" => $this->series, "options" => $this->options];
    }

    /**
     * @return array
     */
    public function getGraphData(): array
    {
        return $this->graph_data;
    }
}
