/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import {
    addError,
    addInfo,
    getErrors, getInfos,
    localStorageKey,
    markInfoAsRead,
    removeNotify,
    removeObsoleteNotifications
} from "@/core/utils/OxNotifyManager"

/**
 * OxJsonApiManager tests
 */
export default class OxNotifyManagerTest extends OxTest {
    protected component = "OxNotifyManagerTest"

    protected beforeTest () {
        super.beforeTest()
        localStorage.clear()
    }

    public testAddDefaultInfo () {
        addInfo("Test")
        const notifications = this.getNotificationsFromLocalStorage()
        expect(notifications).toBeInstanceOf(Array)
        expect(notifications).toHaveLength(1)
        expect(notifications[0]).toMatchObject({
            message: "Test",
            isError: false,
            minTime: 500,
            maxTime: 4000,
            read: false,
            closable: true
        })
    }

    public testAddCustomInfo () {
        addInfo("Custom test", 2000, 5000, false)
        const notifications = this.getNotificationsFromLocalStorage()
        expect(notifications).toBeInstanceOf(Array)
        expect(notifications).toHaveLength(1)
        expect(notifications[0]).toMatchObject({
            message: "Custom test",
            isError: false,
            minTime: 2000,
            maxTime: 5000,
            read: false,
            closable: false
        })
    }

    public testAddError () {
        addError("Error")
        const notifications = this.getNotificationsFromLocalStorage()
        expect(notifications).toBeInstanceOf(Array)
        expect(notifications).toHaveLength(1)
        expect(notifications[0]).toMatchObject({
            message: "Error",
            isError: true,
            minTime: 500,
            maxTime: 4000,
            read: false,
            closable: true
        })
    }

    public testRemoveNotify () {
        addInfo("Test")
        let notifications = this.getNotificationsFromLocalStorage()
        removeNotify(notifications[0].id)
        notifications = this.getNotificationsFromLocalStorage()
        expect(notifications).toBeInstanceOf(Array)
        expect(notifications).toHaveLength(0)
    }

    public testRemoveObsoleteNotifications () {
        addInfo("Test")
        addInfo("Test 2")
        addError("Error")
        let notifications = this.getNotificationsFromLocalStorage()
        markInfoAsRead(notifications[0].id)
        removeObsoleteNotifications()
        notifications = this.getNotificationsFromLocalStorage()
        expect(notifications).toBeInstanceOf(Array)
        expect(notifications).toHaveLength(1)
        expect(notifications[0]).toMatchObject({
            message: "Test 2",
            isError: false,
            minTime: 500,
            maxTime: 4000,
            read: false,
            closable: true
        })
    }

    public testGetErrorsWhenNoError () {
        expect(getErrors()).toBeInstanceOf(Array)
        expect(getErrors()).toHaveLength(0)
    }

    public testGetErrorsWhenError () {
        addError("Error")
        addError("Error 2")
        const notifications = getErrors()
        expect(notifications).toBeInstanceOf(Array)
        expect(notifications).toHaveLength(2)
        expect(notifications).toEqual([
            {
                id: notifications[0].id,
                message: "Error",
                isError: true,
                minTime: 500,
                maxTime: 4000,
                read: false,
                closable: true
            },
            {
                id: notifications[1].id,
                message: "Error 2",
                isError: true,
                minTime: 500,
                maxTime: 4000,
                read: false,
                closable: true
            }
        ])
    }

    public testGetInfosWhenNoInfo () {
        addError("Error")
        expect(getInfos()).toBeInstanceOf(Array)
        expect(getInfos()).toHaveLength(0)
    }

    public testGetInfosWhenInfos () {
        addInfo("Info 1")
        addInfo("Info 2")
        const notifications = getInfos()
        expect(notifications).toBeInstanceOf(Array)
        expect(notifications).toHaveLength(2)
        expect(notifications).toEqual([
            {
                id: notifications[0].id,
                message: "Info 1",
                isError: false,
                minTime: 500,
                maxTime: 4000,
                read: false,
                closable: true
            },
            {
                id: notifications[1].id,
                message: "Info 2",
                isError: false,
                minTime: 500,
                maxTime: 4000,
                read: false,
                closable: true
            }
        ])
    }

    public testMarkInfoAsRead () {
        addInfo("Info 1")
        let notifications = this.getNotificationsFromLocalStorage()
        markInfoAsRead(notifications[0].id)
        notifications = this.getNotificationsFromLocalStorage()
        expect(notifications).toBeInstanceOf(Array)
        expect(notifications).toHaveLength(1)
        expect(notifications[0]).toMatchObject({
            message: "Info 1",
            isError: false,
            minTime: 500,
            maxTime: 4000,
            read: true,
            closable: true
        })
    }

    private getNotificationsFromLocalStorage () {
        return JSON.parse(localStorage.getItem(localStorageKey) || "")
    }
}

(new OxNotifyManagerTest()).launchTests()
