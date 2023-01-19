/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { Praticien } from "@modules/mediusers/vue/Models/PraticienModel"

/**
 * PatientChip
 */
@Component
export default class PraticienChip extends OxVue {
    @Prop()
    private praticien!: Praticien

    @Prop({ default: false })
    private small!: boolean

    private get color (): string {
        return "#" + this.praticien._color
    }

    private get initials (): string {
        return this.praticien.initials
    }

    private get name (): string {
        return (this.praticien._user_first_name ? this.praticien._user_first_name + " " : "") + this.praticien._user_last_name
    }
}
