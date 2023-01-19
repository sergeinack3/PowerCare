/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxParametersApi from "@/components/Core/OxParametersApi/OxParametersApi"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"

/**
 * OxVueApi
 *
 * Classe pilote des donn�es globales de l'application OxVue : Etat de chargement, url d'api, ...
 */
export default class OxVueApi {
    /**
     * Initialisation globale d'une zone VueJS
     * @param {object[]} preferences - Liste compl�te des pr�f�rences de l'application
     * @param {object[]} locales - Liste des traductions
     * @param {string} externalUrl - Url externe de l'application utilis�e pour les appels
     * @param {string} apiUrlSuffix - Suffix d'url d'api
     */
    public static async init (
        preferences: {name: string; value: string}[],
        externalUrl: string,
        apiUrlSuffix: string
    ) {
        OxParametersApi.setPrefs(preferences)
        OxVueApi.setUrl(externalUrl, apiUrlSuffix)
    }

    /**
     * Set des urls
     *
     * @param {string} baseUrl - Url de base
     */
    public static setUrl (baseUrl: string, apiSuffix: string) {
        this.setRootUrl(baseUrl)
        this.setBaseUrl(baseUrl + "/" + apiSuffix)
    }

    /**
     * Set de l'url de base
     *
     * @param {string} baseUrl - Url de base
     */
    public static setRootUrl (baseUrl: string) {
        OxStoreCore.commit("setRootUrl", baseUrl)
    }

    /**
     * Set de l'url de base
     *
     * @param {string} - baseUrl Url de base
     */
    public static setBaseUrl (baseUrl: string) {
        OxStoreCore.commit("setBaseUrl", baseUrl)
    }

    /**
     * R�cup�ration de l'url de base
     *
     * @return string
     */
    public static getBaseUrl (): string {
        return OxStoreCore.getters.url
    }

    /**
     * R�cup�ration de l'url de base
     *
     * @return string
     */
    public static async getRootUrl (): Promise<string> {
        return OxStoreCore.getters.rooturl
    }

    /**
     * Mise en chargement de l'application
     */
    public static load () {
        OxStoreCore.commit("addLoading")
    }

    /**
     * D�sactivation du chargement de l'application
     */
    public static unload () {
        OxStoreCore.commit("removeLoading")
    }

    /**
     * R�cup�ration de l'�tat de chargement de l'application
     */
    public static loading () {
        return OxStoreCore.getters.loading
    }
}
