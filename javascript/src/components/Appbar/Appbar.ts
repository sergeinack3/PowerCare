/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { OxIcon } from "oxify"
import NavTabs from "@/components/Appbar/NavTabs/NavTabs.vue"
import {
    Group,
    Module,
    UserInfo,
    Function,
    InfoMaj,
    Placeholder,
    MenuSection,
    TabBadgeModel,
    AppbarProp
} from "@/components/Appbar/Models/AppbarModel"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import { appbarPropTransformer } from "@/components/Appbar/Serializer/DataSerializer"
import OxNotify from "@/core/components/OxNotify/OxNotify.vue"
const NavModules = () => import(/* webpackChunkName: "NavModules" */ "@/components/Appbar/NavModules/NavModules.vue")
const NavModulesTamm = () => import(/* webpackChunkName: "NavModulesTamm" */ "@/components/Appbar/NavModules/NavModulesTamm/NavModulesTamm.vue")
const UserAccount = () => import(/* webpackChunkName: "UserAccount" */ "@/components/Appbar/UserAccount/UserAccount.vue")
const GroupSelector = () => import(/* webpackChunkName: "GroupSelector" */ "@/components/Appbar/GroupSelector/GroupSelector.vue")
const PlaceholderShortcut = () => import(/* webpackChunkName: "PlaceholderShortcut" */ "@/components/Appbar/PlaceholderShortcut/PlaceholderShortcut.vue")
const PlaceholderLine = () => import(/* webpackChunkName: "PlaceholderLine" */ "@/components/Appbar/PlaceholderLine/PlaceholderLine.vue")
const ModuleDetailMobile = () => import(/* webpackChunkName: "ModuleDetail" */ "@/components/Appbar/ModuleDetail/ModuleDetailMobile/ModuleDetailMobile.vue")

/**
 * Appbar
 * Composant Appbar de l'application
 */
@Component({
    components: {
        OxIcon,
        NavModules,
        NavModulesTamm,
        NavTabs,
        UserAccount,
        GroupSelector,
        PlaceholderShortcut,
        PlaceholderLine,
        ModuleDetailMobile,
        OxNotify
    }
})
export default class Appbar extends OxVue {
    @Prop()
    private currentModule!: AppbarProp

    @Prop()
    private defaultModules!: AppbarProp[]

    @Prop({ default: () => [] })
    private functionsData!: AppbarProp[]

    @Prop()
    private groupData!: AppbarProp

    @Prop()
    private infoMaj!: InfoMaj

    @Prop({ default: () => [] })
    private moduleTabs!: AppbarProp[]

    @Prop({ default: () => [] })
    private placeholdersData!: AppbarProp[]

    @Prop({ default: "" })
    private tabActive!: string

    @Prop()
    private tabShortcutsData!: AppbarProp[]

    @Prop()
    private user!: AppbarProp

    @Prop()
    private dateNow!: string

    @Prop({ default: true })
    private useVuetify!: boolean

    @Prop({ default: false })
    private isTamm!: boolean

    @Prop({ default: false })
    private isQualif!: boolean

    @Prop()
    private tammMenu!: Array<MenuSection>

    private static readonly SCREEN_SIZE_XXL = 1300
    private static readonly SCREEN_SIZE_XL = 1000
    private static readonly SCREEN_SIZE_L = 900
    private static readonly SCREEN_SIZE_M = 600

    private showNavModule = false
    private showAccount = false
    private showGroups = false
    private showTabs = false
    private showModuleDetail = false
    private userInfo: UserInfo | {} = {}
    private group: Group | {} = {}
    private currentFunction: Function | {} = {}
    private functions: Array<Function> = []
    private homeLink = ""
    private placeholders: Array<Placeholder> = []
    private showPlaceholders = false
    private screenWidth = 0

    private get isXXL (): boolean {
        return this.screenWidth >= Appbar.SCREEN_SIZE_XXL
    }

    private get isXL (): boolean {
        return this.screenWidth >= Appbar.SCREEN_SIZE_XL
    }

    private get isL (): boolean {
        return this.screenWidth >= Appbar.SCREEN_SIZE_L
    }

    private get isM (): boolean {
        return this.screenWidth >= Appbar.SCREEN_SIZE_M
    }

