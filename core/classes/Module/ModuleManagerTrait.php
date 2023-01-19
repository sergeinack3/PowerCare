<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CMbException;
use Ox\Core\Module\CModule;
use ReflectionClass;

/**
 * Description
 *
 * Todo: Create a Module Manager
 */
trait ModuleManagerTrait {
  /**
   * @param string $classname FQN classname
   *
   * @return string
   * @throws CMbException
   */
  protected function getModuleForClass(string $classname): string {
    try {
      $classmap = CClassMap::getInstance()->getClassMap($classname);

      if (isset($classmap->module) && $classmap->module) {
        return $classmap->module;
      }

      // Detecting core "module"
      $reflection = new ReflectionClass($classname);

      $matches = [];
      if (preg_match("@core(/|\\\)classes(/|\\\)(.+)\.php$@", $reflection->getFileName(), $matches)) {
        return 'core';
      }
    }
    catch (Exception $e) {
      throw new CMbException('CModule-error-Unable to find module for: %s', $classname);
    }

    throw new CMbException('CModule-error-Unable to find module for: %s', $classname);
  }

  /**
   * Tell whether the module of a given class is active
   *
   * @param string $module_name
   *
   * @return bool
   */
  protected function isModuleActive(string $module_name): bool {
    if ($module_name === 'core') {
      return true;
    }

    return (CModule::getActive($module_name) instanceof CModule);
  }

  /**
   * Tell whether a module is active according to given classname
   *
   * @param string $classname
   *
   * @return bool
   */
  protected function isModuleActiveForClass(string $classname): bool {
    try {
      $module_name = $this->getModuleForClass($classname);
    }
    catch (CMbException $e) {
      return false;
    }

    return $this->isModuleActive($module_name);
  }
}
