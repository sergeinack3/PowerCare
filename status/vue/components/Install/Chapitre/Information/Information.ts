/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component } from "vue-property-decorator"
import Chapitre from "../Chapitre"
import INLoading from "../../../INLoading/INLoading.vue"
import InformationProvider from "../../../INProvider/InformationProvider"

/**
 * Gestion de la page d'informations de status
 */
@Component({ components: { INLoading } })
export default class Information extends Chapitre {
  private loaded = false

  private informationDOM = ""

  public async load (): Promise<void> {
      this.loaded = false
      const informationDOM = await new InformationProvider().getDOM()
      this.informationDOM = informationDOM.substr(0, informationDOM.indexOf("<style")) +
      informationDOM.substr(informationDOM.indexOf("</style>") + 8)
      this.loaded = true
  }
}
