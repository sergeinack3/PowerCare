<?php

/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement;

use Ox\Core\Import\CExternalDataSourceImport;

/**
 * Description
 */
class CSaeImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'sae';
    public const DATA_DIR    = '../base';

    public const SAE_FILE_NAME = ['sae.tar.gz', 'sae.sql'];

    public const FILES = [
        'sae' => self::SAE_FILE_NAME,
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
