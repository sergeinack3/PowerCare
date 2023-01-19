<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Oms;

use Ox\Core\CAppUI;
use Ox\Core\Import\CExternalDataSourceImport;
use Ox\Mediboard\Cim10\CImportCim10;

class CCIM10OmsImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'cim10';
    public const DATA_DIR    = '../../base';

    public const OMS_IMPORT_FILES = ['cim10.tar.gz', 'cim10.sql'];

    public const FILES = [
        'cim10_oms' => self::OMS_IMPORT_FILES,
    ];

    public const OMS_UPDATE_ARCHIVE  = 'cim10_modifs.tar.gz';
    public const OMS_UPDATE_CSV_FILE = 'cim10_modifs.csv';

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
        switch ($action) {
            case 'import':
                return parent::importDatabase($types);
            case 'update':
                return $this->importDataFromCsv();
            case 'all':
            default:
                $result = parent::importDatabase($types);
                if (false === $result) {
                    return false;
                }
                return $this->importDataFromCsv();
        }
    }

    public function importDataFromCsv(): bool
    {
        $this->setSource();
        $this->extractData(self::OMS_UPDATE_ARCHIVE);

        (new CImportCim10(
            $this->getTmpDir() . self::OMS_UPDATE_CSV_FILE,
            $this->getSource(),
            $this
        ))->run();

        return true;
    }
}
