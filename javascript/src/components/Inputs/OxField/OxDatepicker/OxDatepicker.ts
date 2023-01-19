/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Prevent TS check, Need Oxify Ref
// @ts-nocheck

import { Component, Prop, Watch } from "vue-property-decorator"
import OxDatePicker from "@/components/Inputs/OxDatePicker/OxDatePicker.vue"
import { OxDate, OxTextField, OxButton, OxIconCore } from "oxify"
import OxVue from "@/components/Core/OxVue"

/**
 * OxDatepicker
 *
 * Composant de champ de saisie de date
 */
@Component({ components: { OxDatePicker, OxTextField, OxButton, OxVue } })
export default class OxDatepicker extends OxTextField {
    @Prop({ default: "date" })
    private format!: "date" | "time" | "datetime" | "month"

    @Prop({ default: "" })
    private defaultDate!: string

    @Prop({ default: "" })
    private defaultTime!: string

    @Prop({ default: true })
    private showNow!: boolean

    @Prop({ default: true })
    private showToday!: boolean

    private tmpDate = ""
    private tmpTime = ""
    private modal = false
    private modalMode: "date" | "time" = "date"

    /**
     * Récupération du label de date
     *
     * @return {string}
     */
    private get dateValue (): string {
        if (this.mutatedValue === "") {
            return ""
        }
        const dateView = OxDate.formatStatic(
            new Date((this.format === "time" ? OxDate.getYMD(new Date()) + " " : "") + this.mutatedValue),
            this.format
        )
        if (dateView.indexOf("NaN") >= 0) {
            return ""
        }

        return decodeURIComponent(escape(dateView))
    }

    /**
     * Récupération de l'icône adaptée au format de date
     *
     * @return {string}
     */
    private get calendarIcon (): string {
        return OxIconCore.get(this.format === "time" ? "time" : "calendar")
    }

    /**
     * Récupération du type de date
     *
     * @return {string}
     */
    private get type (): string {
        return this.format === "month" ? "month" : "date"
    }

    /**
     * Mise à jour de la valeur courante
     * @Watch value
     */
    @Watch("value")
    protected updateMutatedValue (): void {
        this.setMutatedValue(this.value ? this.value.toString() : "")
        this.resetModalMode()
    }

    /**
     * Mise à jour de la valeur depuis un changement de format
     * @Watch format
     */
    @Watch("format")
    protected updateFormat (): void {
        this.updateMutatedValue()
    }

    /**
     * Changement du mode de saisie de date
     * @Watch modal
     */
    @Watch("modal")
    private resetModalMode (): void {
        if (!this.modal) {
            return
        }
        this.modalMode = this.format === "time" ? "time" : "date"
    }

    /**
     * Application d'un changement de valeur saisi
     * @param {string} value - Valeur saisie
     */
    private setMutatedValue (value: string): void {
        if (this.format === "date" || this.format === "month") {
            this.mutatedValue = value
            this.tmpDate = this.mutatedValue ? this.mutatedValue : this.defaultDate
        }
        else if (this.format === "time") {
            this.mutatedValue = value
            this.tmpTime = this.mutatedValue ? this.mutatedValue : this.defaultTime
        }
        else if (this.format === "datetime") {
            this.mutatedValue = value
            if (!this.mutatedValue) {
                this.tmpDate = this.defaultDate ? this.defaultDate : ""
                this.tmpTime = this.defaultTime ? this.defaultTime : ""
                if (this.defaultDate && this.defaultTime) {
                    value = this.defaultDate + " " + this.defaultTime
                }
            }
            if (value && value.length) {
                this.tmpDate = value.length >= 10 ? value.substr(0, 10) : ""
                this.tmpTime = value.length >= 16 ? value.substr(11, 10) : ""
            }
        }

        if (this.tmpDate === "") {
            this.tmpDate = OxDate.getYMD(new Date())
        }
        if (this.tmpTime === "") {
            this.tmpTime = OxDate.getHms(new Date())
        }
    }

    /**
     * Application de la date du jour
     */
    private today (): void {
        this.tmpDate = OxDate.getYMD(new Date())
    }

    /**
     * Application du temp courant
     */
    private now (): void {
        this.tmpTime = OxDate.getHms(new Date())
    }

    /**
     * Changement de date saisie et remontée de la nouvelle valeur
     */
    private updateDate (): void {
        this.modal = false
        this.mutatedValue = ((this.format === "date" || this.format === "month" || this.format === "datetime") ? this.tmpDate : "") +
            ((this.format === "datetime") ? " " : "") +
            ((this.format === "datetime" || this.format === "time") ? this.tmpTime : "")
        this.$emit("change", this.mutatedValue)
    }

    /**
     * Sélection de l'année
     * @param {string} year - Année sélectionnée
     */
    private selectYear (year: string): void {
        const date = new Date(this.tmpDate)
        date.setFullYear(parseInt(year))
        this.tmpDate = OxDate.getYMD(date)
    }

    /**
     * Sélection du mois
     * @param {string} month - Mois sélectionné
     */
    private selectMonth (month: string): void {
        const date = new Date(this.tmpDate)
        date.setMonth(parseInt(month.split("-")[1]) - 1)
        this.tmpDate = OxDate.getYMD(date)
    }

    /**
     * Sélection d'un jour
     * @param {string} day - Jour sélectionné
     */
    private selectDate (day: string): void {
        this.tmpDate = day
    }

    /**
     * Sélection d'une heure
     * @param {string} hour - Heure sélectionnée
     */
    private selectHour (hour: string): void {
        const date = new Date(this.tmpDate + " " + this.tmpTime)
        date.setHours(parseInt(hour))
        this.tmpTime = OxDate.getHms(date)
    }

    /**
     * Sélection d'une minute
     * @param {string} minute - Minute sélectionnée
     */
    private selectMinute (minute: string): void {
        const date = new Date(this.tmpDate + " " + this.tmpTime)
        date.setMinutes(parseInt(minute))
        this.tmpTime = OxDate.getHms(date)
    }

    private tr (trad: string): string {
        return OxVue.str(trad)
    }
}
