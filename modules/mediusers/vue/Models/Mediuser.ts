/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
import OxObject from "@/core/models/OxObject"
import { OxAttr } from "@/core/types/OxObjectTypes"

export default class Mediuser extends OxObject {
    constructor () {
        super()
        this.type = "mediuser"
    }

    get initials (): string {
        if (super.get("initials")) {
            return super.get("initials")
        }
        else if (this.firstName && this.lastName) {
            return this.firstName.charAt(0) + this.lastName.charAt(0)
        }

        return ""
    }

    get color (): OxAttr<string> {
        return "#" + super.get("_color")
    }

    get lastName (): OxAttr<string> {
        return super.get("_user_last_name")
    }

    set lastName (value: OxAttr<string>) {
        super.set("_user_last_name", value)
    }

    get firstName (): OxAttr<string> {
        return super.get("_user_first_name")
    }

    set firstName (value: OxAttr<string>) {
        super.set("_user_first_name", value)
    }

    get fullName (): string {
        return (super.get("_user_last_name") ?? "") + (super.get("_user_first_name") ? " " + super.get("_user_first_name") : "")
    }

    get sex (): OxAttr<string> {
        return super.get("_user_sexe")
    }

    set sex (value: OxAttr<string>) {
        super.set("_user_sexe", value)
    }

    get actif (): OxAttr<boolean> {
        return super.get("actif")
    }

    set actif (value: OxAttr<boolean>) {
        super.set("actif", value)
    }

    get debActivite (): OxAttr<string> {
        return super.get("deb_activite")
    }

    set debActivite (value: OxAttr<string>) {
        super.set("deb_activite", value)
    }

    get finActivite (): OxAttr<string> {
        return super.get("fin_activite")
    }

    set finActivite (value: OxAttr<string>) {
        super.set("fin_activite", value)
    }
}
