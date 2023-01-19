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
 * ObjectCardEdit
 */
@Component({ components: { OxIcon } })
export default class ObjectCardEdit extends OxVue {
    @Prop({ default: "" })
    private icon?: string

    @Prop({ default: "" })
    private title?: string
}
