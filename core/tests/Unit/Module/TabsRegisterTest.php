<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
namespace Ox\Core\Tests\Unit\Module;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CClassMap;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CTabsAdmin;
use Ox\Mediboard\System\CTabsSystem;
use Ox\Tests\OxUnitTestCase;


class TabsRegisterTest extends OxUnitTestCase
{

    private function getModule()
    {
        $module        = CModule::getInstalled('system');
        $module->_tabs = [];

        return $module;
    }

    public function testRegisterFileSuccess()
    {
        $module   = $this->getModule();
        $register = new CTabsSystem($module);
        $this->invokePrivateMethod($register, 'registerFile', "about", TAB_READ, $module::TAB_STANDARD);
        $this->assertArrayHasKey($module::TAB_STANDARD, $module->_tabs);
        $this->assertArrayHasKey("about", $module->_tabs[$module::TAB_STANDARD]);
    }

    public function testRegisterFileFailedPerm()
    {
        $module   = $this->getModule();
        $register = new CTabsSystem($module);
        $this->invokePrivateMethod($register, 'registerFile', "lorem", TAB_ADMIN, $register::TAB_CONFIGURE);
        $this->assertArrayNotHasKey($register::TAB_CONFIGURE, $module->_tabs);
    }

    public function testRegisterFileFailedTab()
    {
        $module   = $this->getModule();
        $register = new CTabsSystem($module);
        $this->invokePrivateMethod($register, 'registerFile', "lorem", TAB_READ, 'group_ipsum');
        $this->assertArrayNotHasKey('group_ipsum', $module->_tabs);
    }

    public function testGenerateFileUrl()
    {
        $module   = $this->getModule();
        $register = new CTabsSystem($module);
        $url      = $this->invokePrivateMethod($register, 'generateFileUrl', "about");
        $this->assertEquals($url, '?m=system&tab=about');
    }

//    public function testRegisterRouteSuccess()
//    {
//        $module   = $this->getModule();
//        $register = new CTabsSystem($module);
//        $this->invokePrivateMethod($register, 'registerRoute', "system_about", TAB_READ, $register::TAB_STANDARD);
//        $this->assertArrayHasKey($module::TAB_STANDARD, $module->_tabs);
//        $this->assertArrayHasKey("system_about", $module->_tabs[$module::TAB_STANDARD]);
//    }
//
//    public function testRegisterRouteFailedGroup()
//    {
//        $module   = $this->getModule();
//        $register = new CTabsSystem($module);
//        $this->invokePrivateMethod($register, 'registerRoute', "system_about", TAB_READ, 'group_ipsum');
//        $this->assertArrayNotHasKey('group_ipsum', $module->_tabs);
//    }

    public function testRegisterRouteFailedBad()
    {
        $module   = $this->getModule();
        $register = new CTabsSystem($module);
        $this->invokePrivateMethod($register, 'registerRoute', "toto_tata_titi", TAB_READ);
        $this->assertEmpty($module->_tabs);
    }

    public function testRegisterRouteFailedApi()
    {
        $module   = $this->getModule();
        $register = new CTabsSystem($module);
        $this->invokePrivateMethod($register, 'registerRoute', "system_modules", TAB_READ);
        $this->assertEmpty($module->_tabs);
    }

    public function testGenerateRouteUrl()
    {
        $module   = $this->getModule();
        $register = new CTabsAdmin($module);
        $url      = $this->invokePrivateMethod($register, 'generateRouteUrl', "admin_identicate");
        $this->assertEquals($url, '/api/identicate');
    }

    /**
     * @dataProvider getTabsRegisters
     */
    public function testRegisterAll(AbstractTabsRegister $register, CModule $module)
    {
        global $can;
        $can        = new CCanDo();
        $can->read  = true;
        $can->view  = true;
        $can->edit  = true;
        $can->admin = true;

        $user_type = CAppUI::$instance->user_type = 1;
        $register->registerAll();
        $this->assertNotEmpty($module->_tabs);
        CAppUI::$instance->user_type = $user_type;
        unset($can);
    }

    public function getTabsRegisters()
    {
        $modules = CModule::getInstalled();
        $retour  = [];
        foreach ($modules as $module) {
            $registers = CClassMap::getInstance()->getClassChildren(
                AbstractTabsRegister::class,
                false,
                true,
                $module->mod_name
            );
            foreach ($registers as $register_name) {
                $instance               = new $register_name($module);
                $retour[$register_name] = [$instance, $module];
            }
        }

        return $retour;
    }
}
