/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { Alert } from "@/components/Core/OxAlert/OxAlertModel"
import OxAlertManagerApi from "@/components/Core/OxAlert/OxAlertManagerApi"
import { OxButton } from "oxify"

/**
 * OxAlert
 */
@Component({ components: { OxButton } })
export default class OxAlert extends OxVue {
    @Prop()
    private alertManager!: OxAlertManagerApi

    /**
     * Récupération de l'alerte courante
     *
     * @return { Alert }
     */
    private get currentAlert (): Alert | false {
        if (!this.alertManager) {
            return false
        }
        return this.alertManager.getAlert()
    }

    /**
     * Affichage de l'alerte
     *
     * @return { boolean }
     */
    private get showAlert (): boolean {
        return !!this.currentAlert
    }

    /**
     * Récupération du message de l'alerte
     *
     * @return { string }
     */
    private get alertMsg (): string {
        return this.currentAlert ? this.currentAlert.msg : ""
    }

    /**
     * Récupération du label du bouton "ok"
     *
     * @return { string }
     */
    private get okLabel (): string {
        return this.currentAlert ? this.currentAlert.okOpt.label : ""
    }

    /**
     * Affichage du bouton "not ok"
     *
     * @return { boolean }
     */
    private get showNok (): boolean {
        return this.currentAlert ? !!this.currentAlert.nokOpt : false
    }

    /**
     * Récupération du label du bouton "non ok"
     *
     * @return { string }
     */
    private get nokLabel (): string {
        return this.currentAlert ? this.currentAlert.nokOpt.label : ""
    }

    /**
     * Exécution du callback du bouton "ok"
     */
    private onOkClick (): void {
        if (this.currentAlert && this.currentAlert.okOpt.callback) {
            this.currentAlert.okOpt.callback()
        }
        this.alertManager.unsetAlert()
    }

    /**
     * Exécution du callback du bouton "non ok"
     * @private
     */
    private onNokClick (): void {
        if (this.currentAlert && this.currentAlert.nokOpt && this.currentAlert.nokOpt.callback) {
            this.currentAlert.nokOpt.callback()
        }
        this.alertManager.unsetAlert()
    }
}
