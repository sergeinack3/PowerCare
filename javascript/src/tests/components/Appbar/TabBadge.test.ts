/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { Wrapper } from "@vue/test-utils"
import TabBadge from "@/components/Appbar/TabBadge/TabBadge"
import { Tab, TabBadgeModel } from "@/components/Appbar/Models/AppbarModel"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"

/**
 * Test pour la classe TabBadge
 */
export default class TabBadgeTest extends OxTest {
    protected component = TabBadge

    private tabBadge: TabBadgeModel = {
        tab_name: "tab_name",
        module_name: "module_name",
        counter: 4,
        color: "blue"
    }

    private tab: Tab = {
        tab_name: "tab_name",
        mod_name: "module_name",
        _links: {
            tab_url: "tab_url"
        },
        is_param: false,
        is_config: false,
        is_standard: true,
        pinned_order: null
    }

    private tabWithoutBadge: Tab = {
        tab_name: "tab_name2",
        mod_name: "module_name",
        _links: {
            tab_url: "tab_url"
        },
        is_param: false,
        is_config: false,
        is_standard: true,
        pinned_order: null
    }

    protected beforeAllTests () {
        OxStoreCore.commit("addTabBadge", this.tabBadge)
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): TabBadge {
        return this.mountComponent(props).vm as TabBadge
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<TabBadge> {
        return super.mountComponent(props) as Wrapper<TabBadge>
    }

    public testShowCounterIfExist () {
        const tabBadge = this.vueComponent({ tab: this.tab })
        this.assertTrue(this.privateCall(tabBadge, "showCounter"))
    }

    public testHideCounterIfNotxExist () {
        const tabBadge = this.vueComponent({ tab: this.tabWithoutBadge })
        this.assertFalse(this.privateCall(tabBadge, "showCounter"))
    }

    public testGetTabBadge () {
        const tabBadge = this.vueComponent({ tab: this.tab })
        this.assertEqual(
            this.privateCall(tabBadge, "tabBadge"),
            OxStoreCore.getters.getTabBadge("module_name", "tab_name")
        )
    }

    public testCounterIfExist () {
        const tabBadge = this.vueComponent({ tab: this.tab })
        this.assertEqual(
            this.privateCall(tabBadge, "counter"),
            4
        )
    }

    public testCounterEqualZeroIfNotExist () {
        const tabBadge = this.vueComponent({ tab: this.tabWithoutBadge })
        this.assertEqual(
            this.privateCall(tabBadge, "counter"),
            0
        )
    }

    public testColorIfExist () {
        const tabBadge = this.vueComponent({ tab: this.tab })
        this.assertEqual(
            this.privateCall(tabBadge, "badgeColor"),
            "blue"
        )
    }

    public testColorIfNotExist () {
        const tabBadge = this.vueComponent({ tab: this.tabWithoutBadge })
        this.assertEqual(
            this.privateCall(tabBadge, "badgeColor"),
            ""
        )
    }
}

(new TabBadgeTest()).launchTests()
