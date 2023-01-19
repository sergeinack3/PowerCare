/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { DataTableHeader } from "vuetify"

export interface OxDatagridColumn extends DataTableHeader {
    filterValues?: string | string[]
}
