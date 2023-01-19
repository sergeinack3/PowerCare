/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import OxVueWrapContent from "@/components/Core/OxVueWrap/OxVueWrapContent/OxVueWrapContent.vue"
import vuetify from "@/components/Core/OxVuetifyCore"

/**
 * OxVueWrap
 *
 * Wrapper général de l'application Vue.
 */
/* eslint-disable  @typescript-eslint/ban-ts-comment */
// @ts-ignore
@Component({ components: { OxVueWrapContent }, vuetify: vuetify })
export default class OxVueWrap extends OxVue {
}