    private get isS (): boolean {
        return !this.isM
    }

    private get uniquePlaceholder (): boolean {
        return this.placeholders.length <= 1
    }

    private get placeholderCompact (): Placeholder {
        return {
            _id: "",
            action: "",
            action_args: [],
            counter: "",
            icon: this.placeholders.length,
            init_action: "",
            label: this.tr("Appbar-Shortcut-access")
        }
    }

    private get isPlaceholderCounter (): boolean {
        if (!Array.isArray(this.placeholders)) {
            return false
        }

        return this.placeholders.some((placeholder) => {
            return placeholder.counter !== null && placeholder.counter !== "" && placeholder.counter.toString() !== "0"
        })
    }

    private get moduleClass (): string {
        return this.showNavModule ? "active" : ""
    }

    private get accountClass (): string {
        return this.showAccount ? "active" : ""
    }

    private get groupClass (): string {
        return this.showGroups ? "active" : ""
    }

    private get module (): Module {
        return OxStoreCore.getters.getCurrentModule
    }

    private get tabName (): string {
        return this.tr("mod-" + OxStoreCore.getters.getCurrentModule.mod_name + "-tab-" + OxStoreCore.getters.getTabActive)
    }

    private async created () {
        this.loadApiData()
        this.homeLink = this.getHomeLink()
        this.currentFunction = this.getCurrentFunction()
        if (this.useVuetify) {
            // eslint-disable-next-line @typescript-eslint/ban-ts-comment
            // @ts-ignore
            this.$vuetify.theme.dark = (this.userInfo as UserInfo)._dark_mode
        }
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        this.currentModule.datas.tabs = this.moduleTabs.map((tab) => {
            return appbarPropTransformer(tab)
        })
        OxStoreCore.commit("setTabActive", this.tabActive)
        await OxStoreCore.dispatch("setCurrentModule", this.currentModule)
        this.showTabs = true

        // Keyboard navigation
        window.addEventListener("keydown", (event) => {
            if (event.altKey && event.code === "KeyQ") {
                event.preventDefault()
                event.stopPropagation()
                this.displayNavModule()
            }
        })

        // Badge Event
        window.addEventListener("badge", this.addBadge)

        // Responsive layout
        window.addEventListener("resize", () => {
            this.screenWidth = window.innerWidth
        })
        this.screenWidth = window.innerWidth

        if (this.useVuetify) {
            // eslint-disable-next-line @typescript-eslint/ban-ts-comment
            // @ts-ignore
            document.getElementById("main").classList.add("me-fullpage")
        }
    }

    private loadApiData () {
        this.group = appbarPropTransformer(this.groupData)
        this.userInfo = appbarPropTransformer(this.user)
        this.functions = this.functionsData.map((_function) => {
            return appbarPropTransformer(_function)
        }) as unknown as Function[]
        this.placeholders = this.placeholdersData.map((placeholder) => {
            return appbarPropTransformer(placeholder)
        }) as unknown as Placeholder[]
    }

    private getHomeLink (): string {
        return (this.userInfo as UserInfo)._links.default
    }

    private getCurrentFunction (): Function {
        const functionId = new URL(window.location.href).searchParams.get("f")
        if (functionId) {
            return this.functions.find((func: Function) => {
                return func._id.toString() === functionId.toString()
            }) as Function
        }
        return this.functions.find((func: Function) => {
            return func.is_main
        }) as Function
    }

    private displayNavModule () {
        this.showNavModule = !this.showNavModule
    }

    private displayAccount () {
        this.showAccount = true
    }

    /**
     * Toggle groups display
     * @private
     */
    private displayGroups () {
        this.showGroups = !this.showGroups
    }

    private clickOutside () {
        this.showNavModule = false
    }

    private openPlaceholders () {
        this.showPlaceholders = true
    }

    private closePlaceholders () {
        this.showPlaceholders = false
    }

    private displayTabs () {
        this.showModuleDetail = true
    }

    private closeTabs () {
        this.showModuleDetail = false
    }

    private addBadge (event) {
        const tabBadge = event.detail as TabBadgeModel
        OxStoreCore.dispatch("updateTabBadge", tabBadge)
    }
}
