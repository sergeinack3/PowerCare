/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import INProvider from "./INProvider"
import axios from "axios"

/**
 * Provider principal de status
 */
export default class InformationProvider extends INProvider {
    constructor () {
        super()
        this.url = "infos"
    }

    public async getDOM (): Promise<string> {
        try {
            const response = await axios.get(this.url, INProvider.getHeader())
            return response.data
        }
        catch (error: any) {
            throw new Error(error)
        }
    }
}
