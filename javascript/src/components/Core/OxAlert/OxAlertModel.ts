/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

export interface Alert {
    msg: string
    okOpt: AlertOpt
    nokOpt: AlertOpt
}

export interface AlertOpt {
    callback: Function|false
    label: string
}
