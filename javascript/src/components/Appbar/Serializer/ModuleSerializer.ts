/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Module, Tab } from "@/components/Appbar/Models/AppbarModel"

export default class ModuleSerializer {
    public static serialize (baseModule, tabs: Array<Tab> = []): Module {
        const module = baseModule as Module
        module.pinned_tabs = []
        module.standard_tabs = []
        module.param_tabs = []
        module.configure_tab = []
        module.tabs_order = []
        if (tabs.length) {
            module.tabs = tabs
        }
        if (baseModule.tabs) {
            baseModule.tabs.forEach((tab) => {
                const tabData = tab
                const tabLinks = tab._links
                if (tabData.pinned_order !== null) {
                    module.pinned_tabs.splice(
                        tabData.pinned_order,
                        0,
                        { tab_name: tabData.tab_name, mod_name: tabData.mod_name, _links: { tab_url: tabLinks.tab_url } }
                    )
                    module.tabs_order.push(tabData.tab_name)
                }
                if (tabData.is_standard && tabData.pinned_order === null) {
                    module.standard_tabs.push({ tab_name: tabData.tab_name, mod_name: tabData.mod_name, _links: { tab_url: tabLinks.tab_url } })
                    module.tabs_order.push(tabData.tab_name)
                }
                if (tabData.is_param) {
                    module.param_tabs.push({ tab_name: tabData.tab_name, mod_name: tabData.mod_name, _links: { tab_url: tabLinks.tab_url } })
                }
                if (tabData.is_config) {
                    module.configure_tab.push({ tab_name: tabData.tab_name, mod_name: tabData.mod_name, _links: { tab_url: tabLinks.tab_url } })
                }
            })
        }

        return module as Module
    }

    public static addTabsToModule (module: Module, tabs: Array<Tab>): Module {
        return ModuleSerializer.serialize(module, tabs)
    }
}
