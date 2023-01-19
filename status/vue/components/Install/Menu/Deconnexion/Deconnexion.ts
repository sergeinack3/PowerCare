/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import INVue from "../../../INVue/INVue"
import INIcon from "../../../INIcon/INIcon.vue"

/**
 * Wrapper du bouton de déconnexion de status
 */
@Component({ components: { INIcon } })
export default class Deconnexion extends INVue {
  @Prop({ default: "power-off" })
  private icon!: string

  @Prop({ default: "" })
  private lib!: string

  private disconnect (): void {
      this.$emit("disconnect")
  }
}
