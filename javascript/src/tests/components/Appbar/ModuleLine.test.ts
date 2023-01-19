/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import ModuleLine from "@/components/Appbar/ModuleLine/ModuleLine"
import { Wrapper } from "@vue/test-utils"

/**
 * Test pour la classe ModuleLine
 */
export default class ModuleLineTest extends OxTest {
    protected component = ModuleLine

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
        ],
        _links: {
            module_url: "module-ulr"
        }
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): ModuleLine {
        return this.mountComponent(props).vm as ModuleLine
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<ModuleLine> {
        return super.mountComponent(props) as Wrapper<ModuleLine>
    }

    public testClassesWhenActive (): void {
        const moduleLine = this.vueComponent({ module: this.module, isActive: true })
        this.assertEqual(this.privateCall(moduleLine, "classes"), "active")
    }

    public testClassesWhenInactive (): void {
        const moduleLine = this.vueComponent({ module: this.module })
        this.assertEqual(this.privateCall(moduleLine, "classes"), "")
    }

    public testClassesWhenFocus () {
        const moduleLine = this.vueComponent({ module: this.module, isFocus: true })
        this.assertEqual(this.privateCall(moduleLine, "classes"), " focused")
    }

    public testClassesWhenActiveAndFocus () {
        const moduleLine = this.vueComponent({ module: this.module, isActive: true, isFocus: true })
        this.assertEqual(this.privateCall(moduleLine, "classes"), "active focused")
    }

    public async testClickDetailFromMouse (): Promise<void> {
        const moduleLine = this.mountComponent({ module: this.module })
        this.privateCall(moduleLine.vm, "clickDetail")
        await moduleLine.vm.$nextTick()
        this.assertTrue(moduleLine.emitted("detailClick"))
        this.assertHaveLength(moduleLine.emitted("detailClick"), 1)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertEqual(moduleLine.emitted("detailClick")[0], [{ module: this.module, fromKeyboard: false }])
    }

    public async testClickDetailFromKeyboard (): Promise<void> {
        const moduleLine = this.mountComponent({ module: this.module })
        moduleLine.vm["clickDetail"](new KeyboardEvent("keydown"), true)
        await moduleLine.vm.$nextTick()
        this.assertTrue(moduleLine.emitted("detailClick"))
        this.assertHaveLength(moduleLine.emitted("detailClick"), 1)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertEqual(moduleLine.emitted("detailClick")[0], [{ module: this.module, fromKeyboard: true }])
    }
}

(new ModuleLineTest()).launchTests()
