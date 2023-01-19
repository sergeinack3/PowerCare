<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;

/**
 * Description
 */
class CConfigurationStrategy implements IShortNameAutoloadable {
  /** @var IConfigurationStrategy */
  private $strategy;

  /**
   * CConfigurationStrategy constructor.
   *
   * @param IConfigurationStrategy|null $strategy
   *
   * @throws Exception
   */
  public function __construct(IConfigurationStrategy $strategy = null) {
    if (is_null($strategy)) {
      $strategy = CConfigurationModelManager::getStrategy();
    }

    $this->setStrategy($strategy);
  }

  /**
   * Strategy setter
   *
   * @param IConfigurationStrategy $strategy
   *
   * @return void
   */
  public function setStrategy(IConfigurationStrategy $strategy) {
    $this->strategy = $strategy;
  }

  /**
   * Get the configuration strategy
   *
   * @return IConfigurationStrategy
   */
  public function getStrategy() {
    return $this->strategy;
  }

  /**
   * Get the stored configurations of a given module
   *
   * @param string        $module Module name
   * @param CMbObjectSpec $spec   Specification object
   * @param bool          $static Get "static" configurations
   *
   * @return array
   */
  public function getStoredConfigurations($module, CMbObjectSpec $spec, bool $static = false) {
    return $this->strategy->getStoredConfigurations($module, $spec, $static);
  }

  /**
   * Get the NULL stored configurations of a given module
   *
   * @param string        $module       Module name
   * @param CMbObjectSpec $spec         Specification object
   * @param string|null   $object_class The object class
   * @param int|null      $object_id    The object ID
   * @param bool          $static       Get the "static" configurations
   *
   * @return array
   */
  public function getNullStoredConfigurations($module, CMbObjectSpec $spec, $object_class = null, $object_id = null, $static = false) {
    return $this->strategy->getNullStoredConfigurations($module, $spec, $object_class, $object_id, $static);
  }

  /**
   * Change a particular configuration value
   *
   * @param string         $feature Feature
   * @param mixed          $value   Value
   * @param CMbObject|null $object  Host object
   * @param bool           $static  Store as a "static" configuration
   *
   * @return string|null
   * @throws Exception
   */
  public function setConfig($feature, $value, CMbObject $object = null, bool $static = false) {
    return $this->strategy->setConfig($feature, $value, $object, $static);
  }

  /**
   * Get the alternative parameterized configurations of a given module for a given context
   *
   * @param string        $module       Module name
   * @param CMbObjectSpec $spec         Specification object
   * @param null          $object_class The object class
   * @param null          $object_id    The object ID
   * @param bool          $static       Get the "static" configuration
   *
   * @return array
   */
  public function getAltFeatures($module, CMbObjectSpec $spec, $object_class = null, $object_id = null, $static = false) {
    return $this->strategy->getAltFeatures($module, $spec, $object_class, $object_id, $static);
  }
}