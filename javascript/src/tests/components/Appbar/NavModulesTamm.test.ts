/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { Wrapper } from "@vue/test-utils"
import NavModulesTamm from "@/components/Appbar/NavModules/NavModulesTamm/NavModulesTamm"
import Vue from "vue"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import ModuleSerializer from "@/components/Appbar/Serializer/ModuleSerializer"
import OxSerializerCore from "@/components/Core/OxSerializerCore"
import { ApiData } from "@/components/Models/ApiResponseModel"

/* eslint-disable dot-notation */

/**
 * Test pour la classe NavModulesTamm
 */
export default class NavModulesTammTest extends OxTest {
    protected component = NavModulesTamm

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
    protected vueComponent (props: object): NavModulesTamm {
        return this.mountComponent(props).vm as NavModulesTamm
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<NavModulesTamm> {
        return super.mountComponent(props) as Wrapper<NavModulesTamm>
    }

    public async testDefaultClasses () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            useProvider: false
        })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(this.privateCall(navModule.vm, "classes"), "")
    }

    public testExpand () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            useProvider: false
        })
        this.privateCall(navModule.vm, "toggleNav")
        this.assertTrue(navModule.vm["expand"])
    }

    public testCollapse () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            useProvider: false
        })
        this.privateCall(navModule.vm, "toggleNav")
        this.assertTrue(navModule.vm["expand"])
        this.privateCall(navModule.vm, "toggleNav")
        this.assertFalse(navModule.vm["expand"])
    }

    public async testNotDisplayEmptyByDefault () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            useProvider: false
        })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertFalse(this.privateCall(navModule.vm, "displayEmpty"))
    }

    public async testDisplayEmptyWhenNoMatchingSearch () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            useProvider: false
        })
        this.privateCall(navModule.vm, "filterModules", "NoMatchingSearch")
        await Vue.nextTick()
        this.assertTrue(this.privateCall(navModule.vm, "displayEmpty"))
        this.assertEqual(this.privateCall(navModule.vm, "modulesClasses"), "empty")
    }

    public async testSearchModulesByModName () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            useProvider: false
        })
        this.privateCall(navModule.vm, "filterModules", "dppatie")
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(this.privateCall(navModule.vm, "modules")[0].mod_name, "dPpatients")
    }

    public async testResetSearch () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
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

    public async testClickOutside () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
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
            useProvider: false
        })
        const moduleSelected = {
            mod_name: "ModuleSelected",
            tabs_order: [
                "Tab 3",
                "Tab 2",
                "Tab 1"
            ]
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

    public async testCheckActiveFalseForUnselectedModule () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
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
            useProvider: false
        })
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertEqual(
            this.privateCall(navModule.vm, "getModuleCategory", "ModuleTest"),
            "plateau_technique"
        )
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
            useProvider: false
        })
        expect(hrefSpy).not.toHaveBeenCalled()
        this.privateCall(navModule.vm, "accessToFirstModule")
        expect(hrefSpy).not.toHaveBeenCalled()
    }

    public async testDesactivated () {
        const navModule = this.mountComponent({
            defaultModulesData: this.modulesData,
            useProvider: false
        })
        navModule.vm["showDetail"] = true
        navModule.vm["detailModule"] = true
        this.privateCall(navModule.vm, "filterModules", "dppatie")
        this.privateCall(navModule.vm, "resetNavModules")
        await navModule.vm.$nextTick()
        await Vue.nextTick()
        this.assertFalse(navModule.vm["showDetail"])
        this.assertFalse(navModule.vm["detailModule"])
        this.assertEqual(navModule.vm["moduleFilter"], "")
    }
}

(new NavModulesTammTest()).launchTests()
