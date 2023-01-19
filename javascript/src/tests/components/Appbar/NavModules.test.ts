/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { mount, shallowMount, Wrapper } from "@vue/test-utils"
import NavModules from "@/components/Appbar/NavModules/NavModules"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import ModuleSerializer from "@/components/Appbar/Serializer/ModuleSerializer"
import OxSerializerCore from "@/components/Core/OxSerializerCore"
import { ApiData } from "@/components/Models/ApiResponseModel"
import Vue from "vue"

/* eslint-disable dot-notation */

/**
 * Test pour la classe NavModules
 */
export default class NavModulesTest extends OxTest {
    protected component = NavModules

    private modulesData = [
        {
            datas: {
                mod_name: "ModuleTest",
                mod_category: "plateau_technique"
            },
            links: {
                module_url: "?m=dPbloc",
                tabs: "/mediboard/api/modules/dPbloc/tabs"
            }
        },
        {
            datas: {
                mod_name: "dPccam",
                mod_category: "referentiel"
            },
            links: {
                module_url: "?m=dPccam",
                tabs: "/mediboard/api/modules/dPccam/tabs"
            }
        },
        {
            datas: {
                mod_name: "dPcim10",
                mod_category: "referentiel"
            },
            links: {
                module_url: "?m=dPcim10",
                tabs: "/mediboard/api/modules/dPcim10/tabs"
            }
        },
        {
            datas: {
                mod_name: "dPcabinet",
                mod_category: "dossier_patient"
            },
            links: {
                module_url: "?m=dPcabinet",
                tabs: "/mediboard/api/modules/dPcabinet/tabs"
            }
        },
        {
            datas: {
                mod_name: "dPpatients",
                mod_category: "dossier_patient"
            },
            links: {
                module_url: "?m=dPpatients",
                tabs: "/mediboard/api/modules/dPpatients/tabs"
            }
        }
    ]

    private shortcuts = [
        {
            datas: {
                mod_name: "dPccam",
                tab_name: "cim",
                is_standard: false,
                is_param: false,
                is_config: false,
                pinned_order: null
            },
            links: {
                tab_url: "?m=dPcim10&tab=cim"
            }
        },
        {
            datas: {
                mod_name: "dPcim10",
                tab_name: "vw_stats",
                is_standard: false,
                is_param: false,
                is_config: false,
                pinned_order: null
            },
            links: {
                tab_url: "?m=dPprescription&tab=vw_stats"
            }
        },
        {
            datas: {
                mod_name: "dPpatients",
                tab_name: "vw_idx_patients",
                is_standard: false,
                is_param: false,
                is_config: false,
                pinned_order: null
            },
            links: {
                tab_url: "?m=dPpatients&tab=vw_idx_patients"
            }
        },
        {
            datas: {
                mod_name: "dPpatients",
                tab_name: "vw_edit_planning",
                is_standard: false,
                is_param: false,
                is_config: false,
                pinned_order: null
            },
            links: {
                tab_url: "?m=dPbloc&tab=vw_edit_planning"
            }
        }
    ]

    private currentModule = {
        mod_name: "ModuleTest",
        tabs_order: [
            "Tab 3",
            "Tab 2",
            "Tab 1"
        ]
    }

    private currentModuleDetail = {
        mod_name: "ModuleTest",
        pinned_tabs: [
            {
                tab_name: "Tab 1",
                _links: {
                    tab_url: "url-1"
                }
            }
        ],
        standard_tabs: [
            {
                tab_name: "Tab 2",
                _links: {
                    tab_url: "url-2"
                }
            },
            {
                tab_name: "Tab 3",
                _links: {
                    tab_url: "url-3"
                }
            }
        ],
        param_tabs: [
            {
                tab_name: "Tab 4",
                _links: {
                    tab_url: "url-4"
                }
            }
        ],
        configure_tab: [
            {
                tab_name: "Tab 5",
                _links: {
                    tab_url: "url-5"
                }
            }
        ],
        tabs_order: [
            "Tab 3",
            "Tab 2",
            "Tab 1"
        ],
        tabs: [
            {
                mod_name: "ModuleTest",
                tab_name: "Tab 1",
                is_standard: true,
                is_param: false,
                is_config: false,
                pinned_order: 0,
                _links: {
                    tab_url: "url"
                }
            },
            {
                mod_name: "ModuleTest",
                tab_name: "Tab 2",
                is_standard: true,
                is_param: false,
                is_config: false,
                pinned_order: null,
                _links: {
                    tab_url: "url"
                }
            },
            {
                mod_name: "ModuleTest",
                tab_name: "Tab 3",
                is_standard: true,
                is_param: false,
                is_config: false,
                pinned_order: null,
                _links: {
                    tab_url: "url"
                }
            },
            {
                mod_name: "ModuleTest",
                tab_name: "Tab 4",
                is_standard: false,
                is_param: true,
                is_config: false,
                pinned_order: null,
                _links: {
                    tab_url: "url"
                }
            },
            {
                mod_name: "ModuleTest",
                tab_name: "Tab 5",
                is_standard: false,
                is_param: false,
                is_config: true,
                pinned_order: null,
                _links: {
                    tab_url: "url"
                }
            }
        ]
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): NavModules {
        return this.mountComponent(props).vm as NavModules
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<NavModules> {
        return super.mountComponent(props) as Wrapper<NavModules>
    }

    public async testExpandOnSearch () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        this.privateCall(navModule.vm, "filterModules", "dppatie")
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(this.privateCall(navModule.vm, "classes"), "expand")
    }

