/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { shallowMount, Wrapper } from "@vue/test-utils"
import Appbar from "@/components/Appbar/Appbar"
import Vuetify from "vuetify"
import Vue from "vue"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import ModuleDetailMobile from "@/components/Appbar/ModuleDetail/ModuleDetailMobile/ModuleDetailMobile"

/* eslint-disable dot-notation */

/**
 * Test pour la classe Appbar
 */
export default class AppbarTest extends OxTest {
    protected component = Appbar

    private currentModuleData = {
        datas: {
            mod_name: "ModuleTest",
            mod_active: true,
            mod_ui_active: true,
            mod_category: "category_test",
            mod_package: "test",
            mod_custom_color: null
        },
        links: {
            self: "/mediboard/api/modules/ModuleTest",
            schema: "/mediboard/api/schemas/module",
            history: "/mediboard/api/history/module/42",
            module_url: "?m=ModuleTest",
            tabs: "/mediboard/api/modules/ModuleTest/tabs"
        }
    }

    private functionsData = [
        {
            datas: {
                _id: "22",
                text: "Fonction 22",
                group_id: 10,
                is_main: true
            }
        },
        {
            datas: {
                _id: 12,
                text: "Fonction 12",
                group_id: 10,
                is_main: false
            }
        }
    ]

    private groupData = {
        datas: {
            _id: 10,
            text: "MB-Etab 10",
            raison_sociale: null
        }
    }

    private moduleTabsData = [
        {
            datas: {
                mod_name: "ModuleTest",
                tab_name: "Tab1",
                is_standard: true,
                is_param: false,
                is_config: false,
                pinned_order: null
            },
            links: { tab_url: "?m=dPcim10&tab=Tab1" }
        }, {
            datas: {
                mod_name: "ModuleTest",
                tab_name: "Tab2",
                is_standard: true,
                is_param: false,
                is_config: false,
                pinned_order: 0
            },
            links: { tab_url: "?m=dPcim10&tab=Tab2" }
        }, {
            datas: {
                mod_name: "ModuleTest",
                tab_name: "configure",
                is_standard: false,
                is_param: false,
                is_config: true,
                pinned_order: null
            },
            links: { tab_url: "?m=dPcim10&tab=configure" }
        }
    ]

    private placeholdersData = [
        {
            datas: {
                _id: "368145e1a559e52d8da68f489a2bbe13",
                label: "Saisir une prestation",
                icon: "time",
                disabled: false,
                action: null,
                action_args: null,
                init_action: null,
                counter: null
            }
        },
        {
            datas: {
                _id: "decba95f634235b27a2b3e9407fe871f",
                label: "Acces au porte documents",
                icon: "folderOpen",
                disabled: false,
                action: null,
                action_args: null,
                init_action: null,
                counter: 1
            }
        },
        {
            datas: {
                _id: "dfb234702cbe3adf51eff9ca9ba90d4f",
                label: "Acces a la messagerie interne",
                icon: "accountGroup",
                disabled: false,
                action: null,
                action_args: ["internal"],
                init_action: null,
                counter: 0
            }
        },
        {
            datas: {
                _id: "7ce3ae8be3a241302195c4a0500e4c6f",
                label: "Acces a la messagerie",
                icon: "email",
                disabled: false,
                action: "",
                action_args: null,
                init_action: null,
                counter: 0
            }
        }
    ]

