/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { Wrapper } from "@vue/test-utils"
import TabLine from "@/components/Appbar/TabLine/TabLine"
import { Tab } from "@/components/Appbar/Models/AppbarModel"

/**
 * Test pour la classe TabLine
 */
export default class TabLineTest extends OxTest {
    protected component = TabLine

    private tab: Tab = {
        tab_name: "TabTest",
        mod_name: "ModuleTest",
        _links: {
            tab_url: "tab-url"
        }
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): TabLine {
        return this.mountComponent(props).vm as TabLine
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<TabLine> {
        return super.mountComponent(props) as Wrapper<TabLine>
    }

    public testClassesWhenPinned () {
        const tabLine = this.vueComponent({ tab: this.tab, moduleName: "ModuleTest", pined: true })
        this.assertEqual(
            this.privateCall(tabLine, "classes"),
            {
                pined: true,
                active: false,
                pinable: true,
                focused: false
            }
        )
    }

    public testClassesWhenNotPinable () {
        const tabLine = this.vueComponent({ tab: this.tab, moduleName: "ModuleTest", showPin: false })
        this.assertEqual(
            this.privateCall(tabLine, "classes"),
            {
                pined: false,
                active: false,
                pinable: false,
                focused: false
            }
        )
    }

    public testClassesWhenActive () {
        const tabLine = this.vueComponent({ tab: this.tab, moduleName: "ModuleTest", isActive: true })
        this.assertEqual(
            this.privateCall(tabLine, "classes"),
            {
                pined: false,
                active: true,
                pinable: true,
                focused: false
            }
        )
    }

    public async testClickPin () {
        const tabLine = this.mountComponent({ tab: this.tab, moduleName: "ModuleTest" })
        this.privateCall(tabLine.vm, "clickPin")
        await tabLine.vm.$nextTick()
        this.assertTrue(tabLine.emitted("changePin"))
        this.assertHaveLength(tabLine.emitted("changePin"), 1)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertEqual(tabLine.emitted("changePin")[0], [this.tab])
    }

    public testDefaultTabName () {
        const tabLine = this.vueComponent({ tab: this.tab, moduleName: "ModuleTest" })
        this.assertEqual(this.privateCall(tabLine, "tabName"), "mod-ModuleTest-tab-TabTest")
    }

    public testParamTabName () {
        const tabLine = this.vueComponent({ tab: this.tab, moduleName: "ModuleTest", param: true })
        this.assertEqual(this.privateCall(tabLine, "tabName"), "settings")
    }

    public testTabLink () {
        const tabLine = this.vueComponent({ tab: this.tab, moduleName: "ModuleTest" })
        this.assertEqual(this.privateCall(tabLine, "tabLink"), this.tab._links.tab_url)
    }

    public async testEmitChangePinEventWhenKeyP () {
        const tabLine = this.mountComponent({ tab: this.tab, moduleName: "ModuleTest" })
        this.privateCall(tabLine.vm, "keydownTabLine", new KeyboardEvent("keydown", { code: "KeyP" }))
        await tabLine.vm.$nextTick()
        this.assertTrue(tabLine.emitted("changePin"))
        this.assertHaveLength(tabLine.emitted("changePin"), 1)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertEqual(tabLine.emitted("changePin")[0], [this.tab])
    }

    public async testEmitUnsetFocusEventWhenArrowLeft () {
        const tabLine = this.mountComponent({ tab: this.tab, moduleName: "ModuleTest" })
        this.privateCall(tabLine.vm, "keydownTabLine", new KeyboardEvent("keydown", { code: "ArrowLeft" }))
        await tabLine.vm.$nextTick()
        this.assertTrue(tabLine.emitted("unsetFocus"))
    }
}

(new TabLineTest()).launchTests()
