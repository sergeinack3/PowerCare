<?php

/**
 * @package Interop\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CSQLDataSource;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Import\CExternalDataSourceImport;
use Ox\Core\Logger\LoggerLevels;

class CAsipImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'ASIP';
    public const DATA_DIR    = '../resources';

    public const FILES = [
        'asip' => [],
    ];

    private const TABLE = 'authorspecialty_20121112';

    private CReport $report;

    public function __construct()
    {
        parent::__construct(
            self::SOURCE_NAME,
            self::DATA_DIR,
            self::FILES
        );

        $this->report = new CReport('Mise à jour table ASIP');
    }

    public function importDatabase(?array $types = []): bool
    {
        $ds   = $this->setSource();
        $path = $this->getDataDir();

        if (!$ds) {
            $this->addMessage(["Import impossible - Aucune source de données", UI_MSG_ERROR]);

            return false;
        }

        $files = glob("$path/*.jv");

        $lineCount = 0;
        foreach ($files as $_file) {
            $name  = basename($_file);
            $name  = substr($name, strpos($name, "_") + 1);
            $table = substr($name, 0, strrpos($name, "."));
            $table = strtolower($table);
            if (!$ds) {
                $this->addMessage(["Import impossible - Source non présente", UI_MSG_WARNING]);
                continue;
            }

            if ($ds->loadTable($table)) {
                $this->addMessage(["La table a déjà été importée - Import impossible", UI_MSG_WARNING]);
                continue;
            }

            $ds->query(
                "CREATE TABLE IF NOT EXISTS `$table` (
                `table_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `code` VARCHAR (255) NOT NULL,
                `oid` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255) NOT NULL,
                INDEX (`table_id`)
              )/*! ENGINE=MyISAM */;"
            );

            $ds->query("DELETE FROM `$table`");

            $csv = new CCSVFile($_file);
            $csv->jumpLine(3);
            while ($line = $csv->readLine()) {
                [$oid, $code, $libelle] = $line;
                if (strpos($code, "/") === false) {
                    continue;
                }
                $query  = "INSERT INTO `$table`(`code`, `oid`, `libelle`) VALUES (?1, ?2, ?3);";
                $query  = $ds->prepare($query, $code, $oid, $libelle);
                $result = $ds->query($query);
                if (!$result) {
                    $msg = $ds->error();
                    $this->addMessage(["Erreur de requête SQL: $msg", UI_MSG_ERROR]);

                    return false;
                }
                $lineCount++;
            }
        }

        if ($lineCount) {
            $this->addMessage(["Import effectué avec succès de $lineCount lignes", UI_MSG_OK]);
        }

        return true;
    }

    /**
     * @param bool          $updateLibelle
     * @param array<object> $all_specialities
     *
     * @return CReport
     * @throws Exception
     */
    public function updateDatabase(array $all_specialities, bool $updateLibelle = true): CReport
    {
        $ds = $this->setSource();

        [$missing_codes_db, $wrong_libelle_specialities] = $this->computeUpdatableRows(
            $ds,
            $all_specialities,
            $updateLibelle
        );

        if (empty($missing_codes_db) && empty($wrong_libelle_specialities)) {
            $this->report->addData(
                CAppUI::tr('CSpecialtyAsip-msg-update none'),
                CItemReport::SEVERITY_SUCCESS
            );

            return $this->report;
        }

        $this->updateMissingCodes($ds, $all_specialities, $missing_codes_db);

        if ($updateLibelle) {
            $this->updateWrongLibelle($ds, $all_specialities, $wrong_libelle_specialities);
        }

        return $this->report;
    }

    /**
     * @param CSQLDataSource $ds
     * @param array<object>  $specialities
     * @param bool           $updateLibelle
     *
     * @return array
     * @throws Exception
     */
    private function computeUpdatableRows(CSQLDataSource $ds, array $specialities, bool $updateLibelle): array
    {
        $asip_objects = $this->loadAsipSpecialities($ds);

        $all_available_codes = array_keys($specialities);

        $wrong_libelle_specialities = [];
        $all_db_codes               = [];
        foreach ($asip_objects as $asip_object) {
            $id             = $asip_object->oid . "-" . $asip_object->code;
            $all_db_codes[] = $id;

            if (!$updateLibelle) {
                continue;
            }

            $speciality = $specialities[$id];
            if ($asip_object->libelle !== $speciality->libelle) {
                $wrong_libelle_specialities[$id] = $asip_object->table_id;
            }
        }

        $missing_codes_db = array_diff($all_available_codes, $all_db_codes);

        return [$missing_codes_db, $wrong_libelle_specialities];
    }


    /**
     * @param CSQLDataSource $ds
     * @param string         $table
     *
     * @return array
     * @throws Exception
     */
    private function loadAsipSpecialities(CSQLDataSource $ds): array
    {
        $all_rows     = $ds->exec("SELECT * from " . self::TABLE);
        $asip_objects = [];
        while ($obj = $ds->fetchObject($all_rows)) {
            $asip_objects[] = $obj;
        }

        return $asip_objects;
    }

    private function updateMissingCodes(?CSQLDataSource $ds, array $all_specialities, array $missing_codes_db): void
    {
        $item = new CItemReport(CAppUI::tr('CSpecialtyAsip-msg-add code|pl'), CItemReport::SEVERITY_SUCCESS);
        $table = self::TABLE;
        foreach ($missing_codes_db as $id) {
            $speciality = $all_specialities[$id];
            $query      = "INSERT INTO `$table` (`code`, `oid`, `libelle`) VALUES (?1, ?2, ?3);";
            $query      = $ds->prepare($query, $speciality->code, $speciality->oid, $speciality->libelle);
            if (!$ds->query($query)) {
                $msg = $ds->error();
                $item->addSubData(
                    "[code : $speciality->code] Erreur de requête SQL: $msg",
                    CItemReport::SEVERITY_ERROR
                );

                continue;
            }

            $item->addSubData($speciality->code . '/' . $speciality->libelle, CItemReport::SEVERITY_SUCCESS);
        }

        $this->report->addItem($item);
    }

    private function updateWrongLibelle(
        ?CSQLDataSource $ds,
        array $all_specialities,
        array $wrong_libelle_specialities
    ): void {
        $item = new CItemReport(CAppUI::tr('CSpecialtyAsip-msg-update libelle|pl'), CItemReport::SEVERITY_SUCCESS);
        $table = self::TABLE;
        foreach ($wrong_libelle_specialities as $id => $speciality_id) {
            $speciality = $all_specialities[$id];
            $query      = "UPDATE `$table` SET `libelle`=?1 WHERE `table_id`=?2;";
            $query      = $ds->prepare($query, $speciality->libelle, $speciality_id);
            if (!$ds->query($query)) {
                $msg = $ds->error();

                CApp::log(
                    $msg,
                    ['raison' => 'Update ASIP database', 'asip_code' => $speciality->code],
                    LoggerLevels::LEVEL_ERROR
                );

                $item->addSubData(
                    "[code : $speciality->code] Erreur de requête SQL: $msg",
                    CItemReport::SEVERITY_ERROR
                );

                continue;
            }

            $item->addSubData($speciality->code . '/' . $speciality->libelle, CItemReport::SEVERITY_SUCCESS);
        }

        $this->report->addItem($item);
    }
}
