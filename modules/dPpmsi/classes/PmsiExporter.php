<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Export des séjours PMSI
 */
class PmsiExporter implements IShortNameAutoloadable
{
    /** @var string */
    public const CURRENT_OPERATION = "planned";
    /** @var string */
    public const URGENT_OPERATION = "unplanned";
    /** @var string[] */
    public const COLUMNS_NAMES = [
        "NDA",
        "CHIRURGIEN",
        "PATIENT",
        "DATE_INTERVENTION",
        "HEURE_PREVUE",
        "LIBELLES_ACTES_PREVUS",
        "BACTERIO",
        "ANAPATH",
    ];
    /** @var string */
    public const EXPORT_FILE_NAME_PREFIX = "PMSI_";

    /** @var string */
    public $date_min;

    /** @var string */
    public $date_max;

    /** @var string */
    public $type_operation = "planned";

    /** @var array */
    public $types;

    /**
     * @param string|null $date_min
     * @param string|null $date_max
     * @param array       $type_adm
     * @param string      $type_operation
     *
     * @return int
     * @throws CMbException
     */
    public function exportOperationsToCsv(
        ?string $date_min,
        ?string $date_max,
        array $type_adm = [],
        string $type_operation = "planned"
    ): int {
        if ((!$date_min || !$date_max) || CMbDT::date($date_max) < CMbDT::date($date_min)) {
            throw new CMbException('PMSI-export-operation-error-invalid_period');
        }

        $this->date_min       = $date_min;
        $this->date_max       = $date_max;
        $this->types          = $type_adm;
        $this->type_operation = $type_operation;

        switch ($this->type_operation) {
            case self::CURRENT_OPERATION:
            default:
                $operations = $this->getCurrentOperations();
                break;
        }

        $sejours = CStoredObject::massLoadFwdRef($operations, "sejour_id");
        CSejour::massLoadNDA($sejours);
        $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");

        $count = 0;
        if (count($operations)) {
            $csv = $this->generateCSVFile();
            foreach ($operations as $_operation) {
                if (!$_operation->getPerm(PERM_READ)) {
                    continue;
                }
                $csv->writeLine(
                    $this->writeLinePmsiOperation($_operation, $patients[$_operation->loadRefSejour()->patient_id])
                );
                $count++;
            }

            $this->streamFile($csv);
        }

        return $count;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getCurrentOperations(): array
    {
        $ds = CSQLDataSource::get("std");
        // Selection des salles
        $listSalles = new CSalle();
        $listSalles = $listSalles->loadGroupList();
        $where      = [
            "date"     => $ds->prepareBetween($this->date_min, $this->date_max),
            "salle_id" => $ds->prepareIn(array_keys($listSalles)),
        ];
        $order      = "debut";

        // Récupération des plages
        $plage = new CPlageOp();
        /** @var CPlageOp[] $plages */
        $plages    = $plage->loadList($where, $order);
        $operation = new COperation();

        $where = [
            "plageop_id" => $ds->prepareIn(array_keys($plages)),
            "annulee"    => $ds->prepare("= '0'"),
        ];
        $ljoin = [];

        if ($this->types && !in_array("", $this->types)) {
            $ljoin["sejour"]      = "sejour.sejour_id = operations.sejour_id";
            $where["sejour.type"] = CSQLDataSource::prepareIn($this->types);
        }

        return $operation->loadList($where, null, null, null, $ljoin);
    }

    /**
     * @return CCSVFile
     */
    public function generateCSVFile(): CCSVFile
    {
        $csv = new CCSVFile(null, CCSVFile::PROFILE_EXCEL);
        $csv->writeLine(self::COLUMNS_NAMES);

        return $csv;
    }

    /**
     * Stream the CSV File created
     *
     * @param CCSVFile $csv_file
     */
    public function streamFile(CCSVFile $csv_file): void
    {
        $filename = self::EXPORT_FILE_NAME_PREFIX . $this->date_min . "_" . $this->date_max;
        $csv_file->stream($filename, true);
    }

    /**
     * From an operation and a patient, write a line in the CSV File
     *
     * @param COperation $operation
     * @param CPatient   $patient
     *
     * @return array
     */
    protected function writeLinePmsiOperation(COperation $operation, CPatient $patient): array
    {
        return [
            $operation->_ref_sejour->_NDA,
            $operation->_ref_chir->_view,
            "$patient->nom $patient->prenom",
            $operation->date,
            explode(" ", $operation->_datetime)[1],
            $operation->getLibellesActesPrevus(),
            $operation->labo,
            $operation->anapath,
        ];
    }
}
