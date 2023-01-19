/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import { Tab } from "@/components/Appbar/Models/AppbarModel"
import TabBadge from "@/components/Appbar/TabBadge/TabBadge.vue"

/**
 * NavTab
 * Navigation tab component
 */
@Component({ components: { TabBadge } })
export default class NavTab extends OxVue {
    @Prop()
    private tab!: Tab

    @Prop({ default: false })
    private tabActive!: boolean

    @Prop({ default: false })
    private parentHover!: boolean

    @Prop({ default: true })
    private isPinned!: boolean

    @Prop({ default: true })
    private showPin!: boolean

    @Prop({ default: false })
    private param!: boolean

    @Prop({ default: false })
    private isDragging!: boolean

    private x = 0
    private y = 0
    private showMenu = false
    private extraClasses = ""

    private get contentClasses (): string {
        return this.tabActive ? "active" : ""
    }

    private get classes (): string {
        return ((this.tabActive && !this.parentHover) ? "active" : "") + " " + this.extraClasses
    }

    private get tabName (): string {
        if (this.param) {
            return this.tr("settings")
        }
        return this.tr("mod-" + OxStoreCore.getters.getCurrentModule.mod_name + "-tab-" + this.tab.tab_name)
    }

    private get tabLink (): string {
        return this.tab._links.tab_url
    }

    private mounted () {
        this.extraClasses = (this.tabActive ? "animated" : "")
        setTimeout(() => {
            this.extraClasses = ""
        }, 250)
    }

    private clickTab (e) {
        if (this.isDragging || this.param) {
            e.preventDefault()
        }
    }

    private show (e) {
        e.preventDefault()
        this.showMenu = false
        this.x = e.clientX
        this.y = e.clientY
        this.$nextTick(() => {
            this.showMenu = true
        })
    }

    private addPinnedClick () {
        this.$emit("addPin", this.tab)
    }

    private removePinnedClick () {
        this.$emit("removePin", this.tab)
    }

    private enterTab () {
        this.$emit("enter")
    }

    private leaveTab () {
        this.$emit("leave")
    }
}
