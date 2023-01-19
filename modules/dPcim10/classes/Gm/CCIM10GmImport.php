<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Gm;

use Ox\Core\Import\CExternalDataSourceImport;

class CCIM10GmImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'cim10';
    public const DATA_DIR    = '../../base';

    public const FILES = [
        'cim10_gm' => ['cim10_gm.tar.gz', 'tables.sql', 'data.sql'],
    ];

    public function __construct()
    {
        parent::__construct(
            self::SOURCE_NAME,
            self::DATA_DIR,
            self::FILES
        );
    }
}
