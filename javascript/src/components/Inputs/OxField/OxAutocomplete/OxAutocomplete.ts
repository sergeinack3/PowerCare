/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop, Watch } from "vue-property-decorator"
import OxProviderCore from "@/components/Core/OxProviderCore"
import { OxFieldStrCore, OxIconCore } from "oxify"
import OxVue from "@/components/Core/OxVue"

/**
 * OxAutocomplete
 *
 * Composant de champ Autocomplete
 */
@Component
export default class OxAutocomplete extends OxFieldStrCore {
    @Prop({ default: false })
    private chips!: boolean

    @Prop({ default: "_id" })
    private itemId!: string

    @Prop({ default: "view" })
    private itemText!: string

    @Prop({ default: "view" })
    private itemView!: string

    @Prop({ default: 0 })
    private minChar!: number

    @Prop({ default: false })
    private multiple!: boolean

    @Prop({ default: false })
    private preFill!: boolean

    @Prop({
        default: () => {
            return []
        }
    })

    /* eslint-disable  @typescript-eslint/no-explicit-any */
    private options!: any[]

    @Prop({ default: false })
    private object!: boolean

    @Prop({
        default: () => {
            return new OxProviderCore()
        }
    })
    private provider!: OxProviderCore

    @Prop({ default: false })
    private useCustomFilter!: boolean

    /* eslint-disable  @typescript-eslint/no-explicit-any */
    private items: any[] = []
    private loading = false
    // @ts-ignore
    private recoverTimer!: NodeJS.Timer
    private recoverTiming = 500
    private search = ""
    private syncValue = ""

    /**
     * Aucune réponses récupérée depuis le dernier appel
     *
     * @return {boolean}
     */
    private get noDataResponse (): boolean {
        return !this.loading && this.search !== "" && this.search !== null
    }

    /**
     * Icone à afficher dans le champ
     *
     * @return {string}
     */
    private get iconSearch (): string {
        if (!this.icon) {
            return OxIconCore.get("search")
        }
        return this.iconName
    }

    /**
     * Synchronisation de la valeur sélectionnée
     * @Watch value
     *
     * @return {Promise<void>}
     */
    @Watch("value")
    private async syncSelectedItem (): Promise<void> {
        this.updateMutatedValue()
        if (this.provider && this.mutatedValue && this.syncValue !== this.mutatedValue) {
            this.loading = true
            this.mutatedValue = this.mutatedValue.toString()
            this.items = [(await this.provider.getAutocompleteById(this.mutatedValue.toString()))]
            this.syncValue = this.mutatedValue
            this.loading = false
        }
    }

    /**
     * Mise à jour des items proposés dans la liste
     * @Watch search
     *
     * @return {Promise<void>}
     */
    @Watch("search")
    private async updateItems (): Promise<void> {
        if (this.options.length || !this.search || this.search.length < this.minChar) {
            if (!this.search || this.search.length < this.minChar) {
                this.items = []
            }
            return
        }
        this.loading = true
        if (this.recoverTimer) {
            window.clearTimeout(this.recoverTimer)
        }
        this.recoverTimer = setTimeout(
            () => {
                this.itemCall()
            },
            this.recoverTiming
        )
    }

    /**
     * Composant créé
     *
     * @return {Promise<void>}
     */
    protected async created (): Promise<void> {
        await this.syncSelectedItem()

        if (this.preFill) {
            this.items = await this.provider.getAutocomplete()
        }
    }

    /**
     * Mise à jour effective de la liste des items proposés
     *
     * @return {Promise<void>}
     */
    private async itemCall (): Promise<void> {
        this.items = await this.provider.getAutocomplete(this.search)
        this.loading = false
    }

    /**
     * Remontée de l'évenement du changement de valeur
     * @param {string} value - Nouvelle valeur
     */
    private changeAuto (value: string): void {
        this.syncValue = value
        this.change(value)
    }

    private tr (trad: string): string {
        return OxVue.str(trad)
    }

    private customFilter (item, queryText, itemText) {
        const itemTextSanitized = itemText.toLocaleLowerCase().normalize("NFD").replace(/([\u0300-\u036f]|[^0-9a-zA-Z])/g, "")
        const queryTextSanitized = queryText.toLocaleLowerCase().normalize("NFD").replace(/([\u0300-\u036f]|[^0-9a-zA-Z])/g, "")
        return itemTextSanitized.indexOf(queryTextSanitized) > -1
    }

    private defaultFilter (item, queryText, itemText) {
        return itemText.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
    }
}
