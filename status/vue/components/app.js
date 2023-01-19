/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import Vue from "vue"
import Install from "./Install/Install.vue"
import { lang } from "./INLocales/INLocales"

// Initialize the Vue Components
document.addEventListener(
    "readystatechange",
    () => {
        if (document.readyState !== "complete") {
            // Escaping while the document is not complete
            return false
        }
        let endPoint = document.head.querySelector("[name='ox-endpoint']")
        endPoint = endPoint ? endPoint.content : "."

        new Vue({
          template: "<Install end-point='" + endPoint + "'/>",
          components: { Install },
          lang
        }).$mount("#app_install")
    }
)
