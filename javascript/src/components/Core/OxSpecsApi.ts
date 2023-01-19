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
     * Ajoute de nouvelles sp�cifications dans le store
     * @param {string} classe - Classe principale � laquelle appartient les specs (PHP side)
     * @param {string} type - Type de ressource � laquelle appartiennent les specs (API side)
     * @param {string[]} fieldsets - Cat�gories de champs
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
     * @param {string} type - Type de ressource � laquelle appartiennent les specs
     * @param {boolean} addOrigin - Ajout de l'origine-url => Patch pour faire fonctionner les url retourn�es depuis les links
     *
     * @return {Promise<void>}
     */
    public static async setSpecsByLink (url: string, type: string, addOrigin = false): Promise<void> {
        const fieldsets = this.extractFieldsets(url)
        if (OxStoreCore.getters.hasSpecByFieldsets(type, fieldsets)) {
            return
        }
        // @todo : Reboucler avec le back "Quelle doit �tre la forme es urls �chang�es dans les paquets"
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
     * R�cup�re la liste des cat�gories de champs (Fieldset) depuis un lien de ressource
     * @param {string} url - Lien de sp�cifications
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
     * R�cup�ration de sp�cifications depuis le store
     * @param {string} type - Type de ressource
     * @param {string} field - Liste des cat�gorie de champ
     *
     * @return {object}
     */
    public static get (type: string, field: string): object {
        return OxStoreCore.getters.spec(type, field)
    }
}
