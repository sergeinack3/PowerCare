<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\EntryPoint;
use Ox\Core\Module\CModule;
use Ox\Core\Plugin\Button\AbstractAppBarButtonPlugin;
use Ox\Core\Plugin\Button\ButtonPluginManager;
use Ox\Core\Plugin\Button\ComplexButtonPlugin;
use Ox\Core\Version\Version;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tamm\Cabinet\Menu\AppBarCabinetMenu;
use Symfony\Component\Routing\Router;

/**
 * AppBar data builder
 */
class AppBar extends EntryPoint
{
    public const APPBAR_ID = 'Appbar';

    public const DATAS = 'datas';
    public const LINKS = 'links';

    public const MODULE_LINK_URL      = 'module_url';
    public const MODULE_LINK_TABS_URL = 'tabs';

    public const GROUPS_LINK_LIST  = 'groups';
    public const GROUPS_WITH_ROLES = 'with_roles';

    public const USER_LINK_EDIT_INFOS   = 'edit_infos';
    public const USER_LINK_LOGOUT       = 'logout';
    public const USER_LINK_DEFAULT_PAGE = 'default';

    public const TAB_LINK_URL = 'tab_url';

    public const INFO_TITLE                    = 'title';
    public const INFO_RELEASE_TITLE            = 'release_title';
    public const INFO_RELEASE_TITLE_CAMEL_CASE = 'releaseTitle';
    public const INFO_RELEASE_CODE             = 'releaseCode';
    public const INFO_RELATIVE                 = 'relative';
    public const INFO_LOCALE                   = 'locale';

    public const SHORTCUT_COUNT = 4;

    private CModule    $module;
    private CGroups    $group;
    private CMediusers $mediuser;
    private Version    $version;

    private string $module_tabs_links;
    private array  $scripts_js = [];

    public function __construct(
        Router $router,
        CModule $module,
        CGroups $group,
        CMediusers $mediuser,
        Version $version,
        ?string $tab = null
    ) {
        parent::__construct(static::APPBAR_ID, $router);

        $this->module   = $module;
        $this->group    = $group;
        $this->mediuser = $mediuser;
        $this->version  = $version;

        $this->module_tabs_links = $this->router->generate(
            'system_modules_tabs_list',
            ['mod_name' => 'mod_name_to_replace']
        );

        $this->data['tab-active'] = $tab;
    }

    public function build(): void
    {
        $this->buildCurrentModule();
        $this->buildCurrentGroup();
        $this->buildCurrentModuleTabs();
        $this->buildModulesList();
        $this->buildPlaceHolders();
        $this->buildCabinetMenu();
        $this->buildInfosMaj();
        $this->buildShortcutList();

        if (CModule::getActive('mediusers')) {
            $this->buildCurrentUser();
            $this->buildAvailableFunctions();
        }

        $this->data['date-now']  = CMbDT::format(null, '%a %d %b %Y');
        $this->data['is-tamm']   = CAppUI::isGroup();
        $this->data['is-qualif'] = CAppUI::conf('instance_role') === 'qualif';
    }

    private function buildCurrentModule(): void
    {
        $this->module->buildUrl();

        $this->data['current-module'] = [
            self::DATAS => [
                'mod_name'     => $this->module->mod_name,
                'mod_category' => $this->module->mod_category,
            ],
            self::LINKS => [
                self::MODULE_LINK_URL      => $this->module->_url,
                self::MODULE_LINK_TABS_URL => $this->getModuleTabsLink($this->module),
            ],
        ];
    }

    private function buildCurrentGroup(): void
    {
        $this->data['group-data'] = [
            self::DATAS => [
                '_id'  => $this->group->_id,
                'text' => mb_convert_encoding($this->group->text, 'UTF-8', 'ISO-8859-1'),
            ],
            self::LINKS => [
                self::GROUPS_LINK_LIST => $this->router->generate(
                    'etablissement_groups_list',
                    [self::GROUPS_WITH_ROLES => true]
                ),
            ],
        ];
    }

