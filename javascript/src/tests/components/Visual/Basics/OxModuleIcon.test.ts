/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import OxModuleIcon from "@/components/Visual/Basics/OxModuleIcon/OxModuleIcon"
import { Wrapper } from "@vue/test-utils"

/**
 * Test pour la classe OxModuleIcon
 */
export default class OxModuleIconTest extends OxTest {
    protected component = OxModuleIcon

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): OxModuleIcon {
        return this.mountComponent(props).vm as OxModuleIcon
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<OxModuleIcon> {
        return super.mountComponent(props) as Wrapper<OxModuleIcon>
    }

    public async testShowCategoryIcon () {
        const oxModuleIcon = this.vueComponent({
            moduleName: "ModuleTest",
            moduleCategory: "CagetoryTest"
        })
        await oxModuleIcon.$nextTick()
        this.assertTrue(this.privateCall(oxModuleIcon, "showCategoryIcon"))
    }

    public async testDefaultIconSize () {
        const oxModuleIcon = this.vueComponent({
            moduleName: "ModuleTest",
            moduleCategory: "CagetoryTest"
        })
        await oxModuleIcon.$nextTick()
        this.assertEqual(this.privateCall(oxModuleIcon, "iconSize"), 26)
    }

    public async testSmallIconSize () {
        const oxModuleIcon = this.vueComponent({
            moduleName: "ModuleTest",
            moduleCategory: "CagetoryTest",
            small: true
        })
        await oxModuleIcon.$nextTick()
        this.assertEqual(this.privateCall(oxModuleIcon, "iconSize"), 20)
    }

    @OxTest.scenarios(
        ["Interop", "interoperabilite", "shareVariant"],
        ["Import", "import", "databaseImport"],
        ["DossierPatient", "dossier_patient", "badgeAccountHorizontal"],
        ["CircuitPatient", "circuit_patient", "hospitalBuilding"],
        ["ERP", "erp", "packageVariant"],
        ["Administratif", "administratif", "tune"],
        ["Referentiel", "referentiel", "compass"],
        ["PlateauTechnique", "plateau_technique", "heartPulse"],
        ["Systeme", "systeme", "tools"],
        ["Parametrage", "parametrage", "cogs"],
        ["Reporting", "reporting", "chartPie"],
        ["Default", "", "bookmark"]
    )
    public async testCategoryIcon (categoryName: string, iconName: string) {
        const oxModuleIcon = this.vueComponent({
            moduleName: "ModuleTest",
            moduleCategory: categoryName,
            small: true
        })
        await oxModuleIcon.$nextTick()
        this.assertEqual(
            this.privateCall(oxModuleIcon, "getCategoryIcon"),
            iconName
        )
    }
}

(new OxModuleIconTest()).launchTests()
