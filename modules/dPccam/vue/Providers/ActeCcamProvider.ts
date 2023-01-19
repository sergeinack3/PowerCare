/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxProviderCore from "@/components/Core/OxProviderCore"
import { Acte } from "../Models/CcamModel"

/**
 * ActeCcamProvider
 */
export default class ActeCcamProvider extends OxProviderCore {
    /**
     * Mise en forme des données d'API d'un acte
     * @param {Acte} acte
     *
     * @return {Acte}
     */
    public static acteTransformer (acte: Acte): Acte {
        return Object.assign(
            acte,
            {
                view: acte.code + " " + acte.libelle_long
            }
        )
    }

    /**
     * Mise en forme des données d'API d'un ensemble d'actes
     * @param {Object[]} actes
     *
     * @return {Array<Acte>}
     */
    public static actesTransformer (actes: Acte[]): Acte[] {
        return actes.map((acte) => {
            return this.acteTransformer(acte)
        })
    }
}
