/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import NavTabs from "@/components/Appbar/NavTabs/NavTabs"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import ModuleSerializer from "@/components/Appbar/Serializer/ModuleSerializer"
import OxSerializerCore from "@/components/Core/OxSerializerCore"
import { ApiData } from "@/components/Models/ApiResponseModel"
import { shallowMount } from "@vue/test-utils"

/**
 * Test pour la classe NavTabs
 */
export default class NavTabsTest extends OxTest {
    protected component = NavTabs

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
    protected vueComponent (props: object): NavTabs {
        return shallowMount(
            this.component,
            {
                propsData: props,
                stubs: {
                    TabSelector: true
                }
            }
        ).vm
    }

    public async testShowMoreTabsWhenMoreStandardTabs () {
        const moduleWithStandardTabs = JSON.parse(JSON.stringify(this.module))
        moduleWithStandardTabs.configure_tab.length = 0
        moduleWithStandardTabs.standard_tabs.length = 0
        moduleWithStandardTabs.tabs = [
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
            }
        ]
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: moduleWithStandardTabs } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertTrue(this.privateCall(navTabs, "showMoreTabs"))
    }

    public async testShowMoreTabsWhenConfigureTab () {
        const moduleWithConfigureTabs = JSON.parse(JSON.stringify(this.module))
        moduleWithConfigureTabs.param_tabs.length = 0
        moduleWithConfigureTabs.standard_tabs.length = 0
        moduleWithConfigureTabs.tabs = [
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
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: moduleWithConfigureTabs } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertTrue(this.privateCall(navTabs, "showMoreTabs"))
    }

    public async testShowMoreTabsWhenParamTab () {
        const moduleWithParamTabs = JSON.parse(JSON.stringify(this.module))
        moduleWithParamTabs.configure_tab.length = 0
        moduleWithParamTabs.standard_tabs.length = 0
        moduleWithParamTabs.tabs = [
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
                tab_name: "Tab 4",
                is_standard: false,
                is_param: true,
                is_config: false,
                pinned_order: null,
                _links: {
                    tab_url: "url"
                }
            }
        ]
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: moduleWithParamTabs } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertTrue(this.privateCall(navTabs, "showMoreTabs"))
    }

    public async testDontShowMoreTabs () {
        const moduleWithoutMoreTabs = JSON.parse(JSON.stringify(this.module))
        moduleWithoutMoreTabs.configure_tab.length = 0
        moduleWithoutMoreTabs.param_tabs.length = 0
        moduleWithoutMoreTabs.standard_tabs.length = 0
        moduleWithoutMoreTabs.tabs = [
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
            }
        ]
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: moduleWithoutMoreTabs } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertFalse(this.privateCall(navTabs, "showMoreTabs"))
    }

    public async testShowCurrentStandardTabIfActive () {
        OxStoreCore.commit("setTabActive", "Tab 2")
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertTrue(this.privateCall(navTabs, "showCurrentStandardTab"))
    }

    public async testDontShowCurrentStandardTabIfActiveIsPined () {
        OxStoreCore.commit("setTabActive", "Tab 1")
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertFalse(this.privateCall(navTabs, "showCurrentStandardTab"))
    }

    public async testDontShowCurrentStandardIfNull () {
        OxStoreCore.commit("setTabActive", "")
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertFalse(this.privateCall(navTabs, "showCurrentStandardTab"))
    }

    public async testTabActiveIsPinableIfStandard () {
        OxStoreCore.commit("setTabActive", "Tab 2")
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertTrue(this.privateCall(navTabs, "tabActiveIsPinnable"))
    }

    public async testTabActiveIsPinableIfPinned () {
        OxStoreCore.commit("setTabActive", "Tab 1")
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertFalse(this.privateCall(navTabs, "tabActiveIsPinnable"))
    }

    public async testShowPinnedTabsIfExist () {
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertTrue(this.privateCall(navTabs, "showPinnedTabs"))
    }

    public async testShowPinnedTabsIfNotExist () {
        const moduleWithoutPinnedTabs = JSON.parse(JSON.stringify(this.module))
        moduleWithoutPinnedTabs.configure_tab.length = 0
        moduleWithoutPinnedTabs.tabs = [
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
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: moduleWithoutPinnedTabs } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertFalse(this.privateCall(navTabs, "showPinnedTabs"))
    }

    public async testDisabledDragIfLessThanTwoPinnedTabs () {
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertTrue(this.privateCall(navTabs, "disableDrag"))
    }

    public async testEnabledDragIfMoreThanOnePinnedTab () {
        const moduleWithPinnedTabs = JSON.parse(JSON.stringify(this.module))
        moduleWithPinnedTabs.tabs = [
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
                tab_name: "Tab 6",
                is_standard: true,
                is_param: false,
                is_config: false,
                pinned_order: 1,
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
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: moduleWithPinnedTabs } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertFalse(this.privateCall(navTabs, "disableDrag"))
    }

    public async testDefaultMoreTabsButtonClass () {
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertEqual(this.privateCall(navTabs, "moreTabsTabClass"), { active: false, standard: false })
    }

    public async testActiveMoreTabsButtonClass () {
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.privateCall(navTabs, "moreClick")
        await navTabs.$nextTick()
        this.assertEqual(this.privateCall(navTabs, "moreTabsTabClass"), { active: true, standard: false })
    }

    public async testIsActiveForActiveTab () {
        OxStoreCore.commit("setTabActive", "Tab 1")
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertTrue(this.privateCall(navTabs, "isActive", "Tab 1"))
    }

    public async testStandardTabClassWhenNotAlone () {
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        await navTabs.$nextTick()
        this.assertEqual(this.privateCall(navTabs, "standardTabClasses"), { standard: true, lonely: false, round: false })
    }

    public async testStandardTabClassWhenAlone () {
        const moduleWithoutPinnedTab = { ...this.module }
        moduleWithoutPinnedTab.pinned_tabs.length = 0
        moduleWithoutPinnedTab.tabs.shift()
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: moduleWithoutPinnedTab } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        await navTabs.$nextTick()
        this.assertEqual(this.privateCall(navTabs, "standardTabClasses"), { standard: true, lonely: true, round: false })
    }

    public async testStandardTabClassWhenNoMoreTab () {
        const moduleWithOneTab = { ...this.module }
        moduleWithOneTab.pinned_tabs.length = 0
        moduleWithOneTab.standard_tabs.length = 0
        moduleWithOneTab.param_tabs.length = 0
        moduleWithOneTab.tabs.splice(0, 4)
        OxStoreCore.commit("setTabActive", "Tab 5")
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: moduleWithOneTab } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        await navTabs.$nextTick()
        this.assertEqual(this.privateCall(navTabs, "standardTabClasses"), { standard: true, lonely: true, round: true })
    }

    public async testIsActiveForInactiveTab () {
        OxStoreCore.commit("setTabActive", "Tab 1")
        OxStoreCore.commit("setCurrentModule", ModuleSerializer.serialize(
            (await new OxSerializerCore(
                { attributes: this.module } as unknown as ApiData,
                {},
                {},
                []
            ).translateData()).data
        ))
        const navTabs = this.vueComponent({})
        this.assertFalse(this.privateCall(navTabs, "isActive", "Tab 2"))
    }
}

(new NavTabsTest()).launchTests()
