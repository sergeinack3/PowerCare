<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit;

use Ox\Core\CApp;
use Ox\Core\Kernel\Routing\RouterBridge;
use Ox\Core\Module\CModule;
use Ox\Core\Plugin\Button\ButtonDummy;
use Ox\Core\Plugin\Button\ButtonPlugin;
use Ox\Core\Plugin\Button\ComplexButtonPlugin;
use Ox\Core\Version\Version;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSecondaryFunction;
use Ox\Mediboard\System\AppBar;
use Ox\Mediboard\System\CTab;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\Routing\Router;

class AppBarTest extends OxUnitTestCase
{
    private Router     $router;
    private CModule    $module;
    private CGroups    $group;
    private CMediusers $mediuser;
    private Version    $version;

    public function setUp(): void
    {
        parent::setUp();

        $this->router   = RouterBridge::getInstance();
        $this->module   = CModule::getActive('system');
        $this->group    = CGroups::loadCurrent();
        $this->mediuser = CMediusers::get();
        $this->version  = CApp::getVersion();
    }

    public function testBuildCurrentModule(): void
    {
        $appbar = new AppBar($this->router, $this->module, $this->group, $this->mediuser, $this->version);
        $this->invokePrivateMethod($appbar, 'buildCurrentModule');

        $data = $this->getPrivateProperty($appbar, 'data');

        $this->assertEquals(
            [
                AppBar::DATAS => [
                    'mod_name'     => 'system',
                    'mod_category' => 'systeme',
                ],
                AppBar::LINKS => [
                    AppBar::MODULE_LINK_URL      => $this->module->_url,
                    AppBar::MODULE_LINK_TABS_URL => "/api/modules/{$this->module->mod_name}/tabs",
                ],
            ],
            $data['current-module']
        );
    }

    public function testBuildCurrentGroup(): void
    {
        $group       = new CGroups();
        $group->_id  = 1;
        $group->text = 'Test Group';

        $appbar = new AppBar($this->router, $this->module, $group, $this->mediuser, $this->version);
        $this->invokePrivateMethod($appbar, 'buildCurrentGroup');

        $data = $this->getPrivateProperty($appbar, 'data');

        $this->assertEquals(
            [
                AppBar::DATAS => [
                    '_id'  => 1,
                    'text' => 'Test Group',
                ],
                AppBar::LINKS => [
                    AppBar::GROUPS_LINK_LIST => '/api/groups?with_roles=1',
                ],
            ],
            $data['group-data']
        );
    }

    public function testBuildCurrentUserModuleDisabled(): void
    {
        $mod_mediusers = CModule::$active['mediusers'];
        CModule::$active['mediusers'] = null;

        $appbar = new AppBar($this->router, $this->module, $this->group, $this->mediuser, $this->version);
        $this->invokePrivateMethod($appbar, 'buildCurrentUser');

        $data = $this->getPrivateProperty($appbar, 'data');

        $this->assertArrayNotHasKey('user', $data);

        CModule::$active['mediusers'] = $mod_mediusers;
    }

