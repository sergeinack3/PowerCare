/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { shallowMount, Wrapper } from "@vue/test-utils"
import ModuleDetail from "@/components/Appbar/ModuleDetail/ModuleDetail"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import ModuleSerializer from "@/components/Appbar/Serializer/ModuleSerializer"
import OxSerializerCore from "@/components/Core/OxSerializerCore"
import { ApiData } from "@/components/Models/ApiResponseModel"

/**
 * Test pour la classe ModuleDetail
 */
export default class ModuleDetailTest extends OxTest {
    protected component = ModuleDetail
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
    protected vueComponent (props: object): ModuleDetail {
        return shallowMount(
            ModuleDetail,
            {
                propsData: props,
                stubs: {
                    Draggable: true
                }
            }
        ).vm
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<ModuleDetail> {
        return shallowMount(
            ModuleDetail,
            {
                propsData: props,
                stubs: {
                    Draggable: true
                }
            }
        )
    }

    public testShowPinnedTabsWhenExist (): void {
        const moduleDetail = this.mountComponent({ module: this.module })
        this.assertTrue(this.privateCall(moduleDetail.vm, "showPinnedTabs"))
    }

    public testHidePinnedTabsWhenNotExist (): void {
        const moduleWithoutPinnedTab = JSON.parse(JSON.stringify(this.module))
        moduleWithoutPinnedTab.pinned_tabs.length = 0
        const moduleDetail = this.mountComponent({ module: moduleWithoutPinnedTab })
        this.assertFalse(this.privateCall(moduleDetail.vm, "showPinnedTabs"))
    }

    public testShowStandardTabsWhenExist (): void {
        const moduleDetail = this.mountComponent({ module: this.module })
        this.assertTrue(this.privateCall(moduleDetail.vm, "showStandardTabs"))
    }

    public testHideStandardTabsWhenNotExist (): void {
        const moduleWithoutStandardTab = JSON.parse(JSON.stringify(this.module))
        moduleWithoutStandardTab.standard_tabs.length = 0
        const moduleDetail = this.mountComponent({ module: moduleWithoutStandardTab })
        this.assertFalse(this.privateCall(moduleDetail.vm, "showStandardTabs"))
    }

    public testShowDetailWhenAvailable (): void {
        const moduleDetail = this.mountComponent({ module: this.module })
        this.assertTrue(this.privateCall(moduleDetail.vm, "showDetail"))
    }

    public testHideDetailWhenNotAvailable (): void {
        const moduleDetail = this.mountComponent({ module: false })
        this.assertFalse(this.privateCall(moduleDetail.vm, "showDetail"))
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

    public testCurrentPinnedTabs (): void {
        const moduleDetail = this.mountComponent({ module: this.module })
        this.assertInstanceOf(this.privateCall(moduleDetail.vm, "currentPinnedTabs"), Array)
        this.assertEqual(this.privateCall(moduleDetail.vm, "currentPinnedTabs"), this.module.pinned_tabs)
    }

    public async testChangePinnedTabs (): Promise<void> {
        const moduleDetail = this.mountComponent({ module: this.module })
        const newPinnedTabs = [
            {
                tab_name: "Tab 1",
                _links: {
                    tab_url: "url"
                }
            },
            {
                tab_name: "Tab 2",
                _links: {
                    tab_url: "url"
                }
            }
        ]
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        moduleDetail.vm.currentPinnedTabs = newPinnedTabs
        await moduleDetail.vm.$nextTick()
        this.assertTrue(moduleDetail.emitted("changePin"))
        this.assertHaveLength(moduleDetail.emitted("changePin"), 1)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertEqual(moduleDetail.emitted("changePin")[0], [newPinnedTabs])
    }

    public testDragDisabledWhenLessTwoPinnedTabs (): void {
        const moduleDetail = this.mountComponent({ module: this.module })
        this.assertTrue(this.privateCall(moduleDetail.vm, "disableDrag"))
    }

    public testDragEnabledWhenMoreThanOnePinnedTab (): void {
        const moduleWithMultiplePinnedTabs = JSON.parse(JSON.stringify(this.module))
        moduleWithMultiplePinnedTabs.pinned_tabs.push(
            {
                tab_name: "Tab 2",
                _links: {
                    tab_url: "url"
                }
            }
        )
        const moduleDetail = this.mountComponent({ module: moduleWithMultiplePinnedTabs })
        this.assertFalse(this.privateCall(moduleDetail.vm, "disableDrag"))
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

    public testDontFocusOnParamWhenParamBeforeStandard () {
        const moduleDetail = this.mountComponent({ module: this.module, focusTabIndex: 2 })
        this.assertFalse(this.privateCall(moduleDetail.vm, "checkFocusForParam"))
    }

    public testFocusOnParamWhenParamAfterStandard () {
        const moduleDetail = this.mountComponent({ module: this.module, focusTabIndex: 3 })
        this.assertTrue(this.privateCall(moduleDetail.vm, "checkFocusForParam"))
    }

    public testDontFocusOnParamWhenNoParam () {
        const moduleWithoutParamTab = JSON.parse(JSON.stringify(this.module))
        moduleWithoutParamTab.param_tabs.length = 0
        const moduleDetail = this.mountComponent({ module: moduleWithoutParamTab, focusTabIndex: 3 })
        this.assertFalse(this.privateCall(moduleDetail.vm, "checkFocusForParam"))
    }

    public testFocusOnConfigWhenParamAndConfig () {
        const moduleDetail = this.mountComponent({ module: this.module, focusTabIndex: 4 })
        this.assertTrue(this.privateCall(moduleDetail.vm, "checkFocusForConfig"))
    }

    public testDontFocusOnConfigBeforeParam () {
        const moduleDetail = this.mountComponent({ module: this.module, focusTabIndex: 3 })
        this.assertFalse(this.privateCall(moduleDetail.vm, "checkFocusForConfig"))
    }

    public testDontFocusOnConfigWhenNoConfig () {
        const moduleWithoutConfigTab = JSON.parse(JSON.stringify(this.module))
        moduleWithoutConfigTab.param_tabs.length = 0
        const moduleDetail = this.mountComponent({ module: moduleWithoutConfigTab, focusTabIndex: 4 })
        this.assertFalse(this.privateCall(moduleDetail.vm, "checkFocusForConfig"))
    }

    public testFocusOnConfigAfterStandardWhenNoParam () {
        const moduleWithoutParamTab = JSON.parse(JSON.stringify(this.module))
        moduleWithoutParamTab.param_tabs.length = 0
        const moduleDetail = this.mountComponent({ module: moduleWithoutParamTab, focusTabIndex: 3 })
        this.assertTrue(this.privateCall(moduleDetail.vm, "checkFocusForConfig"))
    }

    public async testAddPinEvent (): Promise<void> {
        const moduleDetail = this.mountComponent({ module: this.module })
        const tab = { tab_name: "Tab 3", _links: { tab_url: "url-3" } }
        this.privateCall(moduleDetail.vm, "addPin", tab)
        await moduleDetail.vm.$nextTick()
        this.assertTrue(moduleDetail.emitted("addPin"))
        this.assertHaveLength(moduleDetail.emitted("addPin"), 1)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertEqual(moduleDetail.emitted("addPin")[0], [tab])
    }

    public async testRemovePinEvent (): Promise<void> {
        const moduleDetail = this.mountComponent({ module: this.module })
        const tab = { tab_name: "Tab 3", _links: { tab_url: "url-3" } }
        this.privateCall(moduleDetail.vm, "removePin", tab)
        await moduleDetail.vm.$nextTick()
        this.assertTrue(moduleDetail.emitted("removePin"))
        this.assertHaveLength(moduleDetail.emitted("removePin"), 1)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertEqual(moduleDetail.emitted("removePin")[0], [tab])
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

    public async testUnsetFocusEvent () {
        const moduleDetail = this.mountComponent({ module: this.module })
        this.privateCall(moduleDetail.vm, "unsetFocus")
        await moduleDetail.vm.$nextTick()
        this.assertTrue(moduleDetail.emitted("unsetFocus"))
    }
}

(new ModuleDetailTest()).launchTests()
