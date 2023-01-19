/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
import OxObject from "@/core/models/OxObject"
import { OxAttr } from "@/core/types/OxObjectTypes"

export default class MailCounter extends OxObject {
    public static MAILS = ["medimail", "apicrypt", "mailiz", "usermail"]

    constructor () {
        super()
        this.type = "mail_counter"
    }

    get unread (): OxAttr<number> {
        return super.get("unread")
    }

    get mailbox (): OxAttr<string> {
        return super.get("mailbox")
    }

    get svgPath (): string {
        return "modules/messagerie/images/" + this.mailbox + "Icon.svg"
    }
}
