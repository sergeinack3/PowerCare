/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { NotificationDelay, NotificationType } from "@/components/Core/OxNotify/OxNotifyModel"
import OxNotify from "@/components/Core/OxNotify/OxNotify"
import { OxTest } from "oxify"
import { Wrapper } from "@vue/test-utils"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import OxNotifyManagerApi from "@/components/Core/OxNotify/OxNotifyManagerApi"

/**
 * Test pour la classe OxNotify
 */
export default class OxNotifyTest extends OxTest {
    protected component = OxNotify

    private notifyManager = new OxNotifyManagerApi(OxStoreCore)

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<OxNotify> {
        return super.mountComponent(props) as Wrapper<OxNotify>
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): OxNotify {
        return this.mountComponent(props).vm as OxNotify
    }

    /**
     * @inheritDoc
     */
    protected afterTest () {
        this.notifyManager.removeAllNotifications()
    }

    /**
     * Test de l'affichage d'un label donné
     */
    public testLabel (): void {
        const testLabel = "Test"
        const notify = this.vueComponent({ notificationManager: this.notifyManager })
        this.notifyManager.addInfo(testLabel)
        const notifications = this.privateCall(notify, "notifications")
        this.assertHaveLength(notifications, 1)
        const notification = notifications[0]
        this.assertEqual(
            notification.libelle,
            testLabel
        )
    }

    /**
     * Test d'affichage d'une notification de type information
     */
    public testInfo (): void {
        const notify = this.vueComponent({ notificationManager: this.notifyManager })
        this.notifyManager.addInfo("test")
        const notification = this.notifyManager.notifications[this.notifyManager.notifications.length - 1]
        this.assertEqual(
            notification.type,
            NotificationType.INFO
        )
    }

    /**
     * Test d'affichage d'une notification de type erreur
     */
    public testError (): void {
        const notify = this.vueComponent({ notificationManager: this.notifyManager })
        this.notifyManager.addError("test")
        const notification = this.notifyManager.notifications[this.notifyManager.notifications.length - 1]
        this.assertEqual(
            notification.type,
            NotificationType.ERROR
        )
    }

    /**
     * Test d'affichage de plusieurs notifications
     */
    public testMultipleNotification (): void {
        const notify = this.vueComponent({ notificationManager: this.notifyManager })
        this.notifyManager.addInfo("test")
        this.notifyManager.addError("test")
        this.notifyManager.addInfo("test")
        const notifications = this.privateCall(notify, "notifications")
        this.assertHaveLength(notifications, 3)
    }

    /**
     * Test du retrait d'une notification
     */
    public async testUnsetNotification (): Promise<void> {
        const notify = this.vueComponent({ notificationManager: this.notifyManager })
        let callbackFlag = 0
        const expectedCallbackFlag = 1
        this.notifyManager.addInfo(
            "test",
            {
                callback: () => {
                    callbackFlag = expectedCallbackFlag
                }
            }
        )
        const notifications = this.privateCall(notify, "notifications")
        this.assertHaveLength(notifications, 1)
        const notification = notifications[0]
        this.notifyManager.removeNotification(notification.key)
        await this.wait(2000)
        this.assertHaveLength(this.privateCall(notify, "notifications"), 0)
        this.assertEqual(callbackFlag, expectedCallbackFlag)
    }

    /**
     * Test du délai de disparition d'une notification
     */
    public async testDelay (): Promise<void> {
        const notify = this.vueComponent({ notificationManager: this.notifyManager })
        let callbackFlag = 0
        const expectedCallbackFlag = 1
        this.notifyManager.addInfo(
            "test",
            {
                delay: NotificationDelay.SHORT,
                callback: () => {
                    callbackFlag = expectedCallbackFlag
                }
            }
        )
        let notifications = this.privateCall(notify, "notifications")
        this.assertHaveLength(notifications, 1)

        await this.wait(NotificationDelay.SHORT + 100)

        notifications = this.privateCall(notify, "notifications")
        this.assertHaveLength(notifications, 0)
        this.assertEqual(callbackFlag, expectedCallbackFlag)
    }

    /**
     * Display an error over an info test
     */
    public testDisplayErrorOnInfo (): void {
        const notify = this.vueComponent({ notificationManager: this.notifyManager })
        this.notifyManager.addInfo("test info")
        this.notifyManager.addError("test error")
        const displayed = this.privateCall(
            notify,
            "notificationDisplayed"
        )
        this.assertHaveLength(displayed, 1)
        const error = displayed[0]
        this.assertEqual(error.type, NotificationType.ERROR)
    }

    public testNotifyWithoutManager (): void {
        const notify = this.vueComponent({})
        this.assertHaveLength(
            this.privateCall(
                notify,
                "notifications"
            ),
            0
        )
    }

    /**
     * Additional button on notification test
     */
    public testAdditionnalButton (): void {
        const notify = this.vueComponent({ notificationManager: this.notifyManager })
        let callbackFlag = false
        const expectedCallbackFlag = true
        this.notifyManager.addInfo(
            "test info",
            {
                button: {
                    libelle: "Test Button",
                    callback: () => {
                        callbackFlag = expectedCallbackFlag
                    }
                }
            }
        )
        const displayedNotification = this.notifyManager.notifications[this.notifyManager.notifications.length - 1]
        this.privateCall(notify, "notificationButtonCallback", displayedNotification)
        this.assertEqual(callbackFlag, expectedCallbackFlag)
    }

    /**
     * Notification recovery test
     */
    public testInfoRecovery (): void {
        const notify = this.vueComponent({ notificationManager: this.notifyManager })
        this.notifyManager.addInfo("test info")
        this.notifyManager.addInfo("test info 2")
        let displayedNotifs = this.privateCall(notify, "notificationDisplayed")
        this.assertHaveLength(displayedNotifs, 1)
        this.privateCall(
            notify,
            "removeNotification",
            displayedNotifs[0]
        )
        displayedNotifs = this.privateCall(notify, "notificationDisplayed")
        this.assertHaveLength(displayedNotifs, 0)
        this.wait(this.privateCall(notify, "maxRecovery"))

        this.notifyManager.addInfo("test info 3")
        const info3 = this.notifyManager.notifications[this.notifyManager.notifications.length - 1]
        this.privateCall(notify, "removeNotification", info3)
        this.notifyManager.addError("test error")
        displayedNotifs = this.privateCall(notify, "notificationDisplayed")
        this.assertHaveLength(displayedNotifs, 1)
        this.assertEqual(displayedNotifs[0].type, NotificationType.ERROR)
    }
}

(new OxNotifyTest()).launchTests()
