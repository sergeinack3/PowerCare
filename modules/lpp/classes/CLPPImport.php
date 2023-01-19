<?php

/**
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp;

use Ox\Core\CMbArray;
use Ox\Core\CMbPath;
use Ox\Core\CSQLDataSource;
use Ox\Core\Database\CDBF;
use Ox\Core\Import\CExternalDataSourceImport;

class CLPPImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'lpp';
    public const DATA_DIR    = '../base';

    public const LPP_IMPORT_FILES = ['lpp_chapters.tar.gz', 'lpp_chapters.sql'];

    public const FILES = [
        'lpp' => self::LPP_IMPORT_FILES,
    ];

    public const LPP_REMOTE_DATA_PATH  = 'http://www.codage.ext.cnamts.fr/f_mediam/fo/tips/LPP.zip';
    public const LPP_REMOTE_OUTPUT     = 'LPP.zip';

    public function __construct()
    {
        parent::__construct(
            self::SOURCE_NAME,
            self::DATA_DIR,
            self::FILES
        );
    }

    public function importDatabase(?array $types = [], string $action = 'all'): bool
    {
        $result = $this->importRemoteData();
        if (false === $result) {
            return false;
        }
        return parent::importDatabase($types);
    }

    private function importRemoteData(): bool
    {
        $targetDir  = $this->getTmpDir();
        $sourcePath = $targetDir . self::LPP_REMOTE_OUTPUT;

        CMbPath::forceDir($targetDir);

        // Download the archive
        file_put_contents($this->getTmpDir() . self::LPP_REMOTE_OUTPUT, file_get_contents(self::LPP_REMOTE_DATA_PATH));

        // Extract the data files
        if (null == $nbFiles = CMbPath::extract($sourcePath, $targetDir)) {
            $this->addMessage(["Erreur, impossible d'extraire l'archive", UI_MSG_ERROR]);
            return false;
        }

        $this->addMessage(["Extraction de $nbFiles fichier(s)", UI_MSG_OK]);

        $ds = $this->setSource();

        $tables = array(
            "fiche"  => "lpp_fiche_tot*.dbf",
            "comp"   => "lpp_comp_tot*.dbf",
            "incomp" => "lpp_incomp_tot*.dbf",
            "histo"  => "lpp_histo_tot*.dbf",
        );

        $db_types = array(
            "C" => "VARCHAR",
            "D" => "DATE",
            "N" => "NUMBER",
        );

        foreach ($tables as $table => $filename) {
            $files     = glob("$targetDir/$filename");
            $file      = reset($files);
            $dbf       = new CDBF($file);
            $num_rec   = $dbf->dbf_num_rec;
            $field_num = $dbf->dbf_num_field;

            $query = "DROP TABLE IF EXISTS $table";
            $ds->exec($query);

            $this->addMessage(["Table <strong>$table</strong> supprimée", UI_MSG_OK]);

            // Table creation
            $query = "CREATE TABLE $table (";

            $cols = array();
            foreach ($dbf->dbf_names as $i => $col) {
                switch ($col['type']) {
                    case "C":
                        $cols[] = "{$col['name']} VARCHAR({$col['len']})";
                        break;
                    case "D":
                        $cols[] = "{$col['name']} DATE";
                        break;
                    case "N":
                        $cols[] = "{$col['name']} FLOAT";
                        break;
                    default:
                }
            }

            $query .= implode(", ", $cols);
            $query .= ")/*! ENGINE=MyISAM */";

            $ds->exec($query);

            $this->addMessage(["Table <strong>$table</strong> re-créee", UI_MSG_OK]);

            // Table insertion
            $query_start = "INSERT INTO $table (";
            $query_start .= implode(", ", CMbArray::pluck($dbf->dbf_names, "name"));
            $query_start .= ") VALUES";

            for ($i = 0; $i < $num_rec; $i++) {
                $query  = $query_start;
                $values = array();

                if ($row = $dbf->getRow($i)) {
                    foreach ($dbf->dbf_names as $j => $col) {
                        switch ($col['type']) {
                            case "C":
                                $values[] = '"' . addslashes($row[$j]) . '"';
                                break;

                            case "N":
                                $values[] = (($row[$j] === "") ? "NULL" : $row[$j]);
                                break;

                            case "D":
                                $date = "NULL";
                                if (preg_match("/(\d{4})(\d{2})(\d{2})/", $row[$j], $parts)) {
                                    $date = "\"$parts[1]-$parts[2]-$parts[3]\"";
                                }
                                $values[] = $date;
                                break;
                            default:
                        }
                    }
                }

                $query .= '(' . implode(", ", $values) . ')';
                $ds->exec($query);
            }

            $this->addMessage(["$num_rec enregistrements ajoutés à la table <strong>$table</strong>", UI_MSG_OK]);
        }
        return true;
    }
}
