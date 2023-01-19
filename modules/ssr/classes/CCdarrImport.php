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

/**
 * Description
 */
class CCdarrImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'cdarr';
    public const DATA_DIR    = '../base';

    public const CDARR_FILES = ['nomenclature.CdARR.tar.gz', 'tables.sql'];

    public const FILES = [
        'cdarr' => self::CDARR_FILES,
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
            "intervenant"   => "LIBINTERCD.TXT",
            "type_activite" => "TYPACTSSR.TXT",
            "activite"      => "CATSSR.TXT"
        ];

        foreach ($listTables as $table => $file) {
            $this->addFileIntoDB($this->getTmpDir() . $file, $table);
        }

        return true;
    }

    public function addFileIntoDB(string $file, string $table): bool
    {
        $reussi = 0;
        $echoue = 0;
        $ds     = $this->getSource();
        $handle = fopen($file, "r");

        // Ne pas utiliser fgetcsv, qui refuse de prendre en compte les caractères en majusucules accentués (et d'autres caractères spéciaux)
        while ($line = fgets($handle)) {
            $line  = str_replace("'", "\'", $line);
            $datas = explode("|", $line);
            $query = "INSERT INTO $table VALUES('" . implode("','", $datas) . "')";

            $ds->exec($query);
            if ($msg = $ds->error()) {
                $echoue++;
            } else {
                $reussi++;
            }
        }

        fclose($handle);
        $this->addMessage(["ssr-import-cdarr-report", UI_MSG_OK, $file, $table, $reussi, $echoue]);

        return ($echoue === 0);
    }
}
