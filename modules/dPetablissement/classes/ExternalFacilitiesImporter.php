<?php

/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\FileUtil\CCSVFile;

/**
 * Import external facilities from a csv file
 */
class ExternalFacilitiesImporter
{
    private const DIR_IMPORT = "tmp/import_etab_externe";

    private array $file;

    private array $import_result = [
        "created" => 0,
        "updated" => 0,
        "error"   => 0,
    ];

    /**
     * @throws CMbException
     */
    public function __construct(array $file)
    {
        if (!array_key_exists('tmp_name', $file) || $file['tmp_name'][0] == '') {
            throw new CMbException('common-error-No file found.');
        }
        $this->file = $file;
        CMbPath::forceDir(self::DIR_IMPORT);
    }

    /**
     * Start import
     * @throws Exception
     */
    public function doImport(): void
    {
        $csv = $this->getFile();
        while ($line = $csv->readLine()) {
            $line = array_map([$this, "getValue"], $line);
            if ($line[0] === "" || $line[3] === "") {
                // Vérification de la présence du nom et du FINESS
                continue;
            }
            $this->editFacilityFromData($line);
        }
    }

    /**
     * Get uploaded CSV File
     */
    public function getFile(): CCSVFile
    {
        $csv = new CCSVFile($this->file['tmp_name'][0], CCSVFile::PROFILE_EXCEL);
        $csv->jumpLine(1);

        return $csv;
    }

    /**
     * @throws Exception
     */
    private function editFacilityFromData(array $line): void
    {
        $etab         = new CEtabExterne();
        $etab->finess = $this->getNum($line[0]);

        $etab->loadMatchingObject();
        $status               = $etab->_id ? "updated" : "created";
        $etab->siret          = $this->getNum($line[1]);
        $etab->ape            = $line[2] === "" ? $etab->ape : $line[2];
        $etab->nom            = $line[3] === "" ? $etab->nom : $line[3];
        $etab->raison_sociale = $line[4] === "" ? $etab->raison_sociale : $line[4];
        $etab->adresse        = $line[5] === "" ? $etab->adresse : $line[5];
        $etab->cp             = $line[6] === "" ? $etab->cp : $line[6];
        $etab->ville          = $line[7] === "" ? $etab->ville : $line[7];
        $etab->tel            = $line[8] === "" ? $etab->tel : $this->getNum($line[8]);
        $etab->fax            = $line[9] === "" ? $etab->fax : $this->getNum($line[9]);
        $etab->provenance     = $line[10] === "" ? $etab->provenance : $this->getNum($line[10]);
        $etab->destination    = $line[11] === "" ? $etab->destination : $this->getNum($line[11]);
        $etab->priority       = $line[12] === "" ? $etab->priority : $this->getNum($line[12]);
        $etab->repair();

        if ($msg = $etab->store()) {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
            $this->import_result["error"]++;
        } else {
            $this->import_result[$status]++;
        }
    }

    /**
     * @return array
     */
    public function getImportResult(): array
    {
        return $this->import_result;
    }

    /**
     * Get the value matching ^="(.*)"$
     */
    private function getValue(?string $value): string
    {
        if (preg_match('/^="(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        }

        return trim($value, " \t\n\r\0\"'");
    }

    /**
     * Removes all all numeric chars from a string
     */
    private function getNum(?string $value): string
    {
        return preg_replace("/[^0-9]/", "", $value);
    }
}
