<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Class CConfigurationImportTools
 */
class CConfigurationImportTools extends AbstractConfigurationRegister {

  /**
   * @return void
   */
  public function register() {
    CConfiguration::register(
      array(
        'CGroups' => array(
          'importTools' => array(
            'export' => array(
              'root_path'  => 'str',
              'actif'      => 'bool default|0',
              'date_min'   => 'str',
              'date_max'   => 'str',
              'praticiens' => 'str',
              'tag'        => 'str',
            ),
            'import' => array(
              'root_path'      => 'str',
              'actif'          => 'bool default|0',
              'date_min'       => 'str',
              'date_max'       => 'str',
              'tag'            => 'str',
              'import_tag'     => 'str default|migration',
              'source_ipp_tag' => 'str',
              'copy_files'     => 'bool default|0',
            ),
          )
        )
      )
    );
  }
}