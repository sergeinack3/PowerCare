<?php
/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Plugin\Button;

use Exception;
use Ox\Core\Plugin\Button\ButtonPlugin;
use Ox\Core\Plugin\Button\ButtonPluginManager;
use Ox\Mediboard\Admin\CUser;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

/**
 * Class ButtonPluginManagerTest
 */
class ButtonPluginManagerTest extends OxUnitTestCase {
  /**
   * @throws Exception
   */
  public function test_manager_is_singleton() {
    $manager = ButtonPluginManager::get();
    $this->assertInstanceOf(ButtonPluginManager::class, $manager);

    $same_manager = ButtonPluginManager::get();
    $this->assertSame($manager, $same_manager);
  }

  /**
   * @depends test_manager_is_singleton
   *
   * @throws Exception
   */
  public function test_buttons_have_correct_location() {
    $manager = ButtonPluginManager::get();

    $none_buttons  = $manager->getButtonsForLocation('none');
    $dummy_buttons = $manager->getButtonsForLocation('dummy');

    $this->assertCount(3, $none_buttons);
    $this->assertCount(2, $dummy_buttons);

    [$first_none, $second_none] = $none_buttons;
    [$first_dummy, $second_dummy] = $dummy_buttons;

    $this->assertSame($first_none, $first_dummy);
  }

  /**
   * @depends test_buttons_have_correct_location
   * @throws Exception
   */
  public function test_button_onclick_method_is_serialized() {
    $manager = ButtonPluginManager::get();

    $none_buttons = $manager->getButtonsForLocation('none', 1, 'abc', ['toto', 'titi'], 'é');
    $first_none   = $none_buttons[0];

    $this->assertEquals(
      'try { myfunction(1, "abc", ["toto","titi"], "\u00e9"); } catch(e) { console.error(e); }',
      $first_none->getOnClick()
    );
  }

  /**
   * @depends test_button_onclick_method_is_serialized
   *
   * @throws Exception
   */
  public function test_button_has_correct_injected_properties() {
    $manager = ButtonPluginManager::get();

    $none_buttons = $manager->getButtonsForLocation('none');

    /** @var ButtonPlugin[] $none_buttons */
    [$first_none, $second_none, $complex_none] = $none_buttons;

    $this->assertEquals('none', $first_none->getLabel());
    $this->assertEquals('dummy', $first_none->getClassNames());
    $this->assertFalse($first_none->isDisabled());
    $this->assertEquals('try { myfunction(); } catch(e) { console.error(e); }', $first_none->getOnClick());
    $this->assertEmpty($first_none->getScriptName());
    $this->assertEquals('core', $first_none->getModuleName());

    $this->assertEquals('none', $second_none->getLabel());
    $this->assertEquals('none', $second_none->getClassNames());
    $this->assertTrue($second_none->isDisabled());
    $this->assertEmpty($second_none->getOnClick());
    $this->assertEmpty($second_none->getScriptName());
    $this->assertEquals('core', $second_none->getModuleName());

      $this->assertEquals('no_label', $complex_none->getLabel());
      $this->assertEquals('dummy', $complex_none->getClassNames());
      $this->assertFalse($complex_none->isDisabled());
      $this->assertEquals('try { testFunc(); } catch(e) { console.error(e); }', $complex_none->getOnClick());
      $this->assertEquals('noScript', $complex_none->getScriptName());
      $this->assertEquals('core', $complex_none->getModuleName());
      $this->assertEquals('initFunc', $complex_none->getInitAction());
      $this->assertEquals(10, $complex_none->getCounter());
  }

  /**
   * @throws TestsException
   */
  public function test_buttons_from_inactive_module_are_not_registered() {
    $manager = $this->getMockBuilder(ButtonPluginManager::class)
      ->onlyMethods(['isModuleActive'])
      ->disableOriginalConstructor()
      ->getMock();

    $manager->expects($this->any())->method('isModuleActive')->willReturn(false);

    $this->invokePrivateMethod($manager, 'registerAll');

    $no_buttons = $manager->getButtonsForLocation('none');
    $this->assertEmpty($no_buttons);
  }

  /**
   * @throws TestsException
   */
  public function test_buttons_registration_for_active_non_core_module() {
    $manager = $this->getMockBuilder(ButtonPluginManager::class)
      ->onlyMethods(['isModuleActive'])
      ->disableOriginalConstructor()
      ->getMock();

    $manager->expects($this->any())->method('isModuleActive')->willReturn(false);

    $module_name = $this->invokePrivateMethod($manager, 'getModuleForClass', CUser::class);
    $this->assertEquals('admin', $module_name);
  }

  /**
   * @throws TestsException
   */
  public function test_buttons_registration_for_unknown_module() {
    $manager = $this->getMockBuilder(ButtonPluginManager::class)
      ->onlyMethods(['getRegisteredButtons'])
      ->disableOriginalConstructor()
      ->getMock();

    $manager->expects($this->once())->method('getRegisteredButtons')->willReturn(['module_that_is_unlikely_to_exists_one_day']);

    $this->expectExceptionMessage(
      'CModule-error-Unable to find module for: module_that_is_unlikely_to_exists_one_day'
    );

    $this->invokePrivateMethod($manager, 'registerAll');
  }
}
