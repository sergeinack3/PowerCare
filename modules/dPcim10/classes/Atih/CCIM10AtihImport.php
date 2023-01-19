<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Atih;

use Ox\Core\CAppUI;
use Ox\Core\Import\CExternalDataSourceImport;

class CCIM10AtihImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'cim10';
    public const DATA_DIR    = '../../base';

    public const CIM_FILES = ['cim_atih.tar.gz', 'cim10_atih.sql'];

    public const FILES = [
        'cim10_atih' => self::CIM_FILES,
    ];

    public const CSV_FILE = 'cim10_atih.csv';

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

        return $this->importDataFromCsv();
    }

    public function importDataFromCsv(): bool
    {
        $csv = $this->getTmpDir() . self::CSV_FILE;
        $ds  = $this->getSource();

        $chapters = [];

        /* We create a data structure for getting the parent category of a code */
        $result = $ds->exec("SELECT id, libelle FROM chapters_atih WHERE parent_id = 0;");

        while ($chapter = $ds->fetchAssoc($result)) {
            $codes = explode(
                '-',
                substr($chapter['libelle'], strpos($chapter['libelle'], '(') + 1, 7)
            );
            $chapters[$chapter['id']] = [0 => $codes[0], 1 => $codes[1], 'categories' => []];
        }

        /* We get all the categories, add them to the chapters categories */
        $result = $ds->exec("SELECT id, code, parent_id FROM chapters_atih WHERE parent_id != 0;");

        while ($chapter = $ds->fetchAssoc($result)) {
            $codes = explode(
                '-',
                str_replace(['(', ')'], '', $chapter['code'])
            );
            $chapters[$chapter['parent_id']]['categories'][$chapter['id']] = $codes;
        }

        $file = fopen($csv, 'r');

        $id               = 1;
        $entries          = [];
        $codes_categories = [];
        while ($line = fgetcsv($file, null, '|')) {
            $code        = trim($line[0]);
            $parent_code = substr($code, 0, 3);

            $ssr_fppec = $line[2][0] == 'O' ? '1' : '0';
            $ssr_mmp   = $line[2][1] == 'O' ? '1' : '0';
            $ssr_ae    = $line[2][2] == 'O' ? '1' : '0';
            $ssr_das   = $line[2][3] == 'O' ? '1' : '0';

            $lib_court = str_replace("'", "\\'", $line[4]);
            $lib_long  = str_replace("'", "\\'", $line[5]);

            $category = null;
            if (!array_key_exists($parent_code, $codes_categories)) {
                foreach ($chapters as $chapter) {
                    if ($parent_code >= $chapter[0] && $parent_code <= $chapter[1]) {
                        foreach ($chapter['categories'] as $_id => $category) {
                            if (
                                (count($category) == 2 && $parent_code >= $category[0] && $parent_code <= $category[1])
                                || (count($category) == 1 && $parent_code == $category[0])
                            ) {
                                $category                       = $_id;
                                $codes_categories[$parent_code] = $category;
                                break;
                            }
                        }
                        break;
                    }
                }
            } else {
                $category = $codes_categories[$parent_code];
            }

            if ($category) {
                $entries[] = "($id, '$code', '{$line[1]}', '$ssr_fppec', '$ssr_mmp', '$ssr_ae', '$ssr_das', '{$line[3]}',"
                    . "'$lib_court', '$lib_long', $category)";

                $id++;
            }
        }

        $query = "INSERT INTO codes_atih (id, code, type_mco, ssr_fppec, ssr_mmp, ssr_ae, ssr_das, type_psy,"
            . "libelle_court, libelle, category_id) VALUES\n";
        $query .= implode(",\n", $entries) . ';';

        if (!$ds->exec($query)) {
            $msg = $ds->error();
            $this->addMessage(["Erreur de requête SQL: $msg", UI_MSG_ERROR]);
            return false;
        } else {
            $this->addMessage(["Import de " . count($entries) . " codes", UI_MSG_OK]);
            return true;
        }
    }
}
