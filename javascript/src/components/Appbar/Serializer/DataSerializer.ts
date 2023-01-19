/**
 * @package Openxtrem\Core
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { AppbarProp } from "@/components/Appbar/Models/AppbarModel"

/* eslint-disable-next-line @typescript-eslint/no-explicit-any */
export function appbarPropTransformer (prop: AppbarProp): { [key: string]: any, _links: any } {
    return { ...prop.datas, _links: prop.links }
}
