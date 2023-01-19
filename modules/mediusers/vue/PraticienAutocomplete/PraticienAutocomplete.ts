/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"

/* eslint-disable camelcase */

/**
 * PraticienAutocomplete
 */
@Component
export default class PraticienAutocomplete extends OxVue {
    @Prop()
    private item!: {
        color: string
        _id: number
        _user_first_name: string
        _user_last_name: string
        _initials: string
    }
}