    private function buildCurrentUser(): void
    {
        if (!CModule::getActive('mediusers')) {
            return;
        }

        $this->data['user'] = [
            self::DATAS => [
                '_user_username'       => mb_convert_encoding($this->mediuser->_user_username, 'UTF-8', 'ISO-8859-1'),
                '_user_first_name'     => mb_convert_encoding($this->mediuser->_user_first_name, 'UTF-8', 'ISO-8859-1'),
                '_user_last_name'      => mb_convert_encoding($this->mediuser->_user_last_name, 'UTF-8', 'ISO-8859-1'),
                '_color'               => $this->mediuser->_color,
                '_initial'             => mb_convert_encoding($this->mediuser->_initial, 'UTF-8', 'ISO-8859-1'),
                '_font_color'          => $this->mediuser->_font_color,
                '_can_change_password' => $this->mediuser->_allow_change_password,
                '_is_patient'          => CUser::isPatientUser($this->mediuser->_user_type),
                '_dark_mode'           => CAppUI::pref('mediboard_ext_dark') === '1',
                '_is_admin'            => $this->mediuser->isAdmin(),
            ],
            self::LINKS => [
                self::USER_LINK_EDIT_INFOS   => '?m=mediusers&a=edit_infos',
                self::USER_LINK_LOGOUT       => '?logout=-1',
                self::USER_LINK_DEFAULT_PAGE => $this->mediuser->getDefaultPageLink(),
            ],
        ];
    }

    private function buildAvailableFunctions(): void
    {
        $main_function       = $this->mediuser->loadRefFunction();
        $secondary_functions = $this->mediuser->loadRefsSecondaryFunctions();

        $this->data['functions-data'][] = [
            self::DATAS => [
                '_id'      => $main_function->_id,
                'group_id' => $main_function->group_id,
                'is_main'  => true,
                'text'     => mb_convert_encoding($main_function->text, 'UTF-8', 'ISO-8859-1'),
            ],
        ];

        foreach ($secondary_functions as $function) {
            $this->data['functions-data'][] = [
                self::DATAS => [
                    '_id'      => $function->_id,
                    'group_id' => $function->group_id,
                    'is_main'  => false,
                    'text'     => mb_convert_encoding($function->text, 'UTF-8', 'ISO-8859-1'),
                ],
            ];
        }
    }

    private function buildCurrentModuleTabs(): void
    {
        $tabs = $this->getTabs();

        $this->data['module-tabs'] = [];

        /** @var CTab $tab */
        foreach ($tabs as $tab) {
            $this->data['module-tabs'][] = [
                self::DATAS => $tab->getDatas(),
                self::LINKS => [
                    self::TAB_LINK_URL => $tab->getUrl(),
                ],
            ];
        }
    }

    protected function getTabs(): array
    {
        return $this->module->getTabs();
    }

    private function buildModulesList(): void
    {
        $mod_list = $this->getActiveModules();
        foreach ($mod_list as $mod) {
            $mod->updateFormFields();
        }

        CMbArray::pluckSort(
            $mod_list,
            SORT_FLAG_CASE | SORT_NATURAL,
            '_view',
            CMbArray::PLUCK_SORT_REMOVE_DIACRITICS
        );


        $this->data['default-modules'] = [];

        /** @var CModule $module */
        foreach ($mod_list as $module) {
            if (!$module->mod_ui_active || !$module->getPerm(PERM_READ) || !$module->canView()) {
                continue;
            }

            $module->buildUrl();

            $this->data['default-modules'][] = [
                self::DATAS => [
                    'mod_name'     => $module->mod_name,
                    'mod_category' => $module->mod_category,
                ],
                self::LINKS => [
                    self::MODULE_LINK_URL      => $module->_url,
                    self::MODULE_LINK_TABS_URL => $this->getModuleTabsLink($module),
                ],
            ];
        }
    }

