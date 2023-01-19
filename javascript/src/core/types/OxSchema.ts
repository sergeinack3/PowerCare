/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

export interface OxSchema {
    id?: string
    owner: string
    field: string
    type: string
    fieldset: string | null
    autocomplete: string | null
    placeholder: string | null
    notNull: boolean | null
    confidential: string | null
    default: string | null
    values?: [string] | null
    libelle: string
    label: string
    description: string,
    hideDate?: boolean
}
