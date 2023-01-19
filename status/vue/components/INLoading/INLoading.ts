/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import INVue from "../INVue/INVue"

/**
 * Composant affichant un chargement continue
 */
@Component
export default class INLoading extends INVue {
  @Prop({ default: true })
  private fadein!: boolean;

  private get className (): object {
      return {
          "INLoading-fadein": this.fadein
      }
  }
}
