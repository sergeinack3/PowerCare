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
 * GroupLine
 * Group line component
 */
@Component
export default class GroupLine extends OxVue {
    @Prop()
    private group!: Group

    @Prop()
    private functionName!: string | boolean

    @Prop({ default: false })
    private actived!: boolean

    private get lineClass (): string {
        return this.actived ? "active" : ""
    }

    private get functionClass (): string {
        if (this.group.is_main) {
            return "mainFunction"
        }
        if (this.group.is_secondary) {
            return "secondaryFunction"
        }
        return ""
    }

    private selectGroup () {
        this.$emit("click", this.group._id)
    }
}
