<?php
/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Autoload;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CClassMap;

/**
 * Class CAutoloadAlias
 */
class CAutoloadAlias {
  /** @var array */
  public static $autoload_class = [];

  /** @var bool */
  public static $debug = false;

  /**
   * @param string $short_name
   *
   * @return void
   * @throws Exception
   */
  public static function loadClassByShortName($short_name): void {
    $traces = [];
    if (static::$debug) {
      // Call debug_backtrace before any $args manipulation
      $traces = debug_backtrace();
    }

    $classmap = CClassMap::getInstance();

    if ($class_name = $classmap->getAliasByShortName($short_name)) {
      // map
      /** @var object $map */
      $map = $classmap->getClassMap($class_name);

      // require
      if (!class_exists($class_name, false) && file_exists($map->file)) {
        require_once $map->file;
      }

      // alias
      if ($class_name !== $short_name) {
        class_alias($class_name, $short_name, false);
      }

      // debug
      if (static::$debug) {
        static::$autoload_class[$short_name] = [
          'class_name' => $class_name,
          'trace'      => $traces[2] ?? '',
        ];
      }

      return;
    }
  }

  /**
   * Add a log
   *
   * @param bool $debug
   *
   * @return void
   */
  public static function register($debug = false): void {
    spl_autoload_register([CAutoloadAlias::class, 'loadClassByShortName']);

    if ($debug) {
      static::$debug = true;

      CApp::registerShutdown(
        function () {
          dump(['CAutoloadAlias::loadClassByShortName' => static::$autoload_class]);
        },
        CApp::AUTOLOAD_PRIORITY
      );
    }
  }

  /**
   * Remove spl_autoload
   *
   * @return void
   */
  public static function unregister(): void {
    $functions = spl_autoload_functions();
    foreach ($functions as $function) {
      if (is_array($function) && $function[0] === CAutoloadAlias::class) {
        spl_autoload_unregister($function);
      }
    }
  }
}

