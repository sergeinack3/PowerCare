/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxVue from "@/components/Core/OxVue"
import { Prop } from "vue-property-decorator"
import { AppbarProp, Module, Tab } from "@/components/Appbar/Models/AppbarModel"
import ModuleSerializer from "@/components/Appbar/Serializer/ModuleSerializer"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import { appbarPropTransformer } from "@/components/Appbar/Serializer/DataSerializer"

const AppbarProvider = () => import(/* webpackChunkName: "NavModulesAppbarProvider" */ "@/components/Appbar/Providers/AppbarProvider")

export default class NavModulesBase extends OxVue {
    @Prop()
    protected defaultModulesData!: AppbarProp[]

    @Prop({ default: true })
    protected useProvider!: boolean

    @Prop({ default: false })
    protected mobile!: boolean

    @Prop({ default: false })
    protected showHomeLink!: boolean

    @Prop()
    private homeLink!: string

    protected allModules: Array<Module> = []
    protected expand = false
    protected showDetail = false
    protected detailModule: Module | boolean = false
    protected moduleFilter = ""
    protected showModules = false
    protected provider

    protected async created () {
        if (this.useProvider) {
            // eslint-disable-next-line new-cap
            this.provider = await new (await AppbarProvider()).default()
        }
        this.loadModules()
    }

    protected deactivated () {
        this.resetNavModules()
    }

    protected resetNavModules () {
        this.showDetail = false
        this.detailModule = false
        this.filterModules("")
    }

    protected loadModules () {
        const defaultModulesData = this.defaultModulesData.map((module) => {
            return appbarPropTransformer(module)
        })

        defaultModulesData.forEach((module) => {
            this.allModules.push(ModuleSerializer.serialize(module))
        })

        if (Array.isArray(this.allModules) && this.allModules.length < 1) {
            console.warn("Appbar warn: No module available")
        }

        this.showModules = true
    }

    protected clickOutside () {
        this.$emit("input", false)
    }

    protected async affectDetailledModule ({ module }) {
        this.showDetail = true
        // Display skeleton
        this.detailModule = false

        if (module.mod_name === OxStoreCore.getters.getCurrentModule.mod_name) {
            this.detailModule = OxStoreCore.getters.getCurrentModule
        }
        else {
            if (module.tabs_order.length === 0) {
                this.detailModule = ModuleSerializer.addTabsToModule(
                    module,
                    await this.provider.getModuleTabs(module._links.tabs) as Array<Tab>
                )
            }
            else {
                this.detailModule = module
            }
        }
    }

    protected checkActive (moduleName: string): boolean {
        return (typeof this.detailModule !== "boolean" && this.detailModule.mod_name === moduleName)
    }

    protected filterModules (search: string) {
        this.moduleFilter = search
    }

    protected addPin (tab: Tab) {
        const module = this.allModules[this.getModulePosition()]
        if (module.mod_name === OxStoreCore.getters.getCurrentModule.mod_name) {
            OxStoreCore.dispatch("pinTab", { tab: tab, provider: this.provider })
        }
        else {
            module.pinned_tabs.push(tab)
            module.standard_tabs = module.standard_tabs.filter((standardTab) => {
                return standardTab.tab_name !== tab.tab_name
            })
            this.provider.putPinnedTabs(module)
        }
    }

    protected removePin (tab: Tab) {
        const module = this.allModules[this.getModulePosition()]
        if (module.mod_name === OxStoreCore.getters.getCurrentModule.mod_name) {
            OxStoreCore.dispatch("unpinTab", { tab: tab, provider: this.provider })
        }
        else {
            module.standard_tabs.push(tab)
            module.pinned_tabs = module.pinned_tabs.filter((pinnedTab) => {
                return pinnedTab.tab_name !== tab.tab_name
            })
            this.provider.putPinnedTabs(module)
        }
    }

    protected setPinnedTabs (tabs: Array<Tab>) {
        const module = this.allModules[this.getModulePosition()]
        if (module.mod_name === OxStoreCore.getters.getCurrentModule.mod_name) {
            OxStoreCore.dispatch("setPinnedTabs", { tabs: tabs, provider: this.provider })
        }
        else {
            module.pinned_tabs = tabs
            this.provider.putPinnedTabs(module)
        }
    }

    protected getModulePosition (): number {
        return this.allModules.findIndex((module) => {
            return module.mod_name === (this.detailModule as Module).mod_name
        })
    }

    protected getClasses (expand: boolean): string {
        return expand ? "expand" : ""
    }

    protected getModulesClasses (displayEmpty: boolean): string {
        return displayEmpty ? "empty" : ""
    }

    protected getModuleCategory (moduleName: string): string {
        if (!Array.isArray(this.allModules)) {
            return ""
        }
        const module = this.allModules.find((module) => {
            return module.mod_name === moduleName
        })
        return module ? module.mod_category : ""
    }

    protected getDisplayEmpty (moduleFilter: string, moudulesLength: number): boolean {
        return moduleFilter !== "" && moudulesLength === 0
    }
}
