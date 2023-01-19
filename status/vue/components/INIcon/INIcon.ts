/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import INVue from "../INVue/INVue"
import {
    faExternalLinkAlt,
    faFilter,
    faAngleDoubleLeft,
    faAngleDoubleRight,
    faChevronLeft,
    faChevronRight,
    faTimes,
    faSort,
    faSortUp,
    faSortDown,
    faQuestion,
    faTasks,
    faWrench,
    faCogs,
    faInfo,
    faKey,
    faFileMedicalAlt,
    faCheck,
    faPowerOff,
    faArrowUp,
    faExclamation,
    faHourglass
} from "@fortawesome/free-solid-svg-icons"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"

library.add(
    faExternalLinkAlt,
    faFilter,
    faAngleDoubleLeft,
    faAngleDoubleRight,
    faChevronLeft,
    faChevronRight,
    faTimes,
    faSort,
    faSortUp,
    faSortDown,
    faQuestion,
    faTasks,
    faWrench,
    faCogs,
    faInfo,
    faKey,
    faFileMedicalAlt,
    faCheck,
    faPowerOff,
    faArrowUp,
    faExclamation,
    faHourglass
)

/**
 * Wrapper de bouton de l'Install
 */
@Component({ components: { FontAwesomeIcon } })
export default class INIcon extends INVue {
  @Prop({ default: "" })
  private icon!: string
}