    protected function getActiveModules(): array
    {
        return CModule::getActive();
    }

    private function buildShortcutList(): void
    {
        $tabs = $this->getMostCalledTabs();

        foreach ($tabs as $tab) {
            $this->data['tab-shortcuts-data'][] = [
                self::DATAS => $tab->getDatas(),
                self::LINKS => [
                    self::TAB_LINK_URL => $tab->getUrl(),
                ],
            ];
        }
    }

    protected function getMostCalledTabs(): array
    {
        return (new CTabHit())->getMostCalledTabs($this->mediuser, self::SHORTCUT_COUNT);
    }

    private function buildPlaceHolders(): void
    {
        $buttons = $this->getPluginButtons();

        foreach ($buttons as $button) {
            if ($button->getScriptName()) {
                $this->addJsFile($button->getModuleName(), $button->getScriptName());
            }

            $action = $button->getAction();
            $args   = null;
            if ($json_action = json_decode($action, true)) {
                $action = $json_action['callable'];
                $args   = $json_action['arguments'];
            }

            $data = [
                'label'       => mb_convert_encoding($button->getLabel(), 'UTF-8', 'ISO-8859-1'),
                'icon'        => $button->getClassNames(),
                'disabled'    => $button->isDisabled(),
                'action'      => $action,
                'action_args' => $args,
                'init_action' => ($button instanceof ComplexButtonPlugin) ? $button->getInitAction() : null,
                'counter'     => ($button instanceof ComplexButtonPlugin) ? $button->getCounter() : null,
            ];

            $data['_id'] = md5(serialize($data));

            $this->data['placeholders-data'][] = [
                self::DATAS => $data,
            ];
        }
    }

    protected function getPluginButtons(): array
    {
        $manager = ButtonPluginManager::get();

        return $manager->getButtonsForLocation(AbstractAppBarButtonPlugin::LOCATION_APPBAR_SHORTCUT);
    }

    private function buildCabinetMenu(): void
    {
        if (CModule::getActive('oxCabinet')) {
            $this->data['tamm-menu'] = (new AppBarCabinetMenu())->build($this->mediuser, $this->module->mod_name);
        }
    }

    private function buildInfosMaj(): void
    {
        $from_infos = $this->version->toArray();

        $version = [
            self::INFO_TITLE         => null,
            self::INFO_RELEASE_TITLE => null,
        ];

        if (isset($from_infos[self::INFO_TITLE])) {
            $version[self::INFO_TITLE] = mb_convert_encoding($from_infos[self::INFO_TITLE], 'UTF-8', 'ISO-8859-1');
        }

        if (isset($from_infos[self::INFO_RELEASE_CODE])) {
            if (isset($from_infos[self::INFO_RELEASE_TITLE_CAMEL_CASE])) {
                $version[self::INFO_RELEASE_TITLE] = mb_convert_encoding(
                    $from_infos[self::INFO_RELEASE_TITLE_CAMEL_CASE],
                    'UTF-8',
                    'ISO-8859-1'
                );
            }
        } elseif (isset($from_infos[self::INFO_RELATIVE][self::INFO_LOCALE])) {
            $version[self::INFO_RELEASE_TITLE] = mb_convert_encoding(
                CAppUI::tr('Latest update') . ' ' . $from_infos[self::INFO_RELATIVE][self::INFO_LOCALE],
                'UTF-8',
                'ISO-8859-1'
            );
        }

        $this->data['info-maj'] = $version;
    }

    public function getScriptsJs(): array
    {
        return $this->scripts_js;
    }

    private function getModuleTabsLink(CModule $module): string
    {
        return str_replace('mod_name_to_replace', $module->mod_name, $this->module_tabs_links);
    }

    private function addJsFile(string $mod_name, string $script_name): void
    {
        $file = 'modules/' . $mod_name . '/javascript/' . $script_name . '.js';
        if (file_exists($file) && CModule::getActive($mod_name)) {
            $this->scripts_js[] = $file;
        }
    }
}
