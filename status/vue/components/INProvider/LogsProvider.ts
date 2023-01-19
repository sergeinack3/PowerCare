/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import INProvider from "./INProvider"

/**
 * Provider principal de status
 */
export default class LogsProvider extends INProvider {
    constructor () {
        super()
        this.url = "logs"
    }

    protected translateData (data: any): object[] {
        return data.map(
            (log) => {
                return {
                    id: log.id,
                    type: log.type,
                    date: log.attributes.date,
                    level: log.attributes.level,
                    message: log.attributes.message
                }
            }
        )
    }
}
