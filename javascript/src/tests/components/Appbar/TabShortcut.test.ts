/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import TabShortcut from "@/components/Appbar/TabShortcut/TabShortcut"

/**
 * Test pour la classe TabShortcut
 */
export default class TabShortcutTest extends OxTest {
    protected component = TabShortcut

    private tab = {
        tab_name: "TabTest",
        mod_name: "ModuleTest",
        _links: {
            tab_url: "tab-url"
        },
        is_param: false
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): TabShortcut {
        return this.mountComponent(props).vm as TabShortcut
    }

    public testGetModuleName () {
        const tabShortcut = this.vueComponent({ tab: this.tab, moduleCategory: "category_test" })
        this.assertEqual(this.privateCall(tabShortcut, "moduleName"), "module-ModuleTest-court")
    }

    public testGetClassicTabName () {
        const tabShortcut = this.vueComponent({ tab: this.tab, moduleCategory: "category_test" })
        this.assertEqual(this.privateCall(tabShortcut, "tabName"), "mod-ModuleTest-tab-TabTest")
    }

    public testGetParamTabName () {
        const paramTab = { ...this.tab }
        paramTab.is_param = true
        const tabShortcut = this.vueComponent({ tab: paramTab, moduleCategory: "category_test" })
        this.assertEqual(this.privateCall(tabShortcut, "tabName"), "settings")
    }

    public testGetTabLink () {
        const tabShortcut = this.vueComponent({ tab: this.tab, moduleCategory: "category_test" })
        this.assertEqual(this.privateCall(tabShortcut, "tabLink"), "tab-url")
    }
}

(new TabShortcutTest()).launchTests()
