/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import { Notification, NotificationDelay, NotificationType, NotificationOpt } from "@/components/Core/OxNotify/OxNotifyModel"

/**
 * OxNotifyManagerApi
 */
export default class OxNotifyManagerApi {
    private store: typeof OxStoreCore

    constructor (store: typeof OxStoreCore) {
        this.store = store
    }

    /**
     * Ajout d'une notification à afficher
     * @param {string} libelle - Texte de la notification
     * @param {NotificationType} type - Type de la notification
     * @param {NotificationOpt} options - Options de la notification
     */
    public addNotification (libelle: string, type: NotificationType, options: NotificationOpt = {}): void {
        this.store.commit(
            "addNotification",
            {
                libelle: libelle,
                type: type,
                delay: options.delay || NotificationDelay.MEDIUM,
                key: Math.ceil(Math.random() * Math.pow(10, 16)),
                callback: options.callback,
                button: options.button,
                hide: false
            }
        )
    }

    /**
     * Ajout d'une notification de type Information
     * @param {string} libelle - Texte de la notification
     * @param {NotificationOpt} options - Options de la notification
     */
    public addInfo (libelle: string, options: NotificationOpt = {}): void {
        this.addNotification(libelle, NotificationType.INFO, options)
    }

    /**
     * Ajout d'une notification de type Error
     * @param {string} libelle - Texte de la notification
     * @param {NotificationOpt} options - Options de la notification
     */
    public addError (libelle: string, options: NotificationOpt = {}): void {
        this.addNotification(libelle, NotificationType.ERROR, options)
    }

    /**
     * Retrait d'une notification
     * @param {number} key - Identifiant de la notification dans la collection des notifications
     */
    public removeNotification (key: number): void {
        const notification = this.notifications.find(notification => notification.key === key)
        if (notification && notification.callback && !notification.callbackDone) {
            this.store.commit("callbackDoneNotification", key)
            notification.callback()
        }
        this.store.commit("removeNotification", key)
    }

    /**
     * Retrait de l'ensemble des notifications
     */
    public removeAllNotifications (): void {
        this.store.commit("removeAllNotifications")
    }

    /**
     * Récupération de la liste des notifications
     *
     * @return {Array<Notification>}
     */
    public get notifications (): Notification[] {
        return this.store.getters.getNotifications
    }
}
