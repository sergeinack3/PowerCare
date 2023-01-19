/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxLoading from "@/components/Core/OxLoading/OxLoading"
import { OxTest } from "oxify"
import { Wrapper } from "@vue/test-utils"
import OxVueApi from "@/components/Core/OxVueApi"

/**
 * Test pour la classe OxLoading
 */
export default class OxLoadingTest extends OxTest {
    protected component = OxLoading

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<OxLoading> {
        return super.mountComponent(props) as Wrapper<OxLoading>
    }

    /**
     * Test chargement forcé
     */
    public testLoading (): void {
        OxVueApi.load()
        this.assertTrue(OxVueApi.loading())
        this.assertTrue(
            this.mountComponent({}).find(".OxLoading.displayed").exists()
        )
    }

    /**
     * Test chargement forcé
     */
    public testForceLoading (): void {
        this.assertTrue(
            this.mountComponent({ forceLoad: true }).find(".OxLoading.displayed").exists()
        )
    }

    /**
     * Reset de tous les chargements
     */
    public testReset (): void {
        OxVueApi.load()
        OxLoading.unloadAll()
        this.assertFalse(OxVueApi.loading())
        this.assertFalse(
            this.mountComponent({}).find(".OxLoading.displayed").exists()
        )
    }
}

(new OxLoadingTest()).launchTests()
