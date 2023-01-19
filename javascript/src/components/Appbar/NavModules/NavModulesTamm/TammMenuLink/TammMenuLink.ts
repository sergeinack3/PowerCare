/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { MenuLink } from "@/components/Appbar/Models/AppbarModel"
import { callJSFunction } from "@/components/Core/OxUtilsCore"

/**
 * TammMenuLink
 * Tamm menu link component
 */
@Component
export default class TammMenuLink extends OxVue {
    @Prop()
    private link!: MenuLink

    private get isLink (): boolean {
        return this.link.href !== null && this.link.href !== ""
    }

    private get target (): "_self" | "_blank" {
        if (this.link.href !== null && this.link.href.includes("http")) {
            return "_blank"
        }
        return "_self"
    }

    private callFunction () {
        if (this.link.action === null) {
            return
        }
        callJSFunction(this.link.action)
    }
}
