/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { createPinia, PiniaVuePlugin } from "pinia"
import Vue from "vue"

Vue.use(PiniaVuePlugin)
export default createPinia()
