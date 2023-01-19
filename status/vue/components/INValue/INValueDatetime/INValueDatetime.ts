/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import INVue from "../../INVue/INVue"
import INValueString from "../INValueString/INValueString.vue"

/**
 * Wrapper des champs de saisie de texte de status
 */
@Component({ components: { INValueString } })
export default class INValueDatetime extends INVue {
  @Prop({ default: "" })
  private field!: string
}
