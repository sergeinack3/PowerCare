/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import INVue from "../INVue/INVue"
import INValue from "../INValue/INValue.vue"

/**
 * Element de tuile affichant un template de base d'informations
 */
@Component({ components: { INValue } })
export default class INLineElement extends INVue {
  @Prop({ default: "" })
  private label!: string

  @Prop({ default: "" })
  private value!: string
}
