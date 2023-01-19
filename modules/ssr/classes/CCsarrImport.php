<?php

/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;
use Ox\Core\Import\CExternalDataSourceImport;
use const DIRECTORY_SEPARATOR;

/**
 * Description
 */
class CCsarrImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'csarr';
    public const DATA_DIR    = '../base';
    public const PREFIX      = 'csarr_v2021' . DIRECTORY_SEPARATOR;

    public const CSARR_FILES = ['csarr_v2021.zip', self::PREFIX . 'tables.sql'];

    public const FILES = [
        'csarr' => self::CSARR_FILES,
    ];

    public function __construct()
    {
        parent::__construct(
            self::SOURCE_NAME,
            self::DATA_DIR,
            self::FILES
        );
    }

    public function importDatabase(?array $types = []): bool
    {
        $importResult = parent::importDatabase($types);

        if (false === $importResult) {
            return false;
        }

        $listTables = [
            "activite"             => self::PREFIX . "code_csarr.txt",
            "note_activite"        => self::PREFIX . "note_code_csarr.txt",
            "geste_complementaire" => self::PREFIX . "geste_compl_csarr.txt",
            "modulateur"           => self::PREFIX . "modulateur_csarr.txt",
            "hierarchie"           => self::PREFIX . "hier_csarr.txt",
            "note_hierarchie"      => self::PREFIX . "note_hier_csarr.txt",
            "activite_reference"   => self::PREFIX . "acte_ref_csarr.txt",
            "code_extension"       => self::PREFIX . "code_extension_documentaire_csarr.txt"
        ];

        foreach ($listTables as $table => $file) {
            $result = $this->addFileIntoDB($this->getTmpDir() . $file, $table);
            if (false === $result) {
                return false;
            }
        }

        return true;
    }

    public function addFileIntoDB(string $file, string $table): bool
    {
        $reussi = 0;
        $echoue = 0;
        $ignore = 0;
        $ds     = $this->getSource();
        $handle = fopen($file, "r");

        // First line is hearders
        fgets($handle);

        // Ne pas utiliser fgetcsv, qui refuse de prendre en compte les caractères en majusucules accentués (et d'autres caractères spéciaux)
        while ($line = fgets($handle)) {
            $line = str_replace("'", "\'", $line);
            $data = explode("|", $line);
            $data = array_map("trim", $data);

            static $note_ignores = array(
                "À l\\'exclusion de :",
                "Cet acte comprend :",
                "Avec ou sans :",
                "Codage :",
            );

            // CNoteActivite: Traitements spécifiques
            if ($table == "note_activite") {
                // Nettoyage des termes à ignorer
                foreach ($note_ignores as $_ignore) {
                    if (strpos($data[4], $_ignore) === 0) {
                        $data[4] = trim(substr($data[4], strlen($_ignore)));
                        if (empty($data[4])) {
                            $ignore++;
                            continue 2;
                        }
                    }
                }

                // Détection du code à exclure
                $data[6] = "";
                if (preg_match("/\(([a-z]{3}\+\d{3})\)/i", $data[4], $matches)) {
                    $data[6] = $matches[1];
                }
            }

            // CNoteHierarchie: Traitements spécifiques
            if ($table == "note_hierarchie") {
                // Nettoyage des termes à ignorer
                foreach ($note_ignores as $_ignore) {
                    if (strpos($data[4], $_ignore) === 0) {
                        $data[4] = trim(substr($data[4], strlen($_ignore)));
                        if (empty($data[4])) {
                            $ignore++;
                            continue 2;
                        }
                    }
                }

                // Détection de la hierarchie à exclure
                $data[6] = "";
                if (preg_match("/\(((\d{2}\.)+\d{2})\)/i", $data[4], $matches)) {
                    $data[6] = $matches[1];
                }

                // Détection du code à exclure
                $data[7] = "";
                if (preg_match("/\(([a-z]{3}\+\d{3})\)/i", $data[4], $matches)) {
                    $data[7] = $matches[1];
                }
            }

            $query = "INSERT INTO $table VALUES('" . implode("','", $data) . "')";

            $ds->exec($query);
            if ($ds->error()) {
                $echoue++;
            } else {
                $reussi++;
            }
        }

        fclose($handle);
        $this->addMessage(["ssr-import-csarr-report", UI_MSG_OK, $file, $table, $ignore, $reussi, $echoue]);

        return ($echoue === 0);
    }
}
