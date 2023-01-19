/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { Tab, TabBadgeModel } from "@/components/Appbar/Models/AppbarModel"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"

/**
 * TabBadge
 * Badge tab component
 */
@Component
export default class TabBadge extends OxVue {
    @Prop({ default: "" })
    private tab!: Tab

    private get showCounter (): boolean {
        return this.counter > 0
    }

    private get tabBadge (): TabBadgeModel {
        return OxStoreCore.getters.getTabBadge(this.tab.mod_name, this.tab.tab_name) as TabBadgeModel
    }

    private get counter (): number {
        const tabBadge = this.tabBadge
        if (tabBadge) {
            return tabBadge.counter
        }
        return 0
    }

    private get badgeColor (): string {
        const tabBadge = this.tabBadge
        if (tabBadge) {
            return tabBadge.color
        }
        return ""
    }
}