    public function testBuildCurrentUser(): void
    {
        $mediuser                         = new CMediusers();
        $mediuser->_user_username         = 'User username';
        $mediuser->_user_first_name       = 'User first name';
        $mediuser->_user_last_name        = 'User last name';
        $mediuser->_color                 = '255255255';
        $mediuser->_initial               = 'UU';
        $mediuser->_font_color            = '000';
        $mediuser->_allow_change_password = true;
        $mediuser->_user_type             = 14;

        $appbar = new AppBar($this->router, $this->module, $this->group, $mediuser, $this->version);
        $this->invokePrivateMethod($appbar, 'buildCurrentUser');

        $data = $this->getPrivateProperty($appbar, 'data');
        $user_data = $data['user'];

        $this->assertEquals('User username', $user_data[AppBar::DATAS]['_user_username']);
        $this->assertEquals('User first name', $user_data[AppBar::DATAS]['_user_first_name']);
        $this->assertEquals('User last name', $user_data[AppBar::DATAS]['_user_last_name']);
        $this->assertEquals('255255255', $user_data[AppBar::DATAS]['_color']);
        $this->assertEquals('UU', $user_data[AppBar::DATAS]['_initial']);
        $this->assertEquals('000', $user_data[AppBar::DATAS]['_font_color']);
        $this->assertTrue($user_data[AppBar::DATAS]['_can_change_password']);
        $this->assertFalse($user_data[AppBar::DATAS]['_is_patient']);
        $this->assertFalse($user_data[AppBar::DATAS]['_is_admin']);

        $this->assertEquals('?m=mediusers&a=edit_infos', $user_data[AppBar::LINKS][AppBar::USER_LINK_EDIT_INFOS]);
        $this->assertEquals('?logout=-1', $user_data[AppBar::LINKS][AppBar::USER_LINK_LOGOUT]);
        $this->assertStringStartsWith('?m=', $user_data[AppBar::LINKS][AppBar::USER_LINK_DEFAULT_PAGE]);
    }

    public function testBuildAvailableFunctions(): void
    {
        $mediuser              = new CMediusers();
        $mediuser->_id         = 1;
        $mediuser->function_id = 1;

        $main_function           = new CFunctions();
        $main_function->_id      = 1;
        $main_function->group_id = 1;
        $main_function->text     = 'Main function';

        $secondary_function_1           = new CFunctions();
        $secondary_function_1->_id      = 2;
        $secondary_function_1->group_id = 2;
        $secondary_function_1->text     = 'Secondary function 1';

        $secondary_function_join_1                      = new CSecondaryFunction();
        $secondary_function_join_1->function_id         = 2;
        $secondary_function_join_1->_fwd['function_id'] = $secondary_function_1;
        $secondary_function_join_1->_fwd['user_id']     = $mediuser;

        $secondary_function_2           = new CFunctions();
        $secondary_function_2->_id      = 3;
        $secondary_function_2->group_id = 1;
        $secondary_function_2->text     = 'Secondary function 2';

        $secondary_function_join_2                      = new CSecondaryFunction();
        $secondary_function_join_2->function_id         = 3;
        $secondary_function_join_2->_fwd['function_id'] = $secondary_function_2;
        $secondary_function_join_2->_fwd['user_id']     = $mediuser;

        $mediuser->_fwd['function_id']           = $main_function;
        $mediuser->_back['secondary_functions']  = [
            $secondary_function_join_1,
            $secondary_function_join_2,
        ];
        $mediuser->_count['secondary_functions'] = 2;

        $appbar = new AppBar($this->router, $this->module, $this->group, $mediuser, $this->version);
        $this->invokePrivateMethod($appbar, 'buildAvailableFunctions');

        $data = $this->getPrivateProperty($appbar, 'data');

        $this->assertEquals(
            [
                [
                    AppBar::DATAS => [
                        '_id'      => 1,
                        'group_id' => 1,
                        'is_main'  => true,
                        'text'     => 'Main function',
                    ],
                ],
                [
                    AppBar::DATAS => [
                        '_id'      => 2,
                        'group_id' => 2,
                        'is_main'  => false,
                        'text'     => 'Secondary function 1',
                    ],
                ],
                [
                    AppBar::DATAS => [
                        '_id'      => 3,
                        'group_id' => 1,
                        'is_main'  => false,
                        'text'     => 'Secondary function 2',
                    ],
                ],
            ],
            $data['functions-data']
        );
    }

