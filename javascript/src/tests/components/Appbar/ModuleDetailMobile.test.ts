/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { Wrapper } from "@vue/test-utils"
import ModuleDetailMobile from "@/components/Appbar/ModuleDetail/ModuleDetailMobile/ModuleDetailMobile"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import ModuleSerializer from "@/components/Appbar/Serializer/ModuleSerializer"
import OxSerializerCore from "@/components/Core/OxSerializerCore"
import { ApiData } from "@/components/Models/ApiResponseModel"

/**
 * Test pour la classe ModuleDetailMobile
 */
export default class ModuleDetailMobileTest extends OxTest {
    protected component = ModuleDetailMobile
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
    protected vueComponent (props: object): ModuleDetailMobile {
        return this.mountComponent(props).vm as ModuleDetailMobile
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<ModuleDetailMobile> {
        return super.mountComponent(props) as Wrapper<ModuleDetailMobile>
    }

    public testShowFooterWhenOnlyParam (): void {
        const moduleWithoutConfigTab = JSON.parse(JSON.stringify(this.module))
        moduleWithoutConfigTab.configure_tab.length = 0
        const moduleDetail = this.mountComponent({ module: moduleWithoutConfigTab })
        this.assertTrue(this.privateCall(moduleDetail.vm, "showParam"))
        this.assertFalse(this.privateCall(moduleDetail.vm, "showConfig"))
        this.assertTrue(this.privateCall(moduleDetail.vm, "showFooter"))
    }

    public testShowFooterWhenOnlyConfig (): void {
        const moduleWithoutParamTab = JSON.parse(JSON.stringify(this.module))
        moduleWithoutParamTab.param_tabs.length = 0
        const moduleDetail = this.mountComponent({ module: moduleWithoutParamTab })
        this.assertFalse(this.privateCall(moduleDetail.vm, "showParam"))
        this.assertTrue(this.privateCall(moduleDetail.vm, "showConfig"))
        this.assertTrue(this.privateCall(moduleDetail.vm, "showFooter"))
    }

    public testShowFooterWhenParamAndConfig (): void {
        const moduleDetail = this.mountComponent({ module: this.module })
        this.assertTrue(this.privateCall(moduleDetail.vm, "showParam"))
        this.assertTrue(this.privateCall(moduleDetail.vm, "showConfig"))
        this.assertTrue(this.privateCall(moduleDetail.vm, "showFooter"))
    }

    public testHideFooterWhenNoParamAndConfig (): void {
        const moduleWithoutParamAndConfigTab = JSON.parse(JSON.stringify(this.module))
        moduleWithoutParamAndConfigTab.param_tabs.length = 0
        moduleWithoutParamAndConfigTab.configure_tab.length = 0
        const moduleDetail = this.mountComponent({ module: moduleWithoutParamAndConfigTab })
        this.assertFalse(this.privateCall(moduleDetail.vm, "showParam"))
        this.assertFalse(this.privateCall(moduleDetail.vm, "showConfig"))
        this.assertFalse(this.privateCall(moduleDetail.vm, "showFooter"))
    }

    public testParamTabGeneration (): void {
        const moduleDetail = this.mountComponent({ module: this.module })
        const result = this.privateCall(moduleDetail.vm, "paramTab")
        this.assertInstanceOf(result, Object)
        this.assertEqual(result, { tab_name: "", _links: { tab_url: "url-4" } })
    }

    public testConfigTabGeneration (): void {
        const moduleDetail = this.mountComponent({ module: this.module })
        const result = this.privateCall(moduleDetail.vm, "configTab")
        this.assertInstanceOf(result, Object)
        this.assertEqual(result, { tab_name: "Tab 5", _links: { tab_url: "url-5" } })
    }

    public testGetStandardTabs (): void {
        const moduleDetail = this.mountComponent({ module: this.module })
        const result = this.privateCall(moduleDetail.vm, "standardTabs")
        this.assertInstanceOf(result, Array)
        this.assertEqual(result, [
            { tab_name: "Tab 3", _links: { tab_url: "url-3" } },
            { tab_name: "Tab 2", _links: { tab_url: "url-2" } }
        ])
    }

    public testCheckNonActiveTabForDifferentModule (): void {
        const _module = JSON.parse(JSON.stringify(this.module))
        _module.mod_name = "Other Module"
        const moduleDetail = this.mountComponent({ module: _module })
        this.assertFalse(this.privateCall(moduleDetail.vm, "checkTabActive"))
    }

    public testActiveTab (): void {
        const moduleDetail = this.mountComponent({ module: this.module })
        this.assertTrue(this.privateCall(moduleDetail.vm, "checkTabActive", "Tab 1"))
    }

    public testNonActiveTab (): void {
        const moduleDetail = this.mountComponent({ module: this.module })
        this.assertFalse(this.privateCall(moduleDetail.vm, "checkTabActive", "Tab 2"))
    }

    public testActiveParamTab (): void {
        OxStoreCore.commit("setTabActive", "Tab 4")
        const moduleDetail = this.mountComponent({ module: this.module })
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertTrue(moduleDetail.vm.checkTabActive("", "param"))
    }

    public testActiveConfigTab (): void {
        OxStoreCore.commit("setTabActive", "Tab 5")
        const moduleDetail = this.mountComponent({ module: this.module })
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertTrue(moduleDetail.vm.checkTabActive("", "config"))
    }

    public testBackEvent (): void {
        const moduleDetail = this.mountComponent({ module: this.module, showBack: true })
        this.privateCall(moduleDetail.vm, "back")
        this.assertTrue(moduleDetail.emitted("close"))
    }
}

(new ModuleDetailMobileTest()).launchTests()