    public async testCollapseOnEmptySearch () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        this.privateCall(navModule.vm, "filterModules", "dppatie")
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.privateCall(navModule.vm, "filterModules", "")
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(this.privateCall(navModule.vm, "classes"), "")
    }

    public async testDefaultClasses () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(this.privateCall(navModule.vm, "classes"), "")
    }

    public async testNotDisplayEmptyByDefault () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertFalse(this.privateCall(navModule.vm, "displayEmpty"))
    }

    public async testDisplayEmptyWhenNoMatchingSearch () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        this.privateCall(navModule.vm, "filterModules", "NonExistentModule")
        await Vue.nextTick()
        this.assertTrue(this.privateCall(navModule.vm, "displayEmpty"))
        this.assertEqual(this.privateCall(navModule.vm, "modulesClasses"), "empty")
    }

    public async testSearchModulesByModName () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        this.privateCall(navModule.vm, "filterModules", "dppatie")
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(this.privateCall(navModule.vm, "modules")[0].mod_name, "dPpatients")
        this.assertEqual(this.privateCall(navModule.vm, "classes"), "expand")
    }

    public async testResetSearch () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        this.privateCall(navModule.vm, "filterModules", "dppatie")
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.privateCall(navModule.vm, "resetSearch")
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(this.privateCall(navModule.vm, "classes"), "")
    }

    public async testShowFavTabWhenShortcuts () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertTrue(this.privateCall(navModule.vm, "showFavTabs"))
    }

    public async testHideFavTabWhenNoShortcut () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: { data: [] },
            useProvider: false
        })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertFalse(this.privateCall(navModule.vm, "showFavTabs"))
    }

    public async testClickOutside () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        this.privateCall(navModule.vm, "clickOutside")
        await navModule.vm.$nextTick()
        this.assertTrue(navModule.emitted("input"))
        // @ts-ignore
        this.assertEqual(navModule.emitted("input")[0], [false])
    }

    public async testAffectDetailledModuleForCurrentModule () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.currentModule } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        this.privateCall(navModule.vm, "affectDetailledModule", { module: this.currentModuleDetail, fromKeyboard: false })
        this.assertTrue(this.privateCall(navModule.vm, "checkActive", this.currentModuleDetail.mod_name))
    }

    public async testAffectDetailledModule () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        const moduleSelected = {
            mod_name: "ModuleSelected",
            tabs_order: [
                "Tab 3",
                "Tab 2",
                "Tab 1"
            ],
            standard_tabs: [
                {
                    tab_name: "Tab A",
                    _links: {
                        tab_url: "url-a"
                    }
                },
                {
                    tab_name: "Tab B",
                    _links: {
                        tab_url: "url-b"
                    }
                }
            ],
            pinned_tabs: [],
            param_tabs: [],
            configure_tab: []
        }
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.currentModule } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        this.privateCall(navModule.vm, "affectDetailledModule", { module: moduleSelected, fromKeyboard: false })
        this.assertTrue(this.privateCall(navModule.vm, "checkActive", moduleSelected.mod_name))
    }

    public async testAffectDetailledModuleFromKeyboard () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.currentModuleDetail } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        this.privateCall(navModule.vm, "affectDetailledModule", { module: this.currentModuleDetail, fromKeyboard: true })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(navModule.vm["focusTabIndex"], 0)
        this.assertEqual(navModule.vm["tabLimit"], 4)
    }

    public async testCheckActiveFalseForUnselectedModule () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.currentModule } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        this.privateCall(navModule.vm, "affectDetailledModule", { module: this.currentModule, fromKeyboard: false })
        this.assertFalse(this.privateCall(navModule.vm, "checkActive", "OtherModule"))
    }

    public async testGetModulePosition () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        this.privateCall(navModule.vm, "affectDetailledModule", { module: this.currentModule, fromKeyboard: false })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(this.privateCall(navModule.vm, "getModulePosition"), 0)
    }

    public async testGetModuleCategory () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(
            this.privateCall(navModule.vm, "getModuleCategory", "ModuleTest"),
            "plateau_technique"
        )
    }

    public testArrowUpNavigationAtBeginning () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        this.assertEqual(navModule.vm["focusModuleIndex"], -1)
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowUp" }))
        this.assertEqual(navModule.vm["focusModuleIndex"], -1)
    }

    public async testArrowDownNavigationAtBeginning () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(navModule.vm["focusModuleIndex"], -1)
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(navModule.vm["focusModuleIndex"], 0)
    }

    public async testArrowDownNavigationAtEnd () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(navModule.vm["focusModuleIndex"], -1)
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.assertEqual(navModule.vm["focusModuleIndex"], 4)
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.assertEqual(navModule.vm["focusModuleIndex"], 4)
    }

    public async testArrowUpNavigation () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(navModule.vm["focusModuleIndex"], -1)
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowUp" }))
        this.assertEqual(navModule.vm["focusModuleIndex"], 1)
    }

    public async testArrowDownDetailTabNavigation () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.currentModuleDetail } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        this.privateCall(navModule.vm, "affectDetailledModule", { module: this.currentModuleDetail, fromKeyboard: true })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.assertEqual(navModule.vm["focusModuleIndex"], -1)
        this.assertEqual(navModule.vm["focusTabIndex"], 1)
    }

    public async testArrowUpDetailTabNavigationAtBeginning () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.currentModuleDetail } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        this.privateCall(navModule.vm, "affectDetailledModule", { module: this.currentModuleDetail, fromKeyboard: true })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowUp" }))
        this.assertEqual(navModule.vm["focusModuleIndex"], -1)
        this.assertEqual(navModule.vm["focusTabIndex"], 0)
    }

    public async testArrowDownDetailTabNavigationAtEnd () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.currentModuleDetail } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        this.privateCall(navModule.vm, "affectDetailledModule", { module: this.currentModuleDetail, fromKeyboard: true })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.assertEqual(navModule.vm["focusTabIndex"], 4)
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        this.assertEqual(navModule.vm["focusTabIndex"], 4)
    }

    public async testUnsetFocusDetail () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.currentModuleDetail } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.privateCall(navModule.vm, "keyboardShortcutAccess", new KeyboardEvent("keydown", { code: "ArrowDown" }))
        await navModule.vm["affectDetailledModule"]({ module: this.currentModuleDetail, fromKeyboard: true })
        this.privateCall(navModule.vm, "unsetFocusDetail")
        this.assertEqual(navModule.vm["focusTabIndex"], -1)
        this.assertEqual(navModule.vm["focusModuleIndex"], 0)
        this.assertEqual(navModule.vm["saveFocusModuleIndex"], -1)
    }

    public async testAccessToFirstModuleWhenSearch () {
        const hrefSpy = jest.fn()
        // @ts-ignore
        delete window.location
        // @ts-ignore
        window.location = {}
        Object.defineProperty(window.location, "href", {
            get: hrefSpy,
            set: hrefSpy
        })
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        expect(hrefSpy).not.toHaveBeenCalled()
        this.privateCall(navModule.vm, "filterModules", "dppatie")
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.privateCall(navModule.vm, "accessToFirstModule")
        expect(hrefSpy).toHaveBeenCalled()
    }

    public async testDontAccessToFirstModuleWhenNoSearch () {
        const hrefSpy = jest.fn()
        // @ts-ignore
        delete window.location
        // @ts-ignore
        window.location = {}
        Object.defineProperty(window.location, "href", {
            get: hrefSpy,
            set: hrefSpy
        })
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        expect(hrefSpy).not.toHaveBeenCalled()
        this.privateCall(navModule.vm, "accessToFirstModule")
        expect(hrefSpy).not.toHaveBeenCalled()
    }

    public async testDesactivated () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            tabShortcuts: this.shortcuts,
            useProvider: false
        })
        navModule.vm["showDetail"] = true
        navModule.vm["detailModule"] = true
        navModule.vm["focusModuleIndex"] = 4
        navModule.vm["focusTabIndex"] = 2
        this.privateCall(navModule.vm, "filterModules", "dppatie")
        this.privateCall(navModule.vm, "resetNavModules")
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertFalse(navModule.vm["showDetail"])
        this.assertFalse(navModule.vm["detailModule"])
        this.assertEqual(navModule.vm["focusModuleIndex"], -1)
        this.assertEqual(navModule.vm["focusTabIndex"], -1)
        this.assertEqual(navModule.vm["moduleFilter"], "")
    }
}

(new NavModulesTest()).launchTests()
