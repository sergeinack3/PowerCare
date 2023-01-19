/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop, Watch } from "vue-property-decorator"
import { Module } from "@/components/Appbar/Models/AppbarModel"
import OxModuleIcon from "@/components/Visual/Basics/OxModuleIcon/OxModuleIcon.vue"
import OxVue from "@/components/Core/OxVue"
import { OxIcon } from "oxify"

/**
 * ModuleLine
 * Composant de ligne d'un module
 */
@Component({ components: { OxModuleIcon, OxIcon } })
export default class ModuleLine extends OxVue {
    @Prop()
    private module!: Module

    @Prop({ default: false })
    private isActive!: boolean

    @Prop({ default: false })
    private isFocus!: boolean

    private get classes (): string {
        return (this.isActive ? "active" : "") + (this.isFocus ? " focused" : "")
    }

    private get moduleLink (): string {
        return this.module._links.module_url
    }

    private clickDetail (event, fromKeyboard = false) {
        this.$emit("detailClick", { module: this.module, fromKeyboard: fromKeyboard })
    }

    @Watch("isFocus")
    private changeFocus () {
        if (this.$refs.moduleLineLink && this.isFocus) {
            const link = this.$refs.moduleLineLink as HTMLElement
            link.focus()
            link.scrollIntoView({ behavior: "smooth", block: "center", inline: "center" })
        }
    }
}
