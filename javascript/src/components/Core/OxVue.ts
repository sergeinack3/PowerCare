/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component } from "vue-property-decorator"
import OxParametersApi from "@/components/Core/OxParametersApi/OxParametersApi"
import OxVueApi from "@/components/Core/OxVueApi"
import { AlertOpt } from "@/components/Core/OxAlert/OxAlertModel"
import OxAlertManagerApi from "@/components/Core/OxAlert/OxAlertManagerApi"
import OxStoreCore from "./OxStores/OxStoreCore"
import OxNotifyManagerApi from "@/components/Core/OxNotify/OxNotifyManagerApi"
import { NotificationOpt } from "@/components/Core/OxNotify/OxNotifyModel"
import Vue from "vue"

/**
 * OxVue
 *
 * Surcharge Vue : Propose les méthodes de base pour les composants visibles
 */
@Component
export default class OxVue extends Vue {
    // Etat du composant.
    public active = true

    public loaded = true

    private alertApi = new OxAlertManagerApi(OxStoreCore)
    private notificationApi = new OxNotifyManagerApi(OxStoreCore)

    /**
     * Traduction d'une chaine de caractère
     *
     * @param {string} key - Clef de traduction
     * @param {boolean} plural - Utilisation du pluriel
     *
     * @return {string}
     */
    protected tr (key: string, plural = false): string {
        return OxVue.str(key, plural)
    }

    /**
     * Traduction d'une chaine de caractère
     *
     * @param {string} key - Clef de traduction
     * @param {boolean} plural - Utilisation du pluriel
     *
     * @return {string}
     */
    public static str (key: string, plural = false): string {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        return window.$T(key + (plural ? "|pl" : ""))
    }

    /**
     * Lance le chargement de l'application
     */
    protected load (): void {
        OxVueApi.load()
        this.loaded = false
    }

    /**
     * Désactive l'état de chargement de l'application
     */
    protected unload (): void {
        OxVueApi.unload()
        this.loaded = true
    }

    /**
     * Récupération d'une configuration
     * @param {string} key - Clef de configuration
     *
     * @return {Promise<string>}
     */
    protected async conf (key: string): Promise<string> {
        return await OxParametersApi.conf(key)
    }

    /**
     * Récupération d'une configuration par établissement
     * @param {string} key - Clef de configuration
     */
    // protected async gconf(key: string): Promise<string> {
    //   return await OxParametersApi.gconf(key)
    // }

    /**
     * Mise en capitale d'une chaine de caractères
     * @param {string} value - Valeur à modifier
     *
     * @return {string}
     */
    protected capitalize (value: string): string {
        if (!value) {
            return ""
        }
        return value.charAt(0).toUpperCase() + value.slice(1)
    }

    /**
     * Affichage d'une alerte
     * @param {string} label - Label à afficher
     * @param {Function|AlertOpt} okOptions - Options relatives au bouton "ok"
     * @param {Function|AlertOpt} nokOptions - Options relatives au bouton "non ok"
     */
    protected alert (label: string, okOptions?: Function|AlertOpt, nokOptions?: Function|AlertOpt): void {
        let okOpt: AlertOpt = {
            label: this.tr("OxAlert-defaultOkLabel"),
            callback: false
        }
        if (typeof okOptions === "function") {
            okOpt.callback = okOptions
        }
        else if (okOptions) {
            okOpt = okOptions
        }

        let nokOpt: AlertOpt|undefined
        if (nokOptions && typeof nokOptions === "function") {
            nokOpt = {
                label: this.tr("OxAlert-defaultNokLabel"),
                callback: nokOptions
            }
        }
        else if (nokOptions) {
            nokOpt = nokOptions
        }

        this.alertApi.setAlert(
            label,
            okOpt,
            nokOpt
        )
    }

    /**
     * Show an info notification
     * @param {string} libelle
     * @param {NotificationOpt} options
     */
    protected notify (libelle: string, options: NotificationOpt = {}): void {
        this.notificationApi.addInfo(libelle, options)
    }

    /**
     * Show an error notification
     * @param {string} libelle
     * @param {NotificationOpt} options
     */
    protected notifyError (libelle: string, options: NotificationOpt = {}): void {
        this.notificationApi.addError(libelle, options)
    }
}
