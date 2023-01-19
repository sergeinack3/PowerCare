<?php
/**
 * @package Mediboard\Provenance
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Provenance;

use Ox\Core\Handlers\HandlerParameterBag;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Class CConfigurationProvenance
 *
 * @package Ox\Mediboard\Provenance
 */
class CConfigurationProvenance extends AbstractConfigurationRegister {
  /**
   * @inheritDoc
   */
  public function register() {
    CConfiguration::register([]);
  }

  /**
   * Déclaration du handler des provenances patient
   *
   * @return array
   */
  public function getObjectHandlers(HandlerParameterBag $parameter_bag): void {
      $parameter_bag
          ->register(CProvenancePatientHandler::class, false);
  }
}
