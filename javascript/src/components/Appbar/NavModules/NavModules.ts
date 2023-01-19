/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import { AppbarProp, Module, Tab } from "@/components/Appbar/Models/AppbarModel"
import TabShortcut from "@/components/Appbar/TabShortcut/TabShortcut.vue"
import ModuleLine from "@/components/Appbar/ModuleLine/ModuleLine.vue"
import { OxButton, OxTextField } from "oxify"
import ModuleDetail from "@/components/Appbar/ModuleDetail/ModuleDetail.vue"
import NavModulesBase from "@/components/Appbar/NavModules/NavModulesBase/NavModuleBases"
import { appbarPropTransformer } from "@/components/Appbar/Serializer/DataSerializer"
const ModuleDetailMobile = () => import(/* webpackChunkName: "ModuleDetailMobile" */ "@/components/Appbar/ModuleDetail/ModuleDetailMobile/ModuleDetailMobile.vue")

/**
 * NavModules
 * Module navigation component
 */
@Component({
    components: {
        TabShortcut,
        ModuleLine,
        OxTextField,
        OxButton,
        ModuleDetail,
        ModuleDetailMobile
    }
})
export default class NavModules extends NavModulesBase {
    @Prop()
    private tabShortcuts!: AppbarProp[]

    private tabsFav: Array<Tab> = []
    // Keyboard navigation controls
    private saveFocusModuleIndex = -1
    private focusModuleIndex = -1
    private focusTabIndex = -1
    private tabLimit = -1

    private get showFavTabs (): boolean {
        return this.tabsFav.length > 0
    }

    private get moreModules (): boolean {
        return this.allModules.length > 6
    }

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

    protected async created () {
        await super.created()
        if (this.tabShortcuts && this.tabShortcuts.length > 0) {
            this.tabsFav = this.tabShortcuts.map((shortcut) => {
                return appbarPropTransformer(shortcut)
            }) as unknown as Array<Tab>
        }
    }

    private activated () {
        this.setFocusOnSearchField()
        window.addEventListener("keydown", this.keyboardShortcutAccess)
    }

    protected deactivated () {
        super.deactivated()
    }

    protected resetNavModules () {
        super.resetNavModules()
        this.focusModuleIndex = -1
        this.focusTabIndex = -1
        window.removeEventListener("keydown", this.keyboardShortcutAccess)
    }

    protected async loadModules () {
        await super.loadModules()
        this.setFocusOnSearchField()
    }

    protected clickOutside () {
        super.clickOutside()
    }

    private expandNav () {
        this.expand = true
    }

    private collapseNav () {
        this.expand = false
    }

    private onScroll (e) {
        if (e.target.scrollTop === 0 && this.expand) {
            const atBottom = e.target.scrollHeight - Math.round(e.target.scrollTop) === e.target.clientHeight
            if (atBottom) {
                return
            }
            this.collapseNav()
        }
        else if (!this.expand) {
            this.expandNav()
        }
    }

    private keyboardShortcutAccess (event: KeyboardEvent) {
        if (event.altKey) {
            event.preventDefault()
            event.stopPropagation()
            const elements = this.$refs.shortcuts as Vue[]
            let position = -1
            switch (event.code) {
            case "Digit1":
                position = 0
                break
            case "Digit2":
                position = 1
                break
            case "Digit3":
                position = 2
                break
            case "Digit4":
                position = 3
                break
            default:
                break
            }
            if (position !== -1 && Array.isArray(elements) && elements.length > position) {
                (elements[position].$el as HTMLElement).click()
            }
        }
        if (event.code === "ArrowUp") {
            event.preventDefault()
            event.stopPropagation()
            if (this.focusTabIndex !== -1) {
                this.focusTabIndex = (this.focusTabIndex <= 0) ? this.focusTabIndex : this.focusTabIndex - 1
            }
            else {
                if (this.focusModuleIndex <= 0) {
                    this.focusModuleIndex = -1
                    this.setFocusOnSearchField()
                }
                else {
                    this.focusModuleIndex--
                }
            }
        }
        if (event.code === "ArrowDown") {
            event.preventDefault()
            event.stopPropagation()
            if (this.focusTabIndex !== -1 && this.detailModule) {
                this.focusTabIndex = (this.focusTabIndex < this.tabLimit)
                    ? this.focusTabIndex + 1
                    : this.focusTabIndex
            }
            else {
                this.focusModuleIndex = (this.focusModuleIndex < (this.modules.length - 1))
                    ? this.focusModuleIndex + 1
                    : this.focusModuleIndex
            }
        }
    }

    protected async affectDetailledModule ({ module, fromKeyboard }) {
        await super.affectDetailledModule({ module })
        if ((this.detailModule as Module).standard_tabs.length > 0 || ((this.detailModule as Module).pinned_tabs.length > 0)) {
            this.focusTabIndex = fromKeyboard ? 0 : -1
            this.saveFocusModuleIndex = this.focusModuleIndex
            this.focusModuleIndex = -1
            this.tabLimit = (this.detailModule as Module).pinned_tabs.length +
                (this.detailModule as Module).standard_tabs.length +
                ((this.detailModule as Module).configure_tab.length > 0 ? 1 : 0) +
                ((this.detailModule as Module).param_tabs.length > 0 ? 1 : 0) - 1
        }
    }

    protected checkActive (moduleName: string): boolean {
        return super.checkActive(moduleName)
    }

    private checkFocus (index: number): boolean {
        return this.focusModuleIndex === index
    }

    protected filterModules (search: string) {
        this.focusModuleIndex = -1
        if (search !== "") {
            this.expandNav()
        }
        else if (!this.detailModule) {
            this.collapseNav()
        }
        super.filterModules(search)
    }

    private resetSearch () {
        this.expand = false
        this.moduleFilter = ""
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

    private setFocusOnSearchField () {
        if (this.moreModules && !this.mobile) {
            this.$nextTick(() => {
                // eslint-disable-next-line @typescript-eslint/ban-ts-comment
                // @ts-ignore
                const el = this.$refs.searchField.$el.querySelector("input")
                if (el) {
                    el.focus()
                }
            })
        }
    }

    protected getModuleCategory (moduleName: string): string {
        return super.getModuleCategory(moduleName)
    }

    private unsetFocusDetail () {
        this.focusTabIndex = -1
        this.focusModuleIndex = this.saveFocusModuleIndex
        this.saveFocusModuleIndex = -1
    }

    private accessToFirstModule () {
        if (this.moduleFilter !== "") {
            window.location.href = this.modules[0]._links.module_url
        }
    }
}
