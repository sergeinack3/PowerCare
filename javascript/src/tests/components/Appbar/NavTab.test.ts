/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import NavTab from "@/components/Appbar/NavTabs/NavTab/NavTab"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import ModuleSerializer from "@/components/Appbar/Serializer/ModuleSerializer"
import OxSerializerCore from "@/components/Core/OxSerializerCore"
import { ApiData } from "@/components/Models/ApiResponseModel"
import { Wrapper } from "@vue/test-utils"

/**
 * Test pour la classe NavTab
 */
export default class NavTabTest extends OxTest {
    protected component = NavTab
    private tab = { tab_name: "Tab 1", _links: { tab_url: "url-1" } }
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
    protected vueComponent (props: object): NavTab {
        return this.mountComponent(props).vm as NavTab
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<NavTab> {
        return super.mountComponent(props) as Wrapper<NavTab>
    }

    public testDefaultContentClass () {
        const navTab = this.vueComponent({ tab: this.tab })
        this.assertEqual(this.privateCall(navTab, "contentClasses"), "")
    }

    public testActiveContentClass () {
        const navTab = this.vueComponent({ tab: this.tab, tabActive: true })
        this.assertEqual(this.privateCall(navTab, "contentClasses"), "active")
    }

    public testDefaultTabClasses () {
        const navTab = this.vueComponent({ tab: this.tab })
        this.assertEqual(this.privateCall(navTab, "classes"), " ")
    }

    public async testActiveTabClasses () {
        const navTab = this.vueComponent({ tab: this.tab, tabActive: true })
        this.assertEqual(this.privateCall(navTab, "classes"), "active animated")
        await this.wait(300)
        this.assertEqual(this.privateCall(navTab, "classes"), "active ")
    }

    public async testActiveTabClassesWhenParentHover () {
        const navTab = this.vueComponent({ tab: this.tab, tabActive: true, parentHover: true })
        this.assertEqual(this.privateCall(navTab, "classes"), " animated")
        await this.wait(300)
        this.assertEqual(this.privateCall(navTab, "classes"), " ")
    }

    public async testClassicTabName () {
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTab = this.vueComponent({ tab: this.tab })
        this.assertEqual(this.privateCall(navTab, "tabName"), "mod-ModuleTest-tab-Tab 1")
    }

    public testParamTabName () {
        const navTab = this.vueComponent({ tab: this.tab, param: true })
        this.assertEqual(this.privateCall(navTab, "tabName"), "settings")
    }

    public async testAddPinnedClick () {
        const navTab = this.mountComponent({ tab: this.tab })
        this.privateCall(navTab.vm, "addPinnedClick")
        await navTab.vm.$nextTick()
        this.assertTrue(navTab.emitted("addPin"))
        this.assertHaveLength(navTab.emitted("addPin"), 1)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertEqual(navTab.emitted("addPin")[0], [this.tab])
    }

    public async testRemovePinnedClick () {
        const navTab = this.mountComponent({ tab: this.tab })
        this.privateCall(navTab.vm, "removePinnedClick")
        await navTab.vm.$nextTick()
        this.assertTrue(navTab.emitted("removePin"))
        this.assertHaveLength(navTab.emitted("removePin"), 1)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertEqual(navTab.emitted("removePin")[0], [this.tab])
    }

    public async testEnterTab () {
        const navTab = this.mountComponent({ tab: this.tab })
        this.privateCall(navTab.vm, "enterTab")
        await navTab.vm.$nextTick()
        this.assertTrue(navTab.emitted("enter"))
    }

    public async testLeaveTab () {
        const navTab = this.mountComponent({ tab: this.tab })
        this.privateCall(navTab.vm, "leaveTab")
        await navTab.vm.$nextTick()
        this.assertTrue(navTab.emitted("leave"))
    }
}

(new NavTabTest()).launchTests()
