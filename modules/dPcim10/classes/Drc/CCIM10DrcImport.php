<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Drc;

use Exception;
use Ox\Core\Import\CExternalDataSourceImport;

class CCIM10DrcImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'drc';
    public const DATA_DIR    = '../../base';

    public const DRC_FILES = ['drc.tar.gz', 'tables.sql', 'data.sql'];

    public const FILES = [
        'drc' => self::DRC_FILES,
    ];

    public function __construct()
    {
        parent::__construct(
            self::SOURCE_NAME,
            self::DATA_DIR,
            self::FILES
        );
    }

    /**
     * @throws Exception
     */
    protected function importFile(string $file_name): bool
    {
        try {
            if (!$this->ds) {
                return true;
            }

            if (!$this->ds->exec(file_get_contents($this->tmp_dir . $file_name))) {
                throw new Exception($this->getClassName() . '-Error-File, an error occured in query');
            }

            $this->addMessage([$this->getClassName() . '-Info-File imported, query executed', UI_MSG_OK, $file_name]);
        } catch (Exception $e) {
            $this->addMessage([$e->getMessage(), UI_MSG_WARNING, $this->ds->error()]);
            return false;
        }

        return true;
    }
}