    public function testBuildCurrentModuleTabs(): void
    {
        $appbar = $this->getMockBuilder(AppBar::class)
            ->onlyMethods(['getTabs'])
            ->setConstructorArgs([$this->router, $this->module, $this->group, $this->mediuser, $this->version])
            ->getMock();

        $appbar->method('getTabs')->willReturn(
            [
                new CTab('system', 'tab1', true, false, false, 10, '?m=system&tab=tab1'),
                new CTab('system', 'tab2', true, true, false, 9, '?m=system&tab=tab2'),
                new CTab('admin', 'tab1', true, false, false, 10, '?m=admin&tab=tab1'),
                new CTab('admin', 'tab20', false, false, true, null, '?m=admin&tab=tab20'),
            ]
        );

        $this->invokePrivateMethod($appbar, 'buildCurrentModuleTabs');

        $data = $this->getPrivateProperty($appbar, 'data');

        $this->assertEquals(
            [
                [
                    AppBar::DATAS => [
                        'is_config'    => false,
                        'is_param'     => false,
                        'is_standard'  => true,
                        'mod_name'     => 'system',
                        'pinned_order' => 10,
                        'tab_name'     => 'tab1',
                    ],
                    AppBar::LINKS => [
                        AppBar::TAB_LINK_URL => '?m=system&tab=tab1',
                    ],
                ],
                [
                    AppBar::DATAS => [
                        'is_config'    => false,
                        'is_param'     => true,
                        'is_standard'  => true,
                        'mod_name'     => 'system',
                        'pinned_order' => 9,
                        'tab_name'     => 'tab2',
                    ],
                    AppBar::LINKS => [
                        AppBar::TAB_LINK_URL => '?m=system&tab=tab2',
                    ],
                ],
                [
                    AppBar::DATAS => [
                        'is_config'    => false,
                        'is_param'     => false,
                        'is_standard'  => true,
                        'mod_name'     => 'admin',
                        'pinned_order' => 10,
                        'tab_name'     => 'tab1',
                    ],
                    AppBar::LINKS => [
                        AppBar::TAB_LINK_URL => '?m=admin&tab=tab1',
                    ],
                ],
                [
                    AppBar::DATAS => [
                        'is_config'    => true,
                        'is_param'     => false,
                        'is_standard'  => false,
                        'mod_name'     => 'admin',
                        'pinned_order' => null,
                        'tab_name'     => 'tab20',
                    ],
                    AppBar::LINKS => [
                        AppBar::TAB_LINK_URL => '?m=admin&tab=tab20',
                    ],
                ],
            ],
            $data['module-tabs']
        );
    }

    public function testBuildModulesList(): void
    {
        $appbar = $this->getMockBuilder(AppBar::class)
            ->onlyMethods(['getActiveModules'])
            ->setConstructorArgs([$this->router, $this->module, $this->group, $this->mediuser, $this->version])
            ->getMock();

        $appbar->method('getActiveModules')->willReturn(
            [
                CModule::getActive('system'),
                CModule::getActive('admin'),
                CModule::getActive('dPdeveloppement'),
            ]
        );

        $this->invokePrivateMethod($appbar, 'buildModulesList');

        $data = $this->getPrivateProperty($appbar, 'data');

        $this->assertEquals(
            [
                [
                    AppBar::DATAS => [
                        'mod_name'     => 'admin',
                        'mod_category' => 'parametrage',
                    ],
                    AppBar::LINKS => [
                        AppBar::MODULE_LINK_URL      => '?m=admin',
                        AppBar::MODULE_LINK_TABS_URL => '/api/modules/admin/tabs',
                    ],
                ],
                [
                    AppBar::DATAS => [
                        'mod_name'     => 'dPdeveloppement',
                        'mod_category' => 'systeme',
                    ],
                    AppBar::LINKS => [
                        AppBar::MODULE_LINK_URL      => '?m=dPdeveloppement',
                        AppBar::MODULE_LINK_TABS_URL => '/api/modules/dPdeveloppement/tabs',
                    ],
                ],
                [
                    AppBar::DATAS => [
                        'mod_name'     => 'system',
                        'mod_category' => 'systeme',
                    ],
                    AppBar::LINKS => [
                        AppBar::MODULE_LINK_URL      => '?m=system',
                        AppBar::MODULE_LINK_TABS_URL => '/api/modules/system/tabs',
                    ],
                ],
            ],
            $data['default-modules']
        );
    }

