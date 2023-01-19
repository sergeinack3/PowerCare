/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import { Placeholder } from "@/components/Appbar/Models/AppbarModel"
import OxVue from "@/components/Core/OxVue"
import { OxIcon, OxBadge } from "oxify"
import { callJSFunction } from "@/components/Core/OxUtilsCore"

const PlaceholderShortcut = () => import(/* webpackChunkName: "PlaceholderShortcut" */ "@/components/Appbar/PlaceholderShortcut/PlaceholderShortcut.vue")

/**
 * PlaceholderLine
 * line placeholder component
 */
@Component({ components: { OxIcon, OxBadge, PlaceholderShortcut } })
export default class PlaceholderLine extends OxVue {
    @Prop()
    private placeholder!: Placeholder

    private click () {
        if (this.placeholder.action !== "") {
            callJSFunction(this.placeholder.action, this.placeholder.action_args)
        }
    }
}
