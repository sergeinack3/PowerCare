/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { Group } from "@/components/Appbar/Models/AppbarModel"

/**
 * GroupRadio
 * Group radio component
 */
@Component
export default class GroupRadio extends OxVue {
    @Prop({ default: false })
    private actived!: boolean

    @Prop()
    private group!: Group

    @Prop()
    private functionName!: string | boolean

    private get radioClass (): string {
        return this.actived ? "active" : ""
    }

    private selectGroup () {
        this.$emit("click", this.group._id)
    }
}
