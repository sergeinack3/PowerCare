<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Plugin\Button;

/**
 * Dummy button for testing purposes
 * Not in Tests namespace because of class map blacklisting it
 */
class ButtonDummy extends AbstractButtonPlugin {
  /**
   * @inheritDoc
   */
  public static function registerButtons(ButtonPluginManager $manager): void {
    $manager->register(
      'none', 'none', true, ['none'], 1, '', ''
    );

    $manager->register(
      'dummy', 'dummy', false, ['dummy'], 1, '', ''
    );

    $manager->register(
      'none', 'dummy', false, ['none', 'dummy'], 2, 'myfunction', ''
    );

    $manager->registerComplex(
        'no_label', 'dummy', false, ['none'], 0, 'testFunc', 'noScript', 'initFunc', 10
    );
  }
}
