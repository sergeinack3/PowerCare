/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Alert, AlertOpt } from "@/components/Core/OxAlert/OxAlertModel"
import OxStoreCore from "../OxStores/OxStoreCore"

/**
 * OxAlertManagerApi
 */
export default class OxAlertManagerApi {
    private store: typeof OxStoreCore

    constructor (store: typeof OxStoreCore) {
        this.store = store
    }

    /**
     * Assignation de l'alerte à afficher
     * @param {string} label - Label à afficher dans l'alerte
     * @param {AlertOpt} okOptions - Options relatives au bouton "ok"
     * @param {AlertOpt} nokOptions - Options relatives au bouton "not ok"
     */
    public setAlert (label: string, okOptions: AlertOpt, nokOptions?: AlertOpt): void {
        this.store.commit("setAlert", { msg: label, okOpt: okOptions, nokOpt: nokOptions || false })
    }

    /**
     * Désactivation de l'alerte courante
     */
    public unsetAlert (): void {
        this.store.commit("unsetAlert")
    }

    public getAlert (): Alert {
        return this.store.getters.getAlert
    }
}
