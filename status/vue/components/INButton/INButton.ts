/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import INVue from "../INVue/INVue"
import INIcon from "../INIcon/INIcon.vue"

/**
 * Wrapper de bouton de l'Install
 */
@Component({ components: { INIcon } })
export default class INButton extends INVue {
  @Prop({ default: "" })
  private label!: string

  @Prop({ default: "" })
  private icon!: string

  @Prop({ default: "" })
  private buttonClass!: string

  /**
   * Click de l'élément
   */
  private click (): void {
      this.$emit("click")
  }

  /**
   * Classes générales
   */
  private get className (): object {
      return {
          tertiary: this.buttonClass === "tertiary",
          notext: this.label === ""
      }
  }
}
