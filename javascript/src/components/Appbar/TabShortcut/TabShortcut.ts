/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { Tab } from "@/components/Appbar/Models/AppbarModel"
import OxModuleIcon from "@/components/Visual/Basics/OxModuleIcon/OxModuleIcon.vue"
import TabBadge from "@/components/Appbar/TabBadge/TabBadge.vue"

/**
 * TabShortcut
 * Tab shortcut component
 */
@Component({ components: { OxModuleIcon, TabBadge } })
export default class TabShortcut extends OxVue {
    @Prop()
    private tab!: Tab

    @Prop({ default: "" })
    private moduleCategory!: string

    @Prop({ default: false })
    protected mobile!: boolean

    private get moduleName (): string {
        return this.tr("module-" + this.tab.mod_name + "-court")
    }

    private get tabName (): string {
        return this.tab.is_param
            ? this.tr("settings")
            : this.tr("mod-" + this.tab.mod_name + "-tab-" + this.tab.tab_name)
    }

    private get tabLink (): string {
        return this.tab._links.tab_url
    }
}
