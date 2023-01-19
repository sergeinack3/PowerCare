<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\Import\CExternalDataSourceImport;

class CCCAMImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'ccamV2';
    public const DATA_DIR    = '../base';

    public const FILES = [
        'ccamv2' => [
            'ccam.tar.gz',
            'tables.sql',
            'basedata.sql',
            'base.sql',
            'pmsi.sql',
        ],
        'ccamv2_forfaits' => [
            'forfaits_ccam.tar.gz',
            'forfaits_ccam.sql',
        ],
        'ngap' => [
            'ngap.tar.gz',
            'ngap.sql',
        ]
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
