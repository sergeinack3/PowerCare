/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import INProvider from "./INProvider"

/**
 * Provider qui g�re les flag de d�pendance
 */
export default class DependancesProvider extends INProvider {
    constructor () {
        super()
        this.url = "dependances"
    }

    protected translateData (data: any): object {
        const attributes = data.attributes
        return {
            libraries: attributes.libraries_check,
            packages: attributes.packages_check
        }
    }
}
