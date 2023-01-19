/**
 * @package Mediboard\Mediuser
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { ApiTranslatedResponse } from "@/components/Models/ApiResponseModel"
import OxProviderCore from "@/components/Core/OxProviderCore"
import { Praticien } from "../Models/PraticienModel"

/**
 * Provider permettant la gestion des praticiens
 */
export default class TammProtocoleProvider extends OxProviderCore {
    /**
     * Récupération des ifnormations de praticien en fonction de son RPPS
     * @param {string} rpps - Rpps du praticien recherché
     *
     * @return {Promise<Praticien>}
     */
    public async getByRpps (rpps: string): Promise<Praticien> {
        return this.praticienTransformer(
            (await (this.getApi("mediuser/mediuser_by_rpps/" + rpps)) as ApiTranslatedResponse).data as unknown as Praticien
        )
    }

    /**
     * Mise en forme des informations d'un Praticien
     * @param {Praticien} praticien - Informations de praticien récupérées de l'API
     *
     * @return any
     */
    public praticienTransformer (praticien): Praticien {
        return Object.assign(
            praticien,
            {
                view: (praticien._user_first_name ? praticien._user_first_name + " " : "") + praticien._user_last_name,
                initials: praticien.initials ? praticien.initials : ((praticien._user_first_name ? praticien._user_first_name[0] + praticien._user_last_name[0] : ""))
            }
        )
    }
}
