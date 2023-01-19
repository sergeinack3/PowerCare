/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { OxIcon } from "oxify"

/**
 * OxCardHeader
 */
@Component({ components: { OxIcon } })
export default class OxCardHeader extends OxVue {
    @Prop({ default: "" })
    private label!: string

    @Prop({ default: "" })
    private icon!: string
}
