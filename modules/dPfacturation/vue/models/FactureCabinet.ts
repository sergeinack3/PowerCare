/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
import OxObject from "@/core/models/OxObject"
import { OxAttr } from "@/core/types/OxObjectTypes"

export default class FactureCabinet extends OxObject {
    constructor () {
        super()
        this.type = "factureCabinet"
    }

    get duPatient (): OxAttr<number> {
        return super.get("du_patient")
    }

    set duPatient (number: OxAttr<number>) {
        super.set("du_patient", number)
    }

    get duRestantPatient (): number {
        return super.get("_du_restant_patient") ?? 0
    }

    set duRestantPatient (number) {
        super.set("_du_restant_patient", number ?? 0)
    }

    get ouverture (): OxAttr<string> {
        return super.get("ouverture")
    }
}
