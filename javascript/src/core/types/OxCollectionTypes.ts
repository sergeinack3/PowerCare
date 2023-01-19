/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

export interface OxCollectionLinks {
    self?: string
    first?: string
    last?: string
    next?: string
    prev?: string
}

export interface OxCollectionMeta {
    count: number
    total: number
}
