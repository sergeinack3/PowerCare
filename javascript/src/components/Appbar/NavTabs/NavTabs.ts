/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import NavTab from "@/components/Appbar/NavTabs/NavTab/NavTab.vue"
import { OxIcon } from "oxify"
import { Module, Tab } from "@/components/Appbar/Models/AppbarModel"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import AppbarProvider from "@/components/Appbar/Providers/AppbarProvider"
import Draggable from "vuedraggable"

const TabSelector = () => import(/* webpackChunkName: "TabSelector" */ "@/components/Appbar/TabSelector/TabSelector.vue")

/**
 * NavTabs
 * Composant de navigation dans les onglets d'un module
 */
@Component({ components: { NavTab, OxIcon, TabSelector, Draggable } })
export default class NavTabs extends OxVue {
    private showSelector = false
    private hover = false
    private drag = false

    private get currentModule (): Module {
        return OxStoreCore.getters.getCurrentModule
    }

    private get tabActive (): string {
        return OxStoreCore.getters.getTabActive
    }

    private get currentTabIsParam (): boolean {
        return OxStoreCore.getters.currentTabIsParam
    }

    private get currentTabIsConfig (): boolean {
        return OxStoreCore.getters.currentTabIsConfig
    }

    private get showMoreTabs (): boolean {
        return this.currentStandardTabs.length > 0 || this.configureTab || this.paramTab
    }

    private get showCurrentStandardTab (): boolean {
        const index = this.currentModule.pinned_tabs.findIndex((tab: Tab) => {
            return tab.tab_name === this.tabActive
        })
        return index === -1 && this.tabActive !== ""
    }

    private get tabActiveIsPinnable (): boolean {
        const index = this.currentModule.standard_tabs.findIndex((tab: Tab) => {
            return tab.tab_name === this.tabActive
        })
        return index !== -1
    }

    private get currentStandardTabs (): Array<Tab> {
        return OxStoreCore.getters.getStandardTabs
    }

    private get showPinnedTabs (): boolean {
        return this.currentPinnedTabs.length > 0
    }

    private get currentPinnedTabs (): Array<Tab> {
        return OxStoreCore.getters.getPinnedTabs
    }

    private set currentPinnedTabs (tabs) {
        const provider = new AppbarProvider()
        OxStoreCore.dispatch("setPinnedTabs", { tabs, provider })
    }

    private get disableDrag (): boolean {
        return this.currentPinnedTabs.length < 2
    }

    private get moreTabsTabClass (): object {
        return {
            active: this.showSelector,
            standard: this.showCurrentStandardTab
        }
    }

    private get standardTabClasses (): object {
        return {
            standard: true,
            lonely: !this.showPinnedTabs,
            round: !this.showMoreTabs
        }
    }

    private get configureTab (): boolean {
        return this.currentModule.configure_tab.length > 0 && !this.currentTabIsConfig
    }

    private get paramTab (): boolean {
        return this.currentModule.param_tabs.length > 0 && !this.currentTabIsParam
    }

    private isActive (tabName: string): boolean {
        return tabName === this.tabActive
    }

    private moreClick () {
        this.showSelector = !this.showSelector
    }

    private clickOutside () {
        this.showSelector = false
    }

    private addPin (tab: Tab) {
        const provider = new AppbarProvider()
        OxStoreCore.dispatch("pinTab", { tab, provider })
    }

    private removePin (tab: Tab) {
        const provider = new AppbarProvider()
        OxStoreCore.dispatch("unpinTab", { tab, provider })
    }

    private enterTabs () {
        this.hover = true
    }

    private leaveTabs () {
        this.hover = false
    }

    private async endDrag () {
        // Prevent click at the end of drag
        await new Promise(resolve => setTimeout(resolve, 250))
        this.drag = false
    }

    private startDrag () {
        this.drag = true
    }

    private includeGroup () {
        return [document.querySelector(".NavTabs-standard")]
    }
}
