/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxAlert from "@/components/Core/OxAlert/OxAlert"
import { OxTest } from "oxify"
import { Wrapper } from "@vue/test-utils"
import OxAlertManagerApi from "@/components/Core/OxAlert/OxAlertManagerApi"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"

/**
 * Test pour la classe OxAlert
 */
export default class OxAlertTest extends OxTest {
    protected component = OxAlert

    private alertManager = new OxAlertManagerApi(OxStoreCore)

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<OxAlert> {
        return super.mountComponent(props) as Wrapper<OxAlert>
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): OxAlert {
        return this.mountComponent(props).vm as OxAlert
    }

    /**
     * @inheritDoc
     */
    protected afterTest (): void {
        this.alertManager.unsetAlert()
    }

    /**
     * Affichage d'une alerte
     */
    public testDisplayAlert (): void {
        this.alertManager.setAlert(
            "Test",
            { label: "Yes button", callback: false}
        )

        this.assertTrue(
            this.privateCall(
                this.vueComponent({ alertManager: this.alertManager }),
                "showAlert"
            )
        )
    }

    /**
     * Non - affichage d'une alerte
     */
    public testDisplayNotAlert (): void {
        this.assertFalse(
            this.privateCall(
                this.vueComponent({ alertManager: this.alertManager }),
                "showAlert"
            )
        )
    }

    /**
     * Affichage d'un message donné
     */
    public testMessage (): void {
        const labelMsg = "Test"

        this.alertManager.setAlert(
            labelMsg,
            { label: "Yes button", callback: false}
        )

        this.assertEqual(
            this.privateCall(
                this.vueComponent({ alertManager: this.alertManager }),
                "alertMsg"
            ),
            labelMsg
        )
    }

    /**
     * Affichage d'un label donné dans un bouton et callback associé
     */
    public testOkButton (): void {
        const buttonLabel = "Ok label"
        let buttonFlag = 1
        const expectedButtonFlag = 2
        const buttonCallback = () => {
            buttonFlag = expectedButtonFlag
        }
        const alertComponent = this.vueComponent({ alertManager: this.alertManager })

        this.alertManager.setAlert(
            "Test",
            {
                label: buttonLabel,
                callback: buttonCallback
            }
        )

        this.assertEqual(
            this.privateCall(
                alertComponent,
                "okLabel"
            ),
            buttonLabel
        )

        this.privateCall(alertComponent, "onOkClick")
        this.assertEqual(
            buttonFlag,
            expectedButtonFlag
        )
        this.assertFalse(this.privateCall(alertComponent, "showAlert"))
    }

    /**
     * Affichage d'un label donné dans un bouton et callback associé
     */
    public testNotOkButton (): void {
        const buttonLabel = "Not ok label"
        let buttonFlag = 1
        const expectedButtonFlag = 2
        const buttonCallback = () => {
            buttonFlag = expectedButtonFlag
        }
        const alertComponent = this.vueComponent({ alertManager: this.alertManager })

        this.alertManager.setAlert(
            "Test",
            {
                label: "Ok label",
                callback: false
            },
            {
                label: buttonLabel,
                callback: buttonCallback
            }
        )

        this.assertEqual(
            this.privateCall(
                alertComponent,
                "nokLabel"
            ),
            buttonLabel
        )

        this.privateCall(alertComponent, "onNokClick")
        this.assertEqual(
            buttonFlag,
            expectedButtonFlag
        )
        this.assertFalse(this.privateCall(alertComponent, "showAlert"))
    }
}

(new OxAlertTest()).launchTests()
