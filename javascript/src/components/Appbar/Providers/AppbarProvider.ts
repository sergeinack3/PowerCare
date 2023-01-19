/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxProviderCore from "@/components/Core/OxProviderCore"
import { Group, Module, Tab } from "@/components/Appbar/Models/AppbarModel"
import { ApiTranslatedResponse } from "@/components/Models/ApiResponseModel"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"

export default class AppbarProvider extends OxProviderCore {
    constructor () {
        super()
        this.useRawUrl = true
    }

    public async getModuleTabs (url: string): Promise<Array<Tab>> {
        return (await this.getApi(url)).data as unknown as Tab[]
    }

    public async getEtabs (url: string): Promise<Array<Group>> {
        return (await this.getApi(url)).data as unknown as Group[]
    }

    public async putPinnedTabs (module?: Module): Promise<ApiTranslatedResponse> {
        const _module = module ?? OxStoreCore.getters.getCurrentModule
        const putTabs: Array<object> = []
        _module.pinned_tabs.forEach((tab) => {
            putTabs.push({
                type: "pinned_tab",
                id: null,
                attributes: {
                    _tab_name: tab.tab_name
                }
            })
        })
        const putData = {
            data: putTabs
        }
        return super.postApi(_module._links.tabs, putData)
    }
}
