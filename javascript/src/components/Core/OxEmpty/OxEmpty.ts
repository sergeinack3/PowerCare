/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"

/**
 * OxEmpy
 *
 * Module vide et désactivé. Utilisé en alias pour les components non disponibles.
 */
@Component
export default class OxEmpty extends OxVue {
    private created (): void {
        this.active = false
    }
}
