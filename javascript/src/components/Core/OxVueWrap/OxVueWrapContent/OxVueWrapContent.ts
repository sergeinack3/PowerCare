/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import OxLoading from "@/components/Core/OxLoading/OxLoading.vue"
import OxNotify from "@/components/Core/OxNotify/OxNotify.vue"
import OxAlert from "@/components/Core/OxAlert/OxAlert.vue"
import OxAlertManagerApi from "@/components/Core/OxAlert/OxAlertManagerApi"
import OxNotifyManagerApi from "@/components/Core/OxNotify/OxNotifyManagerApi"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import { OxStyleLoader } from "oxify"

/**
 * OxVueWrapContent
 *
 * Initialisateur général de l'application
 */
@Component({ components: { OxLoading, OxNotify, OxAlert, OxStyleLoader } })
export default class OxVueWrapContent extends OxVue {
    private get alertManager (): OxAlertManagerApi {
        return new OxAlertManagerApi(OxStoreCore)
    }

    private get notifyManager (): OxNotifyManagerApi {
        return new OxNotifyManagerApi(OxStoreCore)
    }
}
