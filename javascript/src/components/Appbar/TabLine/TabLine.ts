/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop, Watch } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { OxIcon } from "oxify"
import { Tab } from "@/components/Appbar/Models/AppbarModel"
import TabBadge from "@/components/Appbar/TabBadge/TabBadge.vue"

/**
 * TabLine
 * Line tab component
 */
@Component({ components: { OxIcon, TabBadge } })
export default class TabLine extends OxVue {
    @Prop()
    private tab!: Tab

    @Prop()
    private moduleName!: string

    @Prop({ default: true })
    private showPin!: boolean

    @Prop({ default: false })
    private pined!: boolean

    @Prop({ default: false })
    private param!: boolean

    @Prop({ default: false })
    private isActive!: boolean

    @Prop({ default: false })
    private isFocus!: boolean

    private get classes (): object {
        return {
            pined: this.pined,
            active: this.isActive,
            pinable: this.showPin,
            focused: this.isFocus
        }
    }

    private clickPin () {
        this.$emit("changePin", this.tab)
    }

    private get tabName (): string {
        if (this.param) {
            return this.tr("settings")
        }
        return this.tr("mod-" + this.moduleName + "-tab-" + this.tab.tab_name)
    }

    private get tabLink (): string {
        return this.tab._links.tab_url
    }

    private mounted () {
        this.changeFocus()
    }

    private keydownTabLine (event: KeyboardEvent) {
        if (event.code === "KeyP" && this.showPin) {
            this.$emit("changePin", this.tab)
        }
        if (event.code === "ArrowLeft") {
            this.$emit("unsetFocus")
        }
    }

    @Watch("isFocus")
    private changeFocus () {
        if (this.$refs.tabLineLink && this.isFocus) {
            const link = this.$refs.tabLineLink as HTMLElement
            link.focus()
            link.scrollIntoView({ behavior: "smooth", block: "center", inline: "center" })
        }
    }
}
