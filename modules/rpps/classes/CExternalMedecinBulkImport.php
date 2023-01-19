<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps;

use DateTime;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CSQLDataSource;
use Ox\Import\Rpps\Exception\CImportMedecinException;
use PDO;

/**
 * My class
 */
class CExternalMedecinBulkImport
{
    public const DSN = 'rpps_import';

    public const CREATE_TABLE_SCRIPT_NAME = 'create_tables.sql';

    public const FILE_NAME_PERSONNE_EXERCICE    = 'personne_exercice.txt';
    public const FILE_NAME_SAVOIR_FAIRE         = 'savoir_faire.txt';
    public const FILE_NAME_DIPLOME_AUTORISATION = 'diplome_autorisation.txt';

    public const FILE_NAME_MSSANTE = 'correspondance_mssante.txt';

    public const TABLE_PERSONNE_EXERCICE             = 'personne_exercice';
    public const TABLE_SAVOIR_FAIRE                  = 'savoir_faire';
    public const TABLE_DIPLOME_AUTORISATION_EXERCICE = 'diplome_autorisation_exercice';

    public const TABLE_MSSANTE = 'mssante_info';

    public const FILES_TO_TABLES = [
        self::FILE_NAME_PERSONNE_EXERCICE    => self::TABLE_PERSONNE_EXERCICE,
        self::FILE_NAME_SAVOIR_FAIRE         => self::TABLE_SAVOIR_FAIRE,
        self::FILE_NAME_DIPLOME_AUTORISATION => self::TABLE_DIPLOME_AUTORISATION_EXERCICE,
        self::FILE_NAME_MSSANTE              => self::TABLE_MSSANTE,
    ];

    /** @var CSQLDataSource */
    private $ds;

    /**
     * CExternalMedecinBulkImport constructor.
     *
     * @param bool $init_ds
     */
    public function __construct(bool $init_ds = true)
    {
        if ($init_ds) {
            // Allow bulk operations (LOAD DATA LOCAL INFILE) for the datasource
            $this->ds = CSQLDataSource::get(self::DSN, false, [PDO::MYSQL_ATTR_LOCAL_INFILE => true]);
        }
    }

    /**
     * Create tables for Rpps
     *
     * @return bool
     * @throws Exception
     */
    public function createSchema(): bool
    {
        if (!$this->ds) {
            return false;
        }

        $this->removeTables();

        $file_path = dirname(
                __DIR__
            ) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . self::CREATE_TABLE_SCRIPT_NAME;
        $queries   = file_get_contents($file_path);

        return (bool)$this->ds->exec($queries);
    }

    /**
     * @return void
     * @throws CImportMedecinException
     */
    private function removeTables(): void
    {
        foreach (self::FILES_TO_TABLES as $_tbl_name) {
            if (!$this->ds->exec("DROP TABLE IF EXISTS `{$_tbl_name}`")) {
                throw new CImportMedecinException("Error while removing table {$_tbl_name}");
            }
        }
    }

    /**
     * @param bool $truncate_tables
     *
     * @return array
     * @throws CImportMedecinException
     */
    public function bulkImport(bool $truncate_tables = true): array
    {
        if (!$this->ds) {
            return [];
        }

        // If column error is missing create the tables
        if (
            !$this->ds->hasTable('personne_exercice') || !$this->ds->hasField('personne_exercice', 'error')
            || !$this->ds->hasTable('mssante_info')
        ) {
            $this->createSchema();
        }

        $messages = [];
        if ($truncate_tables) {
            $messages = array_merge($messages, $this->truncateTables());
        }

        foreach (self::FILES_TO_TABLES as $_file_name => $_tbl_name) {
            $messages[] = $this->importFileToTable($_file_name, $_tbl_name);
        }

        return $messages;
    }

    /**
     *
     * @return array
     * @throws CImportMedecinException
     */
    private function truncateTables(): array
    {
        $messages = [];

        foreach (self::FILES_TO_TABLES as $_tbl_name) {
            if ($this->ds->hasTable($_tbl_name)) {
                if (!$this->ds->exec("TRUNCATE TABLE `{$_tbl_name}`")) {
                    throw new CImportMedecinException('Error while truncating table ' . $_tbl_name);
                }

                $messages[] = [
                    "CExternalMedecinBulkImport-msg-Info-Table %s truncated in %s seconds",
                    $_tbl_name,
                    number_format($this->ds->chrono->latestStep, 3, ',', ' '),
                ];
            }
        }

        return $messages;
    }

    /**
     * @param string $_file_name
     * @param string $_tbl_name
     *
     * @return array
     * @throws CImportMedecinException
     */
    private function importFileToTable(string $_file_name, string $_tbl_name): array
    {
        if (!array_key_exists($_file_name, self::FILES_TO_TABLES)) {
            throw new CImportMedecinException('File is not part of RPPS files : ' . $_file_name);
        }

        if (!in_array($_tbl_name, self::FILES_TO_TABLES)) {
            throw new CImportMedecinException("Table {$_tbl_name} is not part of RPPS tables");
        }

        $file_path = self::getUploadDirectory() . DIRECTORY_SEPARATOR . $_file_name;

        $file_version = date('Y-m-d', filemtime($file_path));

        $query = $this->ds->prepare(
            "LOAD DATA LOCAL INFILE ?1
            INTO TABLE `$_tbl_name`
            CHARACTER SET UTF8
            FIELDS TERMINATED BY '|'
            IGNORE 1 LINES
            SET version = ?2,
            synchronized = '0',
            error = '0'",
            $file_path,
            $file_version
        );

        if (!$this->ds->exec($query)) {
            throw new CImportMedecinException("An error occured while importing in table {$_tbl_name}");
        }

        $count_rows = $this->ds->affectedRows();

        return [
            "CExternalMedecinBulkImport-msg-Info-%s rows imported in %s seconds in table %s",
            $count_rows,
            number_format($this->ds->chrono->latestStep, 3, ',', ' '),
            $_tbl_name,
        ];
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function getUploadDirectory(): string
    {
        return (CAppUI::conf('rpps download_directory')) ?: sprintf(
            '%s/%s/upload/rpps_import',
            rtrim(realpath(CAppUI::conf("root_dir")), '/'),
            (rtrim(CAppUI::conf("dPfiles CFile upload_directory"), '/')) ?: 'files'
        );
    }

    /**
     * @throws Exception
     */
    public function canLoadLocalInFile(): bool
    {
        if (!$this->ds) {
            return false;
        }

        return $this->ds->loadResult('SELECT @@GLOBAL.local_infile') || ini_get('mysqli.allow_local_infile');
    }
}
