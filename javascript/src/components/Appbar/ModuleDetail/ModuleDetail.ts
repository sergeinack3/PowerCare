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
const Draggable = () => import(/* webpackChunkName: "DraggableDetail" */ "vuedraggable")

/**
 * ModuleDetail
 * Composant de detail d'un module
 */
@Component({ components: { OxModuleIcon, TabLine, OxButton, Draggable } })
export default class ModuleDetail extends OxVue {
    @Prop()
    private module!: Module

    @Prop({ default: -1 })
    private focusTabIndex!: number

    private get showPinnedTabs (): boolean {
        return this.module.pinned_tabs.length > 0
    }

    private get showStandardTabs (): boolean {
        return this.module.standard_tabs.length > 0
    }

    private get showDetail (): boolean {
        return typeof this.module !== "boolean"
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

    private get currentPinnedTabs (): Array<Tab> {
        return this.module.pinned_tabs
    }

    private set currentPinnedTabs (tabs) {
        this.$emit("changePin", tabs)
    }

    private get disableDrag (): boolean {
        return this.module.pinned_tabs.length < 2
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

    private get checkFocusForParam () {
        return (this.focusTabIndex === this.module.pinned_tabs.length + this.module.standard_tabs.length) &&
            this.showParam
    }

    private get checkFocusForConfig () {
        return ((this.focusTabIndex === this.module.pinned_tabs.length + this.module.standard_tabs.length + 1) &&
                this.showParam && this.showConfig) ||
            ((this.focusTabIndex === this.module.pinned_tabs.length + this.module.standard_tabs.length) &&
                !this.showParam && this.showConfig)
    }

    private addPin (tab) {
        this.$emit("addPin", tab)
    }

    private removePin (tab) {
        this.$emit("removePin", tab)
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

    private checkFocus (index: number): boolean {
        return this.focusTabIndex === index
    }

    private checkFocusForStandard (index: number): boolean {
        return this.focusTabIndex === index + this.module.pinned_tabs.length
    }

    private unsetFocus () {
        this.$emit("unsetFocus")
    }
}
