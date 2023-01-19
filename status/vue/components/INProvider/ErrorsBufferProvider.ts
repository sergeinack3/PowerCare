/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import INProvider from "./INProvider"
import INVue from "../INVue/INVue"

/**
 * Provider principal de status
 */
export default class ErrorsBufferProvider extends INProvider {
    constructor () {
        super()
        this.url = "errors/bufferStatistics"
    }

    protected translateData (data: any): object {
        const attributes = data.attributes
        return {
            path: attributes.path,
            lastUpdate: INVue.dateToString(new Date(attributes.last_update)),
            size: attributes.size * 1,
            filesCount: attributes.files_count
        }
    }
}
