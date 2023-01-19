/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop, Watch } from "vue-property-decorator"
import OxSpecsApi from "@/components/Core/OxSpecsApi"
import OxVue from "@/components/Core/OxVue"
import OxDatepicker from "@/components/Inputs/OxField/OxDatepicker/OxDatepicker.vue"
import { OxCheckbox, OxTextField, OxSelect, OxTextarea, OxTooltip } from "oxify"

interface SpecsInterface {
    owner?: string
    type?: string
    fieldset?: string
    autocomplete?: boolean
    placeholder?: string
    notNull?: boolean
    confidential?: boolean
    default?: string
    libelle?: string
    label?: string
    description?: string
    values?: string[]
    datepickerDefaultDate?: string
    datepickerDefaultTime?: string
    datepickerShowNow?: boolean
    datepickerShowToday?: boolean
    translations?: {}
}

/**
 * OxField
 */
@Component({ components: { OxCheckbox, OxTextField, OxDatepicker, OxSelect, OxTextarea, OxTooltip } })
export default class OxField extends OxVue {
    @Prop({ default: "" })
    private resource!: string

    @Prop({ default: "" })
    private field!: string

    @Prop({ default: false })
    private object!: false | object

    @Prop({ default: false })
    private label!: string | false

    @Prop({ default: false })
    private title!: string | false

    @Prop({
        default: () => {
            return {}
        }
    })
    private customSpecs!: SpecsInterface

    @Prop({ default: "" })
    protected message!: string

    @Prop({ default: false })
    private disabled!: boolean

    @Prop({ default: 5 })
    private rows!: number

    @Prop({ default: false })
    private onPrimary!: boolean

    @Prop()
    private value!: string|boolean

    @Prop({
        default: () => {
            return []
        }
    })
    protected rules!: Array<Function>

    private specs: SpecsInterface = {}

    /**
     * Récupération du type de champs
     *
     * @return {string}
     */
    private get type (): string {
        if (!this.specs.type) {
            return "text"
        }
        return this.specs.type
    }

    /**
     * Récupération de la liste des valeurs possibles
     *
     * @return {Array<Object>}
     */
    private get list (): { _id: string; view: string }[] {
        if (!this.specs.values || !this.specs.owner || !this.field) {
            return [{
                _id: "",
                view: ""
            }]
        }

        return this.specs.values.map(
            (_opt) => {
                return {
                    _id: _opt,
                    view: typeof (this.specs.translations) !== "undefined" ? this.specs.translations[_opt] : this.tr(this.specs.owner + "." + this.field + "." + _opt)
                }
            }
        )
    }

    /**
     * Valeur actuelle du champ
     *
     * @return {string|void}
     */
    private get fieldValue (): string | boolean | void {
        if (this.value) {
            return this.value
        }
        else if (!this.object || typeof (this.object[this.field]) === "undefined") {
            return
        }
        return this.object[this.field]
    }

    /**
     * Label du champ
     *
     * @return {string}
     */
    private get fieldLabel (): string {
        if (!this.specs.label) {
            if (!this.specs.libelle) {
                return this.label ? this.label : ""
            }
            return this.specs.libelle
        }
        return this.specs.label
    }

    /**
     * Libellé du champ
     *
     * @return {string}
     */
    private get fieldLibelle (): string {
        if (!this.specs.libelle) {
            if (!this.specs.label) {
                return this.label ? this.label : ""
            }
            return this.specs.label
        }
        return this.specs.libelle
    }

    /**
     * Titre du champ
     *
     * @return {string}
     */
    private get fieldTitle (): string {
        if (!this.specs.description) {
            return this.title ? this.title : ""
        }
        return this.specs.description
    }

    /**
     * Type de date du champ
     *
     * @return {string|void}
     */
    private get dateType (): string | void {
        if (!this.specs.type || ["date", "time", "dateTime", "birthDate"].indexOf(this.specs.type) === -1) {
            return
        }
        if (this.specs.type === "dateTime") {
            return "datetime"
        }
        else if (this.specs.type === "birthDate") {
            return "date"
        }
        return this.specs.type
    }

    /**
     * Le champ est un booléen
     *
     * @return {boolean}
     */
    private get isBoolean (): boolean {
        return this.type === "bool"
    }

