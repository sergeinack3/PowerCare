<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module;

use Ox\Core\CClassMap;
use Ox\Core\CMbException;
use ReflectionClass;

/**
 * Description
 */
trait ModuleAwareTrait {
  /**
   * @return string
   * @throws CMbException
   */
  public function getModuleName(): string {
    $classmap = CClassMap::getInstance()->getClassMap(static::class);

    if (!isset($classmap->module) || !$classmap->module) {
      // Detecting core "module"
      $reflection = new ReflectionClass(static::class);

      $matches = [];
      if (preg_match("@core(/|\\\)classes(/|\\\)(.+)\.php$@", $reflection->getFileName(), $matches)) {
        return 'core';
      }
    }

    if ($classmap->module === null) {
      throw new CMbException('CModule-error-Unable to find module for: %s', static::class);
    }

    return $classmap->module;
  }
}
