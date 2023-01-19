/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import INVue from "../../INVue/INVue"
import INIcon from "../../INIcon/INIcon.vue"

/**
 * Wrapper des champs de saisie de texte de status
 */
@Component({ components: { INIcon } })
export default class INValueBool extends INVue {
  @Prop({ default: "" })
  public field!: boolean

  private get fieldView (): boolean {
      return this.field
  }
}
