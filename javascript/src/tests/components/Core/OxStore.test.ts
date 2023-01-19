/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import ModuleSerializer from "@/components/Appbar/Serializer/ModuleSerializer"
import OxSerializerCore from "@/components/Core/OxSerializerCore"
import { ApiData } from "@/components/Models/ApiResponseModel"
import { TabBadgeModel } from "@/components/Appbar/Models/AppbarModel"

/**
 * Test pour OxStore
 */
export default class OxStoreTest extends OxTest {
    protected component = "OxStoreCore"

    private currentModule = {
        mod_name: "ModuleTest",
        pinned_tabs: [],
        standard_tabs: [],
        param_tabs: [],
        configure_tab: [],
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
                    tab_url: "tab-url-1"
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
                    tab_url: "tab-url-2"
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
                    tab_url: "tab-url-3"
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
                    tab_url: "tab-url-4"
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
                    tab_url: "tab-url-5"
                }
            }
        ]
    }

    protected async beforeTest () {
        await OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.currentModule } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
    }

    public async testPinTab () {
        const tab = {
            tab_name: "Tab 2",
            mod_name: "ModuleTest",
            _links: {
                tab_url: "tab-url-2"
            }
        }
        const mockedFunction = jest.fn()
        const provider = {
            putPinnedTabs: mockedFunction
        }
        await OxStoreCore.dispatch("pinTab", { tab, provider })
        this.assertHaveLength(OxStoreCore.getters.getStandardTabs, 1)
        this.assertEqual(OxStoreCore.getters.getPinnedTabs, [
            {
                tab_name: "Tab 1",
                mod_name: "ModuleTest",
                _links: {
                    tab_url: "tab-url-1"
                }
            },
            {
                tab_name: "Tab 2",
                mod_name: "ModuleTest",
                _links: {
                    tab_url: "tab-url-2"
                }
            }]
        )
        expect(mockedFunction).toBeCalled()
    }

    public async testUnpinTab () {
        const tab = {
            tab_name: "Tab 1",
            mod_name: "ModuleTest",
            _links: {
                tab_url: "tab-url-1"
            }
        }
        const mockedFunction = jest.fn()
        const provider = {
            putPinnedTabs: mockedFunction
        }
        await OxStoreCore.dispatch("unpinTab", { tab, provider })
        this.assertHaveLength(OxStoreCore.getters.getStandardTabs, 3)
        this.assertEqual(OxStoreCore.getters.getPinnedTabs, [])
        expect(mockedFunction).toBeCalled()
    }

    public async testSetTab () {
        const tabs = [{
            tab_name: "Tab 2",
            mod_name: "ModuleTest",
            _links: {
                tab_url: "tab-url-2"
            }
        }]
        const mockedFunction = jest.fn()
        const provider = {
            putPinnedTabs: mockedFunction
        }
        await OxStoreCore.dispatch("setPinnedTabs", { tabs, provider })
        this.assertEqual(OxStoreCore.getters.getPinnedTabs, tabs)
        expect(mockedFunction).toBeCalled()
    }

    public testAddTabBadge () {
        const tabBadge: TabBadgeModel = {
            tab_name: "Tab 1",
            module_name: "ModuleTest",
            counter: 4,
            color: "blue"
        }
        OxStoreCore.commit("addTabBadge", tabBadge)
        this.assertEqual(
            OxStoreCore.getters.getTabBadge("ModuleTest", "Tab 1"),
            tabBadge
        )
    }

    public testRemoveTabBadge () {
        const tabBadge1: TabBadgeModel = {
            tab_name: "Tab 1",
            module_name: "ModuleTest",
            counter: 4,
            color: "blue"
        }
        const tabBadge2: TabBadgeModel = {
            tab_name: "Tab 2",
            module_name: "ModuleTest",
            counter: 2,
            color: "red"
        }
        OxStoreCore.commit("addTabBadge", tabBadge1)
        OxStoreCore.commit("addTabBadge", tabBadge2)
        OxStoreCore.commit("removeTabBadge", tabBadge1)
        this.assertUndefined(OxStoreCore.getters.getTabBadge("ModuleTest", "Tab 1"))
        this.assertEqual(
            OxStoreCore.getters.getTabBadge("ModuleTest", "Tab 2"),
            tabBadge2
        )
    }

    public testGetBadgeIsNullWhenNoMathchingModuleName () {
        const tabBadge: TabBadgeModel = {
            tab_name: "Tab 1",
            module_name: "ModuleTest2",
            counter: 4,
            color: "blue"
        }
        OxStoreCore.commit("addTabBadge", tabBadge)
        this.assertUndefined(OxStoreCore.getters.getTabBadge("ModuleTest", "Tab 1"))
    }

    public testGetBadgeIsNullWhenNoMathchingTabName () {
        const tabBadge: TabBadgeModel = {
            tab_name: "Tab 10",
            module_name: "ModuleTest",
            counter: 4,
            color: "blue"
        }
        OxStoreCore.commit("addTabBadge", tabBadge)
        this.assertUndefined(OxStoreCore.getters.getTabBadge("ModuleTest", "Tab 1"))
    }

    public testUpdateBadge () {
        const tabBadge: TabBadgeModel = {
            tab_name: "Tab 1",
            module_name: "ModuleTest",
            counter: 4,
            color: "blue"
        }
        OxStoreCore.commit("addTabBadge", tabBadge)
        OxStoreCore.dispatch("updateTabBadge", Object.assign({}, tabBadge, { counter: 8 }))
        this.assertEqual(
            OxStoreCore.getters.getTabBadge("ModuleTest", "Tab 1").counter,
            8
        )
    }
}

(new OxStoreTest()).launchTests()
