/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
import OxObject from "@/core/models/OxObject"
import { OxAttr } from "@/core/types/OxObjectTypes"

export default class Patient extends OxObject {
    constructor () {
        super()
        this.type = "patient"
    }

    get nom (): OxAttr<string> {
        return super.get("nom")
    }

    get prenom (): OxAttr<string> {
        return super.get("prenom")
    }

    get shortView (): string {
        let prenom = ""
        if (this.prenom) {
            prenom = " " + this.prenom.charAt(0).toUpperCase() + this.prenom.slice(1).toLowerCase()
        }
        return this.nom + prenom
    }

    get civilite (): OxAttr<string> {
        return super.get("civilite")
    }

    get sexe (): OxAttr<string> {
        return super.get("sexe")
    }

    get guid (): string {
        return "CPatient-" + super.id
    }
}
