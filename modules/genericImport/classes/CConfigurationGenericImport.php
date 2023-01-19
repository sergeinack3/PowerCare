<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Description
 */
class CConfigurationGenericImport extends AbstractConfigurationRegister
{
    public function register()
    {
        CConfiguration::register(
            [
                'CImportCampaign' => [
                    'genericImport' => [
                        'db'           => [
                            'dsn' => 'enum list|' . implode('|', GenericImportSql::DATA_SOURCES)
                                . ' default|' . GenericImportSql::DATA_SOURCE_1,
                        ],
                        'import_files' => [
                            'external_files_path'             => 'str',
                            'external_files_replacement_path' => 'str',
                            'find_existing_files'             => 'bool default|1',
                        ],
                        'import_dates' => [
                            'plageconsult_heure_debut' => 'time default|08:00:00',
                            'plageconsult_heure_fin'   => 'time default|20:00:00',
                        ],
                        'interop'      => [
                            'generate_ipp' => 'bool default|0',
                            'generate_nda' => 'bool default|0',
                        ],
                    ],
                ],
            ]
        );
    }
}
