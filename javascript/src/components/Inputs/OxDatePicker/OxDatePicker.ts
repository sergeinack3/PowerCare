/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"

/**
 * OxButton
 *
 * @todo: Supprimer la classes et ses appels (OxDatepicker)
 *
 * Composant de bouton
 */
@Component
export default class OxDatePicker extends OxVue {
    // Date
    @Prop({ default: "" })
    private date!: string

    @Prop({ default: "date" })
    private format!: "date" | "datetime" | "time"

    private focused = false
    private form!: HTMLFormElement

    private mounted (): void {
        this.form = (this.$refs.form as HTMLFormElement)
        this.form.input.onchange = this.select
        /* eslint-disable  @typescript-eslint/ban-ts-comment */
        // @ts-ignore
        if (!window.Calendar || !window.Calendar.regField) {
            console.warn("Calendar object not loaded")
        }
        /* eslint-disable  @typescript-eslint/ban-ts-comment */
        // @ts-ignore
        window.Calendar.regField(
            this.form.input,
            null,
            {
                noView: true,
                datePicker: this.isDateFormat,
                timePicker: this.isTimeFormat,
                altFormat: (this.isDateFormat ? "yyyy-MM-dd" : "") + (this.isDateTimeFormat ? " " : "") + (this.isTimeFormat ? "HH:mm:00" : ""),
                onSelect: () => {
                    this.select()
                }
            }
        )
    }

    private get isDateFormat () {
        return (this.format === "date" || this.format === "datetime")
    }

    private get isDateTimeFormat () {
        return this.format === "datetime"
    }

    private get isTimeFormat () {
        return (this.format === "time" || this.format === "datetime")
    }

    private select () {
        this.$emit("change", this.form.input.value)
    }

    public display (): void {
        (this.form.querySelector("i") as HTMLElement).click()
    }

    private get currentDate (): string {
        return this.date
    }

    /**
     * Remontée de l'événement click
     */
    private updateDate () {
        this.$emit("change")
    }
}
