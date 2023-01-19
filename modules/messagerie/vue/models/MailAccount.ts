/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

export default class MailAccount {
    public static MAILS = ["medimail", "apicrypt", "mailiz", "usermail"]

    public libelle: string
    public mailbox: string
    public accountGuid: string

    constructor (libelle: string, mailbox: string, accountGuid: string) {
        this.libelle = libelle
        this.mailbox = mailbox
        this.accountGuid = accountGuid
    }
}
