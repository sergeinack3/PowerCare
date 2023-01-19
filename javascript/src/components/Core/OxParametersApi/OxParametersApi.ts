/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxParametersProvider from "@/components/Core/OxParametersApi/OxParametersProvider"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"

/**
 * OxParametersApi
 *
 * Gestion des configuration, pr�f�rences et locales de l'application
 */
export default class OxParametersApi {
    /**
     * R�cup�ration d'une configuration
     * @param {string} conf - Clef de configuration
     *
     * @return {Promise<any>}
     */
    public static async conf (conf: string): Promise<string> {
        if (OxStoreCore.getters.conf(conf) === "undefined") {
            if (!OxStoreCore.getters.hasConfigurationPromise(conf)) {
                OxStoreCore.commit("setConfigurationPromise", {
                    conf: conf,
                    /* eslint-disable  no-async-promise-executor */
                    promise: new Promise(async (resolve) => {
                        const configuration = await OxParametersProvider.loadConfiguration(conf)
                        OxStoreCore.commit("setConfiguration", { name: conf, value: configuration })
                        OxStoreCore.commit("removeConfigurationPromise", conf)

                        resolve(configuration)
                    })
                }
                )
            }
            return OxStoreCore.getters.configurationPromise(conf)
        }
        const configValue = OxStoreCore.getters.conf(conf)
        if (configValue === "undefined") {
            return ""
        }
        return configValue
    }

    // /**
    //  * R�cup�ration d'une configuration
    //  *
    //  * @param conf string Clef de la configuration
    //  *
    //  * @return string
    //  */
    // public static async conf(conf: string): Promise<string> {
    //   if (OxStoreCore.getters.conf(conf) === "undefined") {
    //     //////////////////////////////////////////
    //     const f = OxStoreCore.getters.hasPromise(conf)
    //
    //     // OxStoreCore.commit("addPromise", {
    //     //   conf: conf,
    //     //   promise: new Promise((a) => {
    //     //     console.log(" New Promise ")
    //     //     OxStoreCore.commit("some data")
    //     //     console.log(OxStoreCore.getters.pika)
    //     //     a("other data")
    //     //   })
    //     // })
    //
    //     // if (f) {
    //     //   const configuration = await OxParametersProvider.loadConfiguration(conf);
    //     //   OxStoreCore.commit("setConf", {name: conf, value: configuration});
    //     //
    //     //   Promise.all(OxStoreCore.getters.getPromises).then((values) => {
    //     //     console.log(values);
    //     //   });
    //     // }
    //     //////////////////////////////////////////////
    //   }
    //   const configValue = OxStoreCore.getters.conf(conf);
    //   if (configValue === "undefined") {
    //     console.warn("Trying to access to an undefined configuration : " + conf);
    //     return "";
    //   }
    //   return configValue;
    // }

    // /**
    //  * R�cup�ration d'une configuration d'�tablissement
    //  *
    //  * @param gconf string Clef de la configuration
    //  *
    //  * @return string
    //  */
    // public static async gconf(gconf: string): Promise<string> {
    //   if (OxStoreCore.getters.conf(gconf) === "undefined") {
    //     const configuration = await OxParametersProvider.loadGroupConfiguration(gconf);
    //     OxStoreCore.commit("setGroupConfiguration", {name: gconf, value: configuration});
    //   }
    //   const configValue = OxStoreCore.getters.gconf(gconf);
    //   if (configValue === "undefined") {
    //     console.warn("Trying to access to an undefined group configuration : " + gconf);
    //     return "";
    //   }
    //   return configValue;
    // }

    /**
     * R�cup�ration d'une pr�f�rence
     *
     * @param {string} pref - Clef de la pr�f�rence � r�cup�rer
     *
     * @return {string}
     */
    public static pref (pref: string): string {
        return OxStoreCore.getters.pref(pref)
    }

    /**
     * Renseigne une pr�f�rence
     *
     * @param {string} label - Clef de la pr�f�rence
     * @param {string} value - Valeur de la pr�f�rence
     */
    public static setPref (label: string, value: string): void {
        OxStoreCore.commit("setPreference", { name: label, value: value })
    }

    /**
     * Renseigne plusieurs pr�f�rences
     *
     * @param {Array<{name: string; value: string}>} prefs - Ensemble de pr�f�rences
     */
    public static setPrefs (prefs: {name: string; value: string}[]): void {
        OxStoreCore.commit("setPreferences", prefs)
    }
}
