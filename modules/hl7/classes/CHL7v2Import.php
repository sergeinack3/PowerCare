<?php

/**
 * @package Interop\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Core\Import\CExternalDataSourceImport;

class CHL7v2Import extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'hl7v2';
    public const DATA_DIR    = '../base';

    public const HL7V2_FILE_NAME = ['hl7v2.zip', 'hl7v2.sql'];

    public const FILES = [
        'hl7v2' => self::HL7V2_FILE_NAME,
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