    /**
     * Le champ est un champ de texte
     *
     * @return {boolean}
     */
    private get isString (): boolean {
        return [
            "str",
            "numchar",
            "phone",
            "email",
            "code"
        ].indexOf(this.type) >= 0
    }

    /**
     * Le champ est un numéro
     *
     * @return {boolean}
     */
    private get isNum (): boolean {
        return this.type === "num"
    }

    /**
     * Le champ est un montant monétaire
     *
     * @return {boolean}
     */
    private get isCurrency (): boolean {
        return this.type === "currency"
    }

    /**
     * Le champ est un champ large de texte
     *
     * @return {boolean}
     */
    private get isText (): boolean {
        return this.type === "text"
    }

    /**
     * Le champ est une date
     *
     * @return {boolean}
     */
    private get isDate (): boolean {
        return [
            "date",
            "time",
            "dateTime",
            "birthDate"
        ].indexOf(this.type) >= 0
    }

    /**
     * Le champ est une liste
     *
     * @return {boolean}
     */
    private get isList (): boolean {
        return this.type === "enum"
    }

    /**
     * Le champ est obligatoire
     *
     * @return {boolean}
     */
    private get isNotNull (): boolean {
        return !!this.specs.notNull
    }

    /**
     * Le champ est un autocomplete
     *
     * @return {boolean}
     */
    private get hasAutocomplete (): boolean {
        return !!this.specs.autocomplete
    }

    /**
     * Valeur par défaut
     *
     * @return {string|boolean|void}
     */
    private get defaultValue (): string | void | boolean {
        const val = this.fieldValue === undefined ? this.specs.default : this.fieldValue
        if (!this.isBoolean) {
            return val
        }
        return val && val !== "0"
    }

    /**
     * Description du champ
     *
     * @return {string|boolean}
     */
    private get fieldLongLabel (): string | boolean {
        return this.specs.description ? this.specs.description : false
    }

    /**
     * Date par défaut
     *
     * @return {string}
     */
    private get defaultDate (): string {
        return this.specs && this.specs.datepickerDefaultDate ? this.specs.datepickerDefaultDate : ""
    }

    /**
     * Temp par défaut
     *
     * @return {string}
     */
    private get defaultTime (): string {
        return this.specs && this.specs.datepickerDefaultTime ? this.specs.datepickerDefaultTime : ""
    }

    /**
     * Génère les rules du field en fonction de ses specs
     *
     * @return {Array}
     */
    private get fieldRules (): Array<Function> {
        const rules: Array<Function> = []
        if (this.specs.notNull) {
            rules.push(v => (!!v || OxVue.str("Missing-field")))
        }
        this.rules.forEach(
            (fun) => {
                rules.push(fun)
            }
        )
        return rules
    }

    /**
     * Mise à jour des spécifications en fonction des specifications personnalisées
     */
    @Watch("customSpecs")
    private customSpecsWatcher (): void {
        this.updateLocalSpec()
    }

    /**
     * Mise à jour des spécifications en fonction de la ressource renseignée
     */
    @Watch("resource")
    private resourceWatcher (): void {
        this.updateLocalSpec()
    }

    /**
     * Mise à jour des spécifications en fonction du champ renseigné
     */
    @Watch("field")
    private fieldWatcher (): void {
        this.updateLocalSpec()
    }

    /**
     * Mise à jour des spécifications en fonction de l'objet renseigné
     */
    @Watch("object")
    private objectWatcher (): void {
        this.updateLocalSpec()
    }

    /**
     * Composant monté
     *
     * @return {Promise<void>}
     */
    protected async mounted (): Promise<void> {
        this.$nextTick(() => {
            this.updateLocalSpec()
        })
    }

    /**
     * Mise à jour des spécifications courrantes du composant
     */
    public updateLocalSpec (): void {
        let spec: false | { specs: object} = { specs: {} }
        if (this.field && this.resource) {
            spec = OxSpecsApi.get(this.resource, this.field) as false | { specs: object}
            if (!spec) {
                spec = { specs: {} }
            }
        }

        this.specs = Object.assign(
            spec.specs,
            this.customSpecs
        )
    }

    /**
     * Remontée du changement de valeur
     * @param {string|boolean} value - Nouvelle valeur
     */
    private change (value: string|boolean): void {
        this.$emit("change", value)
    }
}
