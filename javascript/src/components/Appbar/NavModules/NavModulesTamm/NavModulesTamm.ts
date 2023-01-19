/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import ModuleLine from "@/components/Appbar/ModuleLine/ModuleLine.vue"
import { OxButton, OxTextField, OxIcon } from "oxify"
import ModuleDetail from "@/components/Appbar/ModuleDetail/ModuleDetail.vue"
import NavModulesBase from "@/components/Appbar/NavModules/NavModulesBase/NavModuleBases"
import { Module, Tab, MenuSection } from "@/components/Appbar/Models/AppbarModel"
import TammMenuLink from "@/components/Appbar/NavModules/NavModulesTamm/TammMenuLink/TammMenuLink.vue"
const ModuleDetailMobile = () => import(/* webpackChunkName: "ModuleDetailMobile" */ "@/components/Appbar/ModuleDetail/ModuleDetailMobile/ModuleDetailMobile.vue")

/**
 * NavModulesTamm
 * Module navigation component for TAMM
 */
@Component({
    components: {
        ModuleLine,
        OxTextField,
        OxButton,
        ModuleDetail,
        OxIcon,
        TammMenuLink,
        ModuleDetailMobile
    }
})
export default class NavModulesTamm extends NavModulesBase {
    @Prop()
    private tammMenu!: Array<MenuSection>

    @Prop({ default: false })
    private canSeeModules!: boolean

    protected get classes (): string {
        return super.getClasses(this.expand)
    }

    protected get modulesClasses (): string {
        return super.getModulesClasses(this.displayEmpty)
    }

    protected get displayEmpty (): boolean {
        return super.getDisplayEmpty(this.moduleFilter, this.modules.length)
    }

    private get modules (): Array<Module> {
        if (this.moduleFilter === "") {
            return this.allModules
        }
        return this.allModules.filter((module) => {
            const filter = this.moduleFilter.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
            return (
                this.tr("module-" + module.mod_name + "-court")
                    .toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                    .includes(filter) ||
                module.mod_name
                    .toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                    .includes(filter)
            )
        })
    }

    protected async created (): Promise<void> {
        await super.created()
    }

    protected deactivated () {
        super.deactivated()
    }

    protected resetNavModules () {
        super.resetNavModules()
    }

    protected async loadModules (): Promise<void> {
        await super.loadModules()
    }

    protected clickOutside () {
        super.clickOutside()
    }

    protected async affectDetailledModule ({ module }): Promise<void> {
        await super.affectDetailledModule({ module })
    }

    protected checkActive (moduleName: string): boolean {
        return super.checkActive(moduleName)
    }

    protected filterModules (search: string) {
        super.filterModules(search)
    }

    protected addPin (tab: Tab) {
        super.addPin(tab)
    }

    protected removePin (tab: Tab) {
        super.removePin(tab)
    }

    protected setPinnedTabs (tabs: Array<Tab>) {
        super.setPinnedTabs(tabs)
    }

    protected getModulePosition (): number {
        return super.getModulePosition()
    }

    protected getModuleCategory (moduleName: string): string {
        return super.getModuleCategory(moduleName)
    }

    private accessToFirstModule () {
        if (this.moduleFilter !== "") {
            window.location.href = this.modules[0]._links.module_url
        }
    }

    private resetSearch () {
        this.moduleFilter = ""
    }

    protected expandNav () {
        this.expand = true
    }

    protected collapseNav () {
        (this.$refs.NavModulesTamm as Element).scrollTop = 0
        this.expand = false
    }

    private toggleNav () {
        if (this.expand) {
            this.collapseNav()
        }
        else {
            this.expandNav()
        }
    }

    protected onScroll (e) {
        if (!this.mobile) {
            if (e.target.scrollTop === 0 && this.moduleFilter === "") {
                this.collapseNav()
            }
            else if (!this.expand) {
                this.expandNav()
            }
        }
    }
}
