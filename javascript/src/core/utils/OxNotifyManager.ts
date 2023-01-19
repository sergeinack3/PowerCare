/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxNotify } from "@/core/types/OxNotifyTypes"
import { uniqueId } from "lodash"

export const localStorageKey = "notifications"
const minTimeInfo = 500
const maxTimeInfo = 4000

/**
 * Push a new notification in localStorage
 * @param {string} message - The notification's message
 * @param {boolean} isError - True if the notification is an error, false otherwise
 * @param {number} minTime - Minimum time before the notification is considered as read (notifications read are not display on page reload)
 * @param {number} maxTime - Maximum time the notification is display (automatically disappear after)
 * @param {boolean} closable - Show close button
 */
export function addNotify ({
    message,
    isError,
    minTime = minTimeInfo,
    maxTime = maxTimeInfo,
    closable = true
}: {
    message: string
    isError: boolean
    minTime?: number
    maxTime?: number
    closable?: boolean
}) {
    const notify: OxNotify = { id: uniqueId(), message, isError, minTime, maxTime, read: false, closable }
    const notifications = getNotificationsFromLocalStorage()
    notifications.push(notify)
    setNotificationsInLocalStorage(notifications)
}

/**
 * Push a new info notification in localStorage
 * @param {string} message - The notification's message
 * @param {number} minTime - Minimum time before the notification is considered as read (notifications read are not display on page reload)
 * @param {number} maxTime - Maximum time the notification is display (automatically disappear after)
 * @param {boolean} closable - Show close button
 */
export function addInfo (message: string, minTime?: number, maxTime?: number, closable = true) {
    addNotify({ message, minTime, maxTime, isError: false, closable })
}

/**
 * Push a new error notification in localStorage
 * @param {string} message - The notification's message
 */
export function addError (message: string) {
    addNotify({ message, isError: true })
}

/**
 * Remove a notification from localStorage
 * @param {string} id - Notification's id to remove
 */
export function removeNotify (id: string) {
    let notifications = getNotificationsFromLocalStorage()
    notifications = notifications.filter((notify) => {
        return notify.id !== id
    })
    setNotificationsInLocalStorage(notifications)
}

/**
 * Remove all error notifications and all notifications read from localStorage
 */
export function removeObsoleteNotifications () {
    let notifications = getNotificationsFromLocalStorage()
    notifications = notifications.filter((notify) => {
        return !notify.isError && !notify.read
    })
    setNotificationsInLocalStorage(notifications)
}

/**
 * Return all error notifications from localStorage
 * @returns {OxNotify[]}
 */
export function getErrors (): OxNotify[] {
    return getNotificationsFromLocalStorage().filter((notify) => notify.isError)
}

/**
 * Return all info notifications from localStorage
 * @returns {OxNotify[]}
 */
export function getInfos (): OxNotify[] {
    return getNotificationsFromLocalStorage().filter((notify) => !notify.isError)
}

/**
 * Mark a notification as read
 * @param infoId
 */
export function markInfoAsRead (infoId: string) {
    setNotificationsInLocalStorage(getNotificationsFromLocalStorage().map((notify) => {
        if (notify.id === infoId) {
            return { ...notify, read: true }
        }
        return notify
    }), false)
}

/**
 * Return all notification from localStorage
 * @returns {OxNotify[]}
 */
function getNotificationsFromLocalStorage (): OxNotify[] {
    const notificationsString = localStorage.getItem(localStorageKey)
    if (notificationsString) {
        const notifications = JSON.parse(notificationsString) as OxNotify[]
        if (!Array.isArray(notifications)) {
            localStorage.clear()
        }
        return notifications
    }
    return []
}

/**
 * Store notifications in localStorage
 * @param {OxNotify[]} notifications - The notifications to store
 * @param {boolean} dispatch - Emit "notify" event when notifications are store if true
 */
function setNotificationsInLocalStorage (notifications: OxNotify[], dispatch = true) {
    if (!Array.isArray(notifications)) {
        throw new Error("Invalid notifications type")
    }
    localStorage.setItem(localStorageKey, JSON.stringify(notifications))
    if (dispatch) {
        emitEvent()
    }
}

/**
 * Emit "notify" event on window
 */
function emitEvent () {
    window.dispatchEvent(new Event("notify"))
}