    private userData = {
        datas: {
            initials: null,
            color: null,
            _color: "CCCCFF",
            actif: true,
            deb_activite: null,
            fin_activite: null,
            _user_first_name: "Yvan",
            _user_last_name: "GRADMIN",
            _user_sexe: "u",
            _user_username: "yvang",
            _initial: "YG",
            _font_color: "000000",
            _can_change_password: "1",
            _is_patient: false,
            _dark_mode: false,
            _ui_style: "mediboard"
        },
        links: {
            self: "/mediboard/api/mediuser/mediusers/985",
            schema: "/mediboard/api/schemas/mediuser",
            history: "/mediboard/api/history/mediuser/985",
            edit_infos: "?m=mediusers&a=edit_infos",
            logout: "?logout=-1",
            default: "?m=dPpatients"
        }
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<Appbar> {
        return shallowMount(this.component, {
            propsData: props,
            vuetify: new Vuetify(),
            stubs: {
                NavModules: true,
                NavModulesTamm: true,
                UserAccount: true,
                GroupSelector: true,
                PlaceholderShortcut: true,
                PlaceholderLine: true,
                ModuleDetailMobile: true
            }
        }) as Wrapper<Appbar>
    }

    public testDisplayAndHideNavModule () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false
        })
        this.assertEqual(this.privateCall(appbar.vm, "moduleClass"), "")
        this.privateCall(appbar.vm, "displayNavModule")
        this.assertEqual(this.privateCall(appbar.vm, "moduleClass"), "active")
        this.privateCall(appbar.vm, "displayNavModule")
        this.assertEqual(this.privateCall(appbar.vm, "moduleClass"), "")
        this.privateCall(appbar.vm, "displayNavModule")
        this.privateCall(appbar.vm, "clickOutside")
        this.assertEqual(this.privateCall(appbar.vm, "moduleClass"), "")
    }

    public testDisplayAndHideUserAccount () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        this.assertEqual(this.privateCall(appbar.vm, "accountClass"), "")
        this.privateCall(appbar.vm, "displayAccount")
        this.assertEqual(this.privateCall(appbar.vm, "accountClass"), "active")
    }

    public testDisplayAndHideGroups () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        this.assertEqual(this.privateCall(appbar.vm, "groupClass"), "")
        this.privateCall(appbar.vm, "displayGroups")
        this.assertEqual(this.privateCall(appbar.vm, "groupClass"), "active")
        this.privateCall(appbar.vm, "displayGroups")
        this.assertEqual(this.privateCall(appbar.vm, "groupClass"), "")
    }

    public testHidePlaceholders () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        this.assertFalse(this.privateCall(appbar.vm, "showPlaceholders"))
    }

    public async testShowPlaceholders () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: this.placeholdersData,
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        this.privateCall(appbar.vm, "openPlaceholders")
        this.assertTrue(this.privateCall(appbar.vm, "showPlaceholders"))
    }

    public async testClosePlaceholders () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: this.placeholdersData,
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        this.privateCall(appbar.vm, "openPlaceholders")
        this.assertTrue(this.privateCall(appbar.vm, "showPlaceholders"))
        this.privateCall(appbar.vm, "closePlaceholders")
        this.assertFalse(this.privateCall(appbar.vm, "showPlaceholders"))
    }

    public async testAppbarCreation () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: this.placeholdersData,
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        await appbar.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(OxStoreCore.getters.getCurrentModule.mod_name, "ModuleTest")
        this.assertEqual(OxStoreCore.getters.getTabActive, "Tab1")
    }

    public async testGetHomeLink () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        await appbar.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(this.privateCall(appbar.vm, "getHomeLink"), "?m=dPpatients")
    }

    public async testGetCurrentFunctionMainByDefault () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        await this.wait(10)
        await appbar.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(this.privateCall(appbar.vm, "getCurrentFunction").text, "Fonction 22")
    }

    public async testGetCurrentFunctionByUrl () {
        const location = {
            ...window.location,
            href: "http://test/index.php?f=12"
        }
        Object.defineProperty(window, "location", {
            writable: true,
            value: location
        })

        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        await this.wait(10)
        await appbar.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(this.privateCall(appbar.vm, "getCurrentFunction").text, "Fonction 12")
    }

    public testScreenSizeXXL () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })

        appbar.vm["screenWidth"] = 1300
        this.assertTrue(this.privateCall(appbar.vm, "isXXL"))
        this.assertTrue(this.privateCall(appbar.vm, "isXL"))
        this.assertTrue(this.privateCall(appbar.vm, "isL"))
        this.assertTrue(this.privateCall(appbar.vm, "isM"))
    }

    public testScreenSizeXL () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })

        appbar.vm["screenWidth"] = 1000
        this.assertFalse(this.privateCall(appbar.vm, "isXXL"))
        this.assertTrue(this.privateCall(appbar.vm, "isXL"))
        this.assertTrue(this.privateCall(appbar.vm, "isL"))
        this.assertTrue(this.privateCall(appbar.vm, "isM"))
    }

    public testScreenSizeL () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })

        appbar.vm["screenWidth"] = 900
        this.assertFalse(this.privateCall(appbar.vm, "isXXL"))
        this.assertFalse(this.privateCall(appbar.vm, "isXL"))
        this.assertTrue(this.privateCall(appbar.vm, "isL"))
        this.assertTrue(this.privateCall(appbar.vm, "isM"))
    }

    public testScreenSizeM () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })

        appbar.vm["screenWidth"] = 600
        this.assertFalse(this.privateCall(appbar.vm, "isXXL"))
        this.assertFalse(this.privateCall(appbar.vm, "isXL"))
        this.assertFalse(this.privateCall(appbar.vm, "isL"))
        this.assertTrue(this.privateCall(appbar.vm, "isM"))
    }

    public testScreenSizeS () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: [],
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })

        appbar.vm["screenWidth"] = 300
        this.assertFalse(this.privateCall(appbar.vm, "isXXL"))
        this.assertFalse(this.privateCall(appbar.vm, "isXL"))
        this.assertFalse(this.privateCall(appbar.vm, "isL"))
        this.assertFalse(this.privateCall(appbar.vm, "isM"))
    }

    public testDisplayTabs () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: this.placeholdersData,
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        this.privateCall(appbar.vm, "displayTabs")
        this.assertTrue(this.privateCall(appbar.vm, "showModuleDetail"))
    }

    public testCloseTabs () {
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: this.placeholdersData,
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        this.privateCall(appbar.vm, "displayTabs")
        this.assertTrue(this.privateCall(appbar.vm, "showModuleDetail"))
        this.privateCall(appbar.vm, "closeTabs")
        this.assertFalse(this.privateCall(appbar.vm, "showModuleDetail"))
    }

    public testAddBadge () {
        const tabBadge = { module_name: "ModuleTest", tab_name: "Tab1", counter: "7", color: "blue" }
        const appbar = this.mountComponent({
            currentModule: this.currentModuleData,
            functionsData: this.functionsData,
            groupData: this.groupData,
            moduleTabs: this.moduleTabsData,
            placeholdersData: this.placeholdersData,
            tabActive: "Tab1",
            user: this.userData,
            useVuetify: false,
            infoMaj: {}
        })
        this.privateCall(appbar.vm, "addBadge", { detail: tabBadge })
        this.assertEqual(OxStoreCore.getters.getTabBadge("ModuleTest", "Tab1"), tabBadge)
    }
}

(new AppbarTest()).launchTests()
