/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import Vue from "vue"
import Vuex from "vuex"
Vue.use(Vuex)

/**
 * Singleton général de status
 */
export default new Vuex.Store({
  state: {
    // Credential de la connexion courante
    credential: "",
    endPoint: "."
  },
  mutations: {
    setCredential: (state: {credential: string}, credential: string) => { state.credential = credential },
    setEndPoint: (state: {endPoint: string}, url: string) => { state.endPoint = url }
  }
})
