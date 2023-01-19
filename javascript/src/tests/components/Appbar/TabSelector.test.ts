/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { Wrapper } from "@vue/test-utils"
import TabSelector from "@/components/Appbar/TabSelector/TabSelector"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import ModuleSerializer from "@/components/Appbar/Serializer/ModuleSerializer"
import OxSerializerCore from "@/components/Core/OxSerializerCore"
import { ApiData } from "@/components/Models/ApiResponseModel"

/**
 * Test pour la classe TabSelector
 */
export default class TabSelectorTest extends OxTest {
    protected component = TabSelector

    private module = {
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
                    tab_url: "url-1"
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
                    tab_url: "url-2"
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
                    tab_url: "url-3"
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
                    tab_url: "url-4"
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
                    tab_url: "url-5"
                }
            }
        ]
    }

    private tabs = [
        {
            tab_name: "TabTest1",
            mod_name: "ModuleTest",
            _links: {
                tab_url: "tab-url1"
            }
        },
        {
            tab_name: "TabTest2",
            mod_name: "ModuleTest",
            _links: {
                tab_url: "tab-url2"
            }
        },
        {
            tab_name: "TabTest3",
            mod_name: "ModuleTest",
            _links: {
                tab_url: "tab-url3"
            }
        }
    ]

    protected async beforeAllTests () {
        OxStoreCore.commit("setTabActive", "Tab 1")
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): TabSelector {
        return this.mountComponent(props).vm as TabSelector
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<TabSelector> {
        return super.mountComponent(props) as Wrapper<TabSelector>
    }

    public testGetModuleName () {
        const tabSelector = this.mountComponent({ tabs: this.tabs })
        this.assertEqual(this.privateCall(tabSelector.vm, "moduleName"), "ModuleTest")
    }

    public testParamTabGeneration () {
        const tabSelector = this.mountComponent({ tabs: this.tabs })
        const result = this.privateCall(tabSelector.vm, "paramTab")
        this.assertInstanceOf(result, Object)
        this.assertEqual(result, { tab_name: "", _links: { tab_url: "url-4" } })
    }

    public testConfigTabGeneration () {
        const tabSelector = this.mountComponent({ tabs: this.tabs })
        const result = this.privateCall(tabSelector.vm, "configTab")
        this.assertInstanceOf(result, Object)
        this.assertEqual(result, { tab_name: "Tab 5", _links: { tab_url: "url-5" } })
    }

    private testShowDividerWhenParamAndTabs () {
        const tabSelector = this.vueComponent({ tabs: this.tabs, param: true })
        this.assertTrue(this.privateCall(tabSelector, "showDivider"))
    }

    private testShowDividerWhenConfigAndTabs () {
        const tabSelector = this.vueComponent({ tabs: this.tabs, configure: true })
        this.assertTrue(this.privateCall(tabSelector, "showDivider"))
    }

    private testHideDividerWhenNoParamNoConfigAndTabs () {
        const tabSelector = this.vueComponent({ tabs: this.tabs })
        this.assertFalse(this.privateCall(tabSelector, "showDivider"))
    }

    private testHideDividerWhenNoTabs () {
        const tabSelector = this.vueComponent({ tabs: [], param: true, configure: true })
        this.assertFalse(this.privateCall(tabSelector, "showDivider"))
    }

    private async testPinTab () {
        const tabSelector = this.mountComponent({ tabs: this.tabs })
        this.privateCall(tabSelector.vm, "pinTab", this.tabs[1])
        await tabSelector.vm.$nextTick()
        this.assertTrue(tabSelector.emitted("addPin"))
        this.assertHaveLength(tabSelector.emitted("addPin"), 1)
        // @ts-ignore
        this.assertEqual(tabSelector.emitted("addPin")[0], [this.tabs[1]])
    }
}

(new TabSelectorTest()).launchTests()
