/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxProviderAutoCore from "@/components/Core/OxProviderAutoCore"
import ActeCcamProvider from "./ActeCcamProvider"
import { Acte } from "../Models/CcamModel"

/**
 * ActeCcamAutoProvider
 */
export default class ActeCcamAutoProvider extends OxProviderAutoCore {
    /**
     * Récupération des données d'autocompletion des actes CCAM
     * @param {string} filter
     *
     * @return {Promise<Array<Acte>>}
     */
    public async getAutocomplete (filter?: string): Promise<Acte[]> {
        return ActeCcamProvider.actesTransformer(
            (await this.getApi("ccam/actes", { code: filter })).data as unknown as Acte[]
        ) as unknown as Acte[]
    }
}
