/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"

/**
 * OxLayout
 */
@Component
export default class OxLayoutCore extends OxVue {
    @Prop({ default: 0 })
    protected asideWidth?: number

    /**
     * Style dédié au panneau latéral
     */
    protected get asideStyle () {
        if (!this.asideWidth) {
            return {}
        }
        return {
            width: this.asideWidth + "px"
        }
    }
}
