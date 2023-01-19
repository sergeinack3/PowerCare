/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import { Placeholder } from "@/components/Appbar/Models/AppbarModel"
import OxVue from "@/components/Core/OxVue"
import { OxIcon, OxTooltip, OxBadge } from "oxify"
import { callJSFunction } from "@/components/Core/OxUtilsCore"

/**
 * PlaceholderShortcut
 * Header placeholder (shortcut) component
 */
@Component({ components: { OxIcon, OxTooltip, OxBadge } })
export default class PlaceholderShortcut extends OxVue {
    @Prop({ default: false })
    private badged!: boolean

    @Prop({ default: false })
    private flat!: boolean

    @Prop({ default: true })
    private showLabel!: boolean

    @Prop()
    private placeholder!: Placeholder

    private get showCounter (): boolean {
        return (this.placeholder.counter !== null && this.placeholder.counter !== "") || this.badged
    }

    private get showAppfine (): boolean {
        return this.placeholder.icon === "appfine"
    }

    private get showEcap (): boolean {
        return this.placeholder.icon === "ecap"
    }

    private get showIcon (): boolean {
        return (!this.showAppfine && !this.showEcap) && !this.showNumber
    }

    private get showNumber (): boolean {
        return typeof this.placeholder.icon === "number"
    }

    private mounted () {
        if (this.showCounter) {
            // eslint-disable-next-line @typescript-eslint/ban-ts-comment
            // @ts-ignore
            const badge = this.$refs.placeholder.$el.querySelector(".v-badge__wrapper > .v-badge__badge")
            if (badge) {
                badge.setAttribute("id", this.placeholder._id + "_counter")

                if (this.placeholder.counter.toString() === "0") {
                    badge.style.display = "none"
                }
            }
        }
        if (this.placeholder.init_action) {
            callJSFunction(
                this.placeholder.init_action,
                [this.placeholder._id + "_placeholder", this.placeholder._id + "_counter"]
            )
        }
    }

    private click () {
        if (this.placeholder.action && this.placeholder.action !== "" && !this.flat) {
            callJSFunction(this.placeholder.action, this.placeholder.action_args)
        }
        else {
            this.$emit("click")
        }
    }
}
