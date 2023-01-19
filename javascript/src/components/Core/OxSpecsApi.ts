/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxParametersProvider from "@/components/Core/OxParametersApi/OxParametersProvider"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"

/**
 * OxSpecsApi
 *
 * Gestionnaire des specs d'objet
 */
export default class OxSpecsApi {
    /**
     * Ajoute de nouvelles spécifications dans le store
     * @param {string} classe - Classe principale à laquelle appartient les specs (PHP side)
     * @param {string} type - Type de ressource à laquelle appartiennent les specs (API side)
     * @param {string[]} fieldsets - Catégories de champs
     */
    public static async setSpecs (classe: string, type: string, fieldsets: string[] | false = false) {
        if (!classe) {
            return false
        }
        let url = "schemas/models/" + classe
        if (fieldsets) {
            url += "?fieldsets="
            fieldsets.forEach(
                (_fieldset, _fieldsetIndex) => {
                    url += ((_fieldsetIndex > 0) ? "%2C" : "") + _fieldset
                }
            )
        }
        return this.setSpecsByLink(url, type)
    }

    /**
     * Ajoute de nouvelles specifications dans le store
     * @param {string} url - Lien de la ressource de specifcations
     * @param {string} type - Type de ressource à laquelle appartiennent les specs
     * @param {boolean} addOrigin - Ajout de l'origine-url => Patch pour faire fonctionner les url retournées depuis les links
     *
     * @return {Promise<void>}
     */
    public static async setSpecsByLink (url: string, type: string, addOrigin = false): Promise<void> {
        const fieldsets = this.extractFieldsets(url)
        if (OxStoreCore.getters.hasSpecByFieldsets(type, fieldsets)) {
            return
        }
        // @todo : Reboucler avec le back "Quelle doit être la forme es urls échangées dans les paquets"
        url = (addOrigin ? location.origin : "") + url
        const specs = await (new OxParametersProvider()).loadSpecs(url, addOrigin)
        const objectSpec = {}
        specs.forEach(
            (_spec) => {
                objectSpec[_spec.field] = {
                    specs: _spec,
                    field: _spec.field,
                    object: type
                }
            }
        )
        OxStoreCore.commit("setSpec", { type: type, specs: objectSpec, fieldsets: fieldsets })
    }

    /**
     * Récupère la liste des catégories de champs (Fieldset) depuis un lien de ressource
     * @param {string} url - Lien de spécifications
     *
     * @return {Array<string>}
     */
    private static extractFieldsets (url: string): string[] {
        let match = url.match(/[?&]fieldsets=.*&/g)
        if (!match) {
            match = url.match(/[?&]fieldsets=.*$/g)
        }
        if (!match) {
            match = ["default"]
        }

        const fieldset = match[0]
        return decodeURIComponent(fieldset.substr(fieldset.indexOf("=") + 1).replace("&", ""))
            .split(",")
    }

    /**
     * Récupération de spécifications depuis le store
     * @param {string} type - Type de ressource
     * @param {string} field - Liste des catégorie de champ
     *
     * @return {object}
     */
    public static get (type: string, field: string): object {
        return OxStoreCore.getters.spec(type, field)
    }
}
