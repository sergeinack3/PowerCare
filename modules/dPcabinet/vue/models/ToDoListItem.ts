/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
import OxObject from "@/core/models/OxObject"
import { OxAttr } from "@/core/types/OxObjectTypes"

export default class ToDoListItem extends OxObject {
    constructor () {
        super()
        this.type = "todoListItem"
    }

    get libelle (): OxAttr<string> {
        return super.get("libelle")
    }

    set libelle (value: OxAttr<string>) {
        super.set("libelle", value)
    }

    get handledDate (): OxAttr<string> {
        return super.get("handled_date")
    }

    set handledDate (value: OxAttr<string>) {
        super.set("handled_date", value)
    }
}
