/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxObject from "@/core/models/OxObject"
import { OxAttrNullable } from "@/core/types/OxObjectTypes"

export default class SampleCategory extends OxObject {
    constructor () {
        super()
        this.type = "sample_category"
    }

    get name (): string {
        if (!this.attributes.name) {
            return ""
        }
        return this.attributes.name.charAt(0).toUpperCase() + this.attributes.name.slice(1)
    }

    get active (): OxAttrNullable<boolean> {
        return this.attributes.active
    }

    get color (): OxAttrNullable<string> {
        return this.attributes.color
    }
}
