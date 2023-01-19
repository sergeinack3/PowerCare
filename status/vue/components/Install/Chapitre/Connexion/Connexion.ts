/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component } from "vue-property-decorator"
import INButton from "../../../INButton/INButton.vue"
import INVue from "../../../INVue/INVue"
import INCard from "../../../INCard/INCard.vue"
import INField from "../../../INField/INField.vue"
import ConnexionProvider from "../../../INProvider/ConnexionProvider"
import Api from "../../../INApi/Api"
import INLoading from "../../../INLoading/INLoading.vue"

/**
 * Gestion de la page de connexion de status
 */
@Component({ components: { INButton, INCard, INField, INLoading } })
export default class Connexion extends INVue {
  private login = ""
  private password = ""
  private connecting = false
  private connectMessage = ""
  private connectStatus!: number
  private connectError = false

  private loginChange (login: string): void {
      this.login = login
  }

  private passwordChange (password: string): void {
      this.password = password
  }

  private genCredentials (): string {
      return btoa(this.login + ":" + this.password)
  }

  private async connect (): Promise<void> {
      this.connecting = true
      this.connectError = false
      Api.commit("setCredential", this.genCredentials())
      const response = await new ConnexionProvider().getRaw()
      this.connecting = false
      if (!response || !response.data) {
          this.connectMessage = response ? response.message : ""
          this.connectStatus = response ? response.status : null
          if (this.connectStatus === 401) {
              this.connectMessage = this.tr("Connexion-errorCredentialMessage")
          }
          this.connectError = true
          return
      }
      this.$emit("connect", { credentials: this.genCredentials() })
  }
}
