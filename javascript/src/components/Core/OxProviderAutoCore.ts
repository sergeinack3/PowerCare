/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxProviderCore from "@/components/Core/OxProviderCore"

/**
 * OxProviderAutoCore
 *
 * Provider de données pour les autocompletes
 */
export default class OxProviderAutoCore extends OxProviderCore {
    /**
     *
     * @param url
     * @param params
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any, @typescript-eslint/no-unused-vars */
    public async getAutocomplete (filter?: string): Promise<any[]> {
        return []
    }

    /**
     *
     * @param url
     * @param params
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any, @typescript-eslint/no-unused-vars */
    public async getAutocompleteById (identifier?: string): Promise<any[]> {
        return []
    }
}
