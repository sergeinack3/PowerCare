/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { BulkElement, BulkResponse } from "@/components/Models/ApiResponseModel"

/**
 * OxBulkCore
 */
export default class OxBulkCore {
    private bodies: BulkResponse[] = []

    constructor (data: BulkResponse[]) {
        this.bodies = data
    }

    /**
     * Récupération des données d'api en fonction des options de bulk passées lors de l'appel bulk
     *
     * @param {BulkElement} bulkElement - Options de bulk
     *
     * @return {any} - Données dédiées aux options données
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any */
    public getDataFromBulkElement (bulkElement: BulkElement): any {
        /* eslint-disable  @typescript-eslint/no-explicit-any */
        let body = (this.bodies.find(
            (body) => {
                return body.id === bulkElement.id
            }
        ) as unknown as { data: any }).data
        if (bulkElement.transformer && typeof (bulkElement.transformer) === "function") {
            body = bulkElement.transformer(body)
        }
        return body
    }
}
