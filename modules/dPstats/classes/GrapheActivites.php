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
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * Statistics of operations per month
 *
 * Refactored from file dPstats\graph_activite
 */
class GrapheActivites
{
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
    /**@var CSalle */
    private CSalle $salle;
    /** @var int */
    private int $bloc_id;
    /** @var bool */
    private bool $hors_plage;
    /** @var string */
    private string $type_hospi;
    /** @var string */
    private string $codes_ccam;
    /** @var string */
    private string $debut = "";
    /** @var string */
    private string $fin = "";
    /** @var CMediusers */
    private CMediusers $praticien;

    /**
     * @throws Exception
     */
    public function __construct(
        ?string $debut = null,
        ?string $fin = null,
        int $prat_id = 0,
        int $salle_id = 0,
        int $bloc_id = 0,
        int $discipline_id = 0,
        string $codes_ccam = "",
        string $type_hospi = "",
        bool $hors_plage = true
    ) {
        $this->debut = $debut ?? CMbDT::date("-1 YEAR");
        $this->fin   = $fin ?? CMbDT::date();

        $prat            = new CMediusers();
        $this->praticien = $prat->load($prat_id);

        $this->discipline = CDiscipline::findOrNew($discipline_id);

        $this->salle = CSalle::findOrNew($salle_id);

        $this->bloc_id    = $bloc_id;
        $this->codes_ccam = $codes_ccam;
        $this->type_hospi = $type_hospi;
        $this->hors_plage = $hors_plage;
        $this->ds         = CSQLDataSource::get("std");

        $this->options = [
            "xaxis"       => [
                "labelsAngle" => 45,
            ],
            "yaxis"       => ["autoscaleMargin" => 5],
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
        for ($i = $this->debut; $i <= $this->fin; $i = CMbDT::date("+ 1 MONTH", $i)) {
            $this->ticks[]               = [count($this->ticks), CMbDT::transform("+ 0 DAY", $i, "%m/%Y")];
            $this->serie_total["data"][] = [count($this->serie_total["data"]), 0];
        }

        $salles = CSalle::getSallesStats($this->salle->_id, $this->bloc_id);
        // Gestion du hors plage
        $where_hors_plage = !$this->hors_plage ? "operations.plageop_id IS NOT NULL" : "";

        foreach ($salles as $salle) {
            $request   = new CRequest();
            $operation = new COperation();
            $request->addSelect(
                "COUNT(operations.operation_id) AS total,
                DATE_FORMAT(operations.date, '%m/%Y') AS mois,
                DATE_FORMAT(operations.date, '%Y%m') AS orderitem,
                sallesbloc.nom AS nom"
            );
            $ljoin = [
                "sejour"          => "operations.sejour_id = sejour.sejour_id",
                "sallesbloc"      => "operations.salle_id = sallesbloc.salle_id",
                "plagesop"        => "operations.plageop_id = plagesop.plageop_id",
                "users_mediboard" => "operations.chir_id = users_mediboard.user_id",
            ];

            $where = [
                "operations.annulee"  => $this->ds->prepare("= '0'"),
                "operations.date"     => $this->ds->prepareBetween($this->debut, $this->fin),
                "sejour.group_id"     => $this->ds->prepare("= ?", CGroups::loadCurrent()->_id),
                "sallesbloc.salle_id" => $this->ds->prepare("= ?", $salle->_id),
            ];
            $request->addLJoin($ljoin);
            $request->addTable($operation->_spec->table);
            $where[] = $where_hors_plage;
            if ($this->type_hospi) {
                $where["sejour.type"] = $this->ds->prepare("= ?", $this->type_hospi);
            }
            if ($this->praticien->_id && !$this->praticien->isFromType(["Anesthésiste"])) {
                $where["operations.chir_id"] = $this->ds->prepare("= ?", $this->praticien->_id);
            }
            if ($this->praticien->_id && $this->praticien->isFromType(["Anesthésiste"])) {
                $whereOr = [
                    "operations.anesth_id " . $this->ds->prepare("= ?", $this->praticien->_id),
                    "plagesop.anesth_id " . $this->ds->prepare("= ?", $this->praticien->_id),
                    "operations.anesth_id " . $this->ds->prepare("= 0"),
                    "operations.anesth_id " . $this->ds->prepare("IS NULL"),
                ];
                $where[] = implode(" OR ", $whereOr);
            }
            if ($this->discipline->_id) {
                $where["users_mediboard.discipline_id"] = $this->ds->prepare("= ?", $this->discipline->_id);
            }
            if ($this->codes_ccam) {
                $where["operations.codes_ccam"] = $this->ds->prepareLike("%$this->codes_ccam%");
            }

            $request->addWhere($where);
            $request->addGroup("mois");
            $request->addOrder("orderitem");

            $result = $this->ds->loadlist($request->makeSelect());
            $this->buildSerieData($result, $salle);
        }

        $this->buildGraphData();
    }

    private function buildSerieData(array $result, CSalle $salle): void
    {
        $serie = [
            "label" => $this->bloc_id ? $salle->nom : $salle->_view,
            "data"  => [],
        ];
        foreach ($this->ticks as $i => $tick) {
            $f = true;
            foreach ($result as $r) {
                if ($tick[1] == $r["mois"]) {
                    $serie["data"][]                  = [$i, (int)$r["total"]];
                    $this->serie_total["data"][$i][1] += (int)$r["total"];
                    $this->total                      += (int)$r["total"];
                    $f                                = false;
                    break;
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

        // Set up the title for the graph
        if ($this->praticien->_id && $this->praticien->isFromType(["Anesthésiste"])) {
            $title    = CAppUI::tr("GrapheActivites-title-nb anesth per room");
            $subtitle = $this->total . " " . CAppUI::tr("common-Operating anesthesie|pl");
        } else {
            $title    = CAppUI::tr("GrapheActivites-title-nb operation per room");
            $subtitle = $this->total . " " . CAppUI::tr("common-Operation|pl");
        }

        if ($this->praticien->_id) {
            $subtitle .= " - Dr $this->praticien";
        }
        if ($this->discipline->_id) {
            $subtitle .= " - $this->discipline";
        }
        if ($this->codes_ccam) {
            $subtitle .= " - CCAM : $this->codes_ccam";
        }
        if ($this->type_hospi) {
            $subtitle .= " - " . CAppUI::tr("CSejour . type . $this->type_hospi");
        }

        $this->options["title"]          = $title;
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
