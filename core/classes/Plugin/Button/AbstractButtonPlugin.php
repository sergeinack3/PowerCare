<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Plugin\Button;

/**
 * Abstraction class for plugin buttons
 */
abstract class AbstractButtonPlugin {
  /**
   * @param ButtonPluginManager $manager
   *
   * @return void
   */
  abstract public static function registerButtons(ButtonPluginManager $manager): void;
}
