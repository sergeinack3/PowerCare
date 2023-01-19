/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { Notification, NotificationType } from "@/components/Core/OxNotify/OxNotifyModel"
import { OxButton } from "oxify"
import OxNotifyManagerApi from "@/components/Core/OxNotify/OxNotifyManagerApi"

/**
 * OxNotify
 */
@Component({ components: { OxButton } })
export default class OxNotify extends OxVue {
    @Prop()
    private notificationManager!: OxNotifyManagerApi

    // @ts-ignore
    private timer: NodeJS.Timeout | false = false
    private recovery = false
    private maxRecovery = 250

    /**
     * Liste des notifications à afficher
     *
     * @return {Array<Notify>}
     */
    private get notifications (): Notification[] {
        if (!this.notificationManager) {
            return []
        }
        return this.notificationManager.notifications
    }

    /**
     * Récupération des classes d'une notification
     * @param {Notification} notification
     *
     * @return {string}
     */
    private notifyClass (notification: Notification): object {
        return {
            "OxNotify-info": notification.type === NotificationType.INFO,
            "OxNotify-error": notification.type === NotificationType.ERROR
        }
    }

    /**
     * Récupération des classes de bouton d'une notification
     * @param {Notification} notification
     *
     * @return {string}
     */
    private closeClass (notification: Notification): string {
        return notification.type === NotificationType.INFO ? "OxNotify-closeInfo" : "OxNotify-closeError"
    }

    /**
     * Retrait d'une notification
     * @param {Notification} notification
     */
    private removeNotification (notification: Notification): void {
        if (notification.type === NotificationType.INFO) {
            this.recovery = true
            this.resetTimer()
            setTimeout(
                () => {
                    this.recovery = false
                },
                this.maxRecovery
            )
        }
        this.notificationManager.removeNotification(notification.key)
    }

    /**
     * Récupération de la liste des notifications à afficher
     *
     * @return {Array<Notification>}
     */
    private get notificationDisplayed (): Notification[] {
        const errors = this.notifications.filter(
            (notification) => {
                return notification.type === NotificationType.ERROR
            }
        )
        if (errors.length > 0) {
            if (this.recovery) {
                this.recovery = false
                this.resetTimer()
            }
            return errors
        }

        if (this.recovery) {
            return []
        }

        const informationList = this.notifications.filter(
            (notification) => {
                return notification.type === NotificationType.INFO
            }
        )
        if (informationList.length > 0) {
            const information = informationList[0]
            if (information.delay && !this.timer) {
                this.timer = setTimeout(
                    () => {
                        this.removeNotification(information)
                    },
                    information.delay
                )
            }
            return [informationList[0]]
        }
        return []
    }

    /**
     * Lancement du callback alternatif d'une notification
     * @param {Notification} notification
     */
    private notificationButtonCallback (notification: Notification): void {
        this.removeNotification(notification)
        if (notification.button && notification.button.callback) {
            notification.button.callback()
        }
    }

    /**
     * Remise à zéro du timer des notifications
     */
    private resetTimer (): void {
        if (!this.timer) {
            return
        }
        window.clearTimeout(this.timer)
        this.timer = false
    }
}
