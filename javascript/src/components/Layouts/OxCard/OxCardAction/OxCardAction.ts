/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { OxButton } from "oxify"

/**
 * OxCardAction
 */
@Component({ components: { OxButton } })
export default class OxCardAction extends OxVue {
    @Prop({ default: "" })
    private label!: string

    @Prop({ default: "" })
    private icon!: string

    /**
     * Remontée de l'évenement click
     */
    private click (): void {
        this.$emit("click")
    }
}
