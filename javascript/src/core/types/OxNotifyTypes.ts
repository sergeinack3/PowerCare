/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

export interface OxNotify {
    id: string
    message: string
    isError: boolean
    minTime: number
    maxTime: number
    read: boolean
    closable: boolean
}
