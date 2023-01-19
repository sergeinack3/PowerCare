/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { OxIcon } from "oxify"

/**
 * OxModuleIcon
 * Composant d'icone des modules
 */
@Component({ components: { OxIcon } })
export default class OxModuleIcon extends OxVue {
    @Prop({ default: "" })
    private moduleName!: string

    @Prop({ default: "" })
    private moduleCategory!: string

    @Prop({ default: false })
    private small!: boolean

    private get icon () {
        let icon: string
        try {
            icon = require(`./../../../../../../modules/${this.moduleName}/images/icon.svg`)
        }
        catch {
            icon = ""
        }
        return icon
    }

    private get showCategoryIcon () {
        return this.icon === ""
    }

    private get iconSize (): number {
        return this.small ? 20 : 26
    }

    private get getCategoryIcon (): string {
        switch (this.moduleCategory) {
        case "interoperabilite": return "shareVariant"
        case "import": return "databaseImport"
        case "dossier_patient": return "badgeAccountHorizontal"
        case "circuit_patient": return "hospitalBuilding"
        case "erp": return "packageVariant"
        case "administratif": return "tune"
        case "referentiel": return "compass"
        case "plateau_technique": return "heartPulse"
        case "systeme": return "tools"
        case "parametrage": return "cogs"
        case "reporting": return "chartPie"
        default: return "bookmark"
        }
    }
}
