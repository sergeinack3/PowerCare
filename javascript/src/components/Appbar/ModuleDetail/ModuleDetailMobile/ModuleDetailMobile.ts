/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { Module, Tab } from "@/components/Appbar/Models/AppbarModel"
import OxModuleIcon from "@/components/Visual/Basics/OxModuleIcon/OxModuleIcon.vue"
import TabLine from "@/components/Appbar/TabLine/TabLine.vue"
import { OxButton } from "oxify"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
const AppbarProvider = () => import(/* webpackChunkName: "ModuleDetailMobileAppbarProvider" */ "@/components/Appbar/Providers/AppbarProvider")

/**
 * ModuleDetailMobile
 * Composant de detail d'un module
 */
@Component({ components: { OxModuleIcon, TabLine, OxButton } })
export default class ModuleDetailMobile extends OxVue {
    @Prop()
    private module!: Module

    @Prop({ default: false })
    protected showBack!: boolean

    @Prop({ default: true })
    protected useProvider!: boolean

    protected provider

    private get showStandardTabs (): boolean {
        return this.module.standard_tabs.length > 0
    }

    private get showParam (): boolean {
        return this.module.param_tabs.length > 0
    }

    private get showConfig (): boolean {
        return this.module.configure_tab.length > 0
    }

    private get showFooter (): boolean {
        return this.showParam || this.showConfig
    }

    private get paramTab (): Tab {
        return { tab_name: "", _links: { tab_url: this.module.param_tabs[0]._links.tab_url } }
    }

    private get configTab (): Tab {
        return {
            tab_name: this.module.configure_tab[0].tab_name,
            _links: { tab_url: this.module.configure_tab[0]._links.tab_url }
        }
    }

    private get standardTabs (): Array<Tab> {
        return this.module.standard_tabs.slice().sort((a, b) => {
            return this.module.tabs_order.indexOf(a.tab_name) - this.module.tabs_order.indexOf(b.tab_name)
        })
    }

    protected async created () {
        if (this.useProvider) {
            // eslint-disable-next-line new-cap
            this.provider = await new (await AppbarProvider()).default()
        }
    }

    private addPin (tab) {
        if (this.module.mod_name === OxStoreCore.getters.getCurrentModule.mod_name) {
            OxStoreCore.dispatch("pinTab", { tab: tab, provider: this.provider })
        }
        else {
            this.module.pinned_tabs.push(tab)
            this.module.standard_tabs = this.module.standard_tabs.filter((standardTab) => {
                return standardTab.tab_name !== tab.tab_name
            })
            this.provider.putPinnedTabs(module)
        }
    }

    private removePin (tab) {
        if (this.module.mod_name === OxStoreCore.getters.getCurrentModule.mod_name) {
            OxStoreCore.dispatch("unpinTab", { tab: tab, provider: this.provider })
        }
        else {
            this.module.standard_tabs.push(tab)
            this.module.pinned_tabs = this.module.pinned_tabs.filter((pinnedTab) => {
                return pinnedTab.tab_name !== tab.tab_name
            })
            this.provider.putPinnedTabs(module)
        }
    }

    private checkTabActive (tabName: string, context?: string): boolean {
        if (this.module.mod_name !== OxStoreCore.getters.getCurrentModule.mod_name) {
            return false
        }
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

    private redirectToModule () {
        window.location.href = this.module._links.module_url
    }

    private back (): void {
        this.$emit("close")
    }
}
