/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import INVue from "../../../INVue/INVue"
import INIcon from "../../../INIcon/INIcon.vue"

/**
 * Wrapper des boutons-icones du menu de status
 */
@Component({ components: { INIcon } })
export default class MenuIcon extends INVue {
  @Prop({ default: "question" })
  private icon!: string

  @Prop()
  private label!: string

  @Prop()
  private chapter!: string

  @Prop()
  private selectedChapter!: string

  @Prop({ default: false })
  private compact!: boolean

  @Prop({ default: false })
  private hasFlag!: boolean

  @Prop({ default: false })
  private flagIcon!: string

  @Prop({ default: false })
  private flagState!: "ok"|"nok"

  private get className (): object {
      return {
          "is-selected": this.chapter === this.selectedChapter
      }
  }

  private chapterClick (): void {
      this.$emit("chapterclick", this.chapter)
  }

  private get buttonClassName (): object {
      return {
          "MenuIcon-containerButtonCompact": this.compact
      }
  }

  private get labelClassName (): object {
      return {
          "MenuIcon-labelCompact": this.compact
      }
  }
}