    public function testBuildShortcutList(): void
    {
        $appbar = $this->getMockBuilder(AppBar::class)
            ->onlyMethods(['getMostCalledTabs'])
            ->setConstructorArgs([$this->router, $this->module, $this->group, $this->mediuser, $this->version])
            ->getMock();

        $appbar->method('getMostCalledTabs')->willReturn(
            [
                new CTab('system', 'tab1', true, false, false, 10, '?m=system&tab=tab1'),
                new CTab('system', 'tab2', true, true, false, 9, '?m=system&tab=tab2'),
            ]
        );

        $this->invokePrivateMethod($appbar, 'buildShortcutList');

        $data = $this->getPrivateProperty($appbar, 'data');

        $this->assertEquals(
            [
                [
                    AppBar::DATAS => [
                        'is_config'    => false,
                        'is_param'     => false,
                        'is_standard'  => true,
                        'mod_name'     => 'system',
                        'pinned_order' => 10,
                        'tab_name'     => 'tab1',
                    ],
                    AppBar::LINKS => [
                        AppBar::TAB_LINK_URL => '?m=system&tab=tab1',
                    ],
                ],
                [
                    AppBar::DATAS => [
                        'is_config'    => false,
                        'is_param'     => true,
                        'is_standard'  => true,
                        'mod_name'     => 'system',
                        'pinned_order' => 9,
                        'tab_name'     => 'tab2',
                    ],
                    AppBar::LINKS => [
                        AppBar::TAB_LINK_URL => '?m=system&tab=tab2',
                    ],
                ],
            ],
            $data['tab-shortcuts-data']
        );
    }

    public function testBuildPlaceHolders(): void
    {
        $appbar = $this->getMockBuilder(AppBar::class)
            ->onlyMethods(['getPluginButtons'])
            ->setConstructorArgs([$this->router, $this->module, $this->group, $this->mediuser, $this->version])
            ->getMock();

        $appbar->method('getPluginButtons')->willReturn(
            [
                new ButtonPlugin(
                    'button1',
                    'class_name',
                    true,
                    'system',
                    '{"callable": "test", "arguments": null}',
                    'script'
                ),
                new ComplexButtonPlugin(
                    'button2',
                    'class_name2',
                    false,
                    'admin',
                    '{"callable": "test2", "arguments": "arg !"}',
                    'script',
                    'action',
                    10
                ),
            ]
        );

        $this->invokePrivateMethod($appbar, 'buildPlaceHolders');

        $data = $this->getPrivateProperty($appbar, 'data');

        $this->assertEquals(
            [
                [
                    AppBar::DATAS => [
                        '_id'         => '9655e0b269af14957b2bfc8f0271f4b5',
                        'label'       => 'button1',
                        'icon'        => 'class_name',
                        'disabled'    => true,
                        'action'      => 'test',
                        'action_args' => null,
                        'init_action' => null,
                        'counter'     => null,
                    ],
                ],
                [
                    AppBar::DATAS => [
                        '_id'         => 'b23015c3185889de3e1b50b8eabb7449',
                        'label'       => 'button2',
                        'icon'        => 'class_name2',
                        'disabled'    => false,
                        'action'      => 'test2',
                        'action_args' => 'arg !',
                        'init_action' => 'action',
                        'counter'     => '10',
                    ],
                ],
            ],
            $data['placeholders-data']
        );
    }

    public function testBuildInfosMaj(): void
    {
        $version = new Version(
            [
                'title'        => 'Version title',
                'code'         => '1234abcd',
                'releaseTitle' => 'The release title is this',
            ]
        );

        $appbar = new AppBar($this->router, $this->module, $this->group, $this->mediuser, $version);

        $this->invokePrivateMethod($appbar, 'buildInfosMaj');

        $data = $this->getPrivateProperty($appbar, 'data');

        $this->assertEquals(
            [
                'title'        => 'Version title',
                'release_title' => 'The release title is this',
            ],
            $data['info-maj']
        );
    }
}
