<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\Import\CExternalDataSourceImport;

/**
 * Description
 */
class CInseeImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'INSEE';
    public const DATA_DIR    = '../INSEE';
    public const INSEE_FILE_NAME = ['insee.tar.gz', 'insee.sql'];
    public const COUNTRIES_FILE_NAME = ['countries.tar.gz', 'countries.sql'];
    public const COMMUNES_FRANCE_FILE_NAME = ['communes_france.tar.gz', 'communes_france.sql'];
    public const FILES = [
        'insee'     => self::INSEE_FILE_NAME,
        'countries' => self::COUNTRIES_FILE_NAME,
        'communes'  => self::COMMUNES_FRANCE_FILE_NAME,
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
