/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { Tab } from "@/components/Appbar/Models/AppbarModel"
import TabLine from "@/components/Appbar/TabLine/TabLine.vue"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"

/**
 * TabSelector
 * Tab selector component (dropdown)
 */
@Component({ components: { TabLine } })
export default class TabSelector extends OxVue {
    @Prop()
    private tabs!: Array<Tab>

    @Prop({ default: false })
    private configure!: boolean

    @Prop({ default: false })
    private param!: boolean

    @Prop()
    private value!: boolean

    private get moduleName (): string {
        return OxStoreCore.getters.getCurrentModule.mod_name
    }

    private get paramTab (): Tab {
        return { tab_name: "", _links: { tab_url: OxStoreCore.getters.getCurrentModule.param_tabs[0]._links.tab_url } }
    }

    private get configTab (): Tab {
        return {
            tab_name: OxStoreCore.getters.getCurrentModule.configure_tab[0].tab_name,
            _links: { tab_url: OxStoreCore.getters.getCurrentModule.configure_tab[0]._links.tab_url }
        }
    }

    private get showDivider (): boolean {
        return (this.param || this.configure) && this.tabs.length > 0
    }

    private pinTab (tab: Tab) {
        this.$emit("addPin", tab)
    }

    private checkTabActive (tabName: string, context?: string): boolean {
        if (!context) {
            return tabName === OxStoreCore.getters.getTabActive
        }
        if (context === "param") {
            return OxStoreCore.getters.currentTabIsParam
        }
        if (context === "config") {
            return OxStoreCore.getters.currentTabIsConfig
        }
        return false
    }
}
