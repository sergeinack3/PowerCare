/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxProviderCore from "@/components/Core/OxProviderCore"
import OxDatePaginationCore from "@/components/Core/OxDatePaginationCore"
import { ApiTranslatedResponse } from "@/components/Models/ApiResponseModel"

/**
 * OxPaginationCore
 *
 * Une fois un provider de données renseigné, la classe gère automatiquement les liens pour proposer une pagination.
 */
export default class OxPaginationCore {
    // Provider de données associé
    private provider!: OxProviderCore

    // Dans le cas d'une pagination par date, une OxDatePaginationCore intermédiaire est nécessaire
    public datePagination!: OxDatePaginationCore | false

    public filters = {}

    public loading = false

    // Liens de la pagination. Se met à jour en fonction du provider
    private links: { next: string; first: string; last: string; previous: string; self: string } = {
        next: "",
        first: "",
        last: "",
        previous: "",
        self: ""
    }

    // Page courante
    public currentPage = 0
    // Tri courant
    public currentSort = ""

    /**
     * @inheritDoc
     */
    constructor (provider?: OxProviderCore, datePagination?: OxDatePaginationCore) {
        this.provider = provider ?? new OxProviderCore()
        this.datePagination = datePagination ?? false
        return this
    }

    /**
     * Retour basic de données depuis le provider de données.
     *
     * @return {Promise<object>}
     */
    public async getData (): Promise<object> {
        return this.dataTraitment(await this.launchApi("", this.genFiltersParam()))
    }

    /**
     * Fixe manuellement la page courante
     * Actuellement disponible que pour la pagination en mode Date (nécessite une instance de OxDatePaginationCore)
     *
     * @param {number|string} page
     */
    public setPage (page: number | string): void {
        if (this.datePagination) {
            this.datePagination.currentDate = new Date(page)
        }
        // unavailable for the normal mode
    }

    /**
     * Traitement des données annexes depuis le provider (links, curseur) puis retourne les données (data)
     *
     * @param {any} data object Retour d'un provider de données
     *
     * @return {Object}
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any */
    private dataTraitment (data: any): object {
        if (data.links) {
            this.setLinks(data.links)
        }
        this.setCursor()
        return Array.isArray(data.data) ? data.data : data.data.attributes
    }

    /**
     * Mise à jour des links de la paginations
     *
     * @param {object} providerLinks Liens récupérées depuis un retour provider
     */
    private setLinks (providerLinks: { next?: string; first?: string; last?: string; prev?: string; self?: string }): void {
        this.links.next = providerLinks.next ?? ""
        this.links.first = providerLinks.first ?? ""
        this.links.last = providerLinks.last ?? ""
        this.links.previous = providerLinks.prev ?? ""
        this.links.self = providerLinks.self ?? ""
    }

    /**
     * Mise à jour des curseurs de la paginations
     *
     * @param providerCursor object Curseurs récupérés depuis un retour provider
     */
    private setCursor (): void {
        this.currentPage = this.extractCursorFromLink(this.links.self)
    }

    /**
     * Récupération des curseurs depuis  le lien d'une ressource
     * @param {string} link - Lien de la ressource
     *
     * @return {number}
     */
    private extractCursorFromLink (link: string): number {
        const limitPos = link.match(/[?&]limit=[0-9]*/g)
        const offsetPos = link.match(/[?&]offset=[0-9]*/g)
        if (!limitPos || !offsetPos) {
            return 0
        }
        const offset = offsetPos[0].substr(offsetPos[0].indexOf("=") + 1)
        const limit = limitPos[0].substr(limitPos[0].indexOf("=") + 1)
        if (offset === "0" || limit === "0") {
            return 1
        }

        return (Math.ceil(parseInt(offset) / parseInt(limit)) + 1)
    }

    /**
     * Mise en chargement et récupération de données
     * @param {string} url - Lien de la ressource à récupérer
     * @param {object} params - Paramètres de l'appel
     *
     * @return {Promise<ApiTranslatedResponse>}
     */
    private async launchApi (url: string, params?: {}): Promise<ApiTranslatedResponse> {
        this.loading = true
        const data = await this.provider.getApi(url, params) as ApiTranslatedResponse
        this.loading = false
        return data
    }

    /**
     * Rafraichissement des données (application du lien Self)
     *
     * @return {Promise<any>}
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any */
    public async self (): Promise<any> {
        if (!this.hasSelf()) {
            return
        }
        return this.dataTraitment(await this.launchApi(this.getSelfUrl(), this.genSelfParam()))
    }

    /**
     * Récupération des données de la page suivante (application du lien Next)
     *
     * @return {Promise<any>}
     */
    public async next (): Promise<any> {
        if (!this.hasNext()) {
            return
        }
        return this.dataTraitment(await this.launchApi(this.getNextUrl(), this.genNextParam()))
    }

    /**
     * Récupération des données de la page précèdente (application du lien Prev)
     *
     * @return {Promise<any>}
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any */
    public async previous (): Promise<any> {
        if (!this.hasPrevious()) {
            return
        }
        return this.dataTraitment(await this.launchApi(this.getPreviousUrl(), this.genPreviousParam()))
    }

    /**
     * Récupération des données de la dernière page (application du lien Last)
     *
     * @return {Promise<any>}
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any */
    public async last (): Promise<any> {
        if (!this.hasLast()) {
            return
        }
        return this.dataTraitment(await this.launchApi(this.getLastUrl()))
    }

    /**
     * Récupération des données de la première page (application du lien First)
     *
     * @return {Promise<any>}
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any */
    public async first (): Promise<any> {
        if (!this.hasFirst()) {
            return
        }
        return this.dataTraitment(await this.launchApi(this.getFirstUrl()))
    }

    /**
     * Retourne la disponibilité du lien vers la page suivante
     *
     * @return {boolean}
     */
    public hasSelf (): boolean {
        return typeof (this.links.self) !== "undefined" &&
            this.links.self !== ""
    }

    /**
     * Retourne la disponibilité du lien vers la page suivante
     *
     * @return {boolean}
     */
    public hasNext (): boolean {
        return typeof (this.links.next) !== "undefined" &&
            this.links.next !== "" &&
            this.links.next !== this.links.self
    }

    /**
     * Retourne la disponibilité du lien vers la page précèdente
     *
     * @return {boolean}
     */
    public hasPrevious (): boolean {
        return typeof (this.links.previous) !== "undefined" &&
            this.links.previous !== "" &&
            this.currentPage > 1
    }

    /**
     * Retourne la disponibilité du lien vers la première page
     *
     * @return {boolean}
     */
    public hasFirst (): boolean {
        return typeof (this.links.first) !== "undefined" &&
            this.links.first !== "" &&
            this.currentPage > 1
    }

    /**
     * Retourne la disponibilité du lien vers la dernière page
     *
     * @return {boolean}
     */
    public hasLast (): boolean {
        return typeof (this.links.last) !== "undefined" &&
            this.links.last !== "" &&
            this.links.last !== this.links.self
    }

    /**
     * Récupération de la page de rafraichissement (self)
     *
     * @return {string}
     */
    private getSelfUrl (): string {
        return this.links.self
    }

    /**
     * Récupération de la page suivante
     *
     * @return {string}
     */
    public getNextUrl (): string {
        // private getNextUrl(): string {
        if (this.datePagination) {
            return this.links.self
        }
        return this.hasNext() ? this.links.next : ""
    }

    /**
     * Récupération de la page précèdente
     *
     * @return {string}
     */
    private getPreviousUrl (): string {
        if (this.datePagination) {
            return this.links.self
        }
        return this.hasPrevious() ? this.links.previous : ""
    }

    /**
     * Récupération de la première page
     *
     * @return {string}
     */
    private getFirstUrl (): string {
        return this.hasFirst() ? this.links.first : ""
    }

    /**
     * Récupération de la dernière page
     *
     * @return {string}
     */
    private getLastUrl (): string {
        return this.hasLast() ? this.links.last : ""
    }

    /**
     * Récupération des paramètres pour afficher la page rafraichie
     *
     * @return {Object}
     */
    private genSelfParam (): object {
        return Object.assign(
            this.datePagination ? this.datePagination.genSelfParam() : {},
            this.genFiltersParam()
        )
    }

    /**
     * Récupération des paramètres pour afficher la page suivante
     *
     * @return {Object}
     */
    private genNextParam (): object {
        return Object.assign(
            this.datePagination ? this.datePagination.genNextParam() : {},
            this.genFiltersParam()
        )
    }

    /**
     * Récupération des paramètres pour afficher la page précèdente
     *
     * @return {Object}
     */
    private genPreviousParam (): object {
        return Object.assign(
            this.datePagination ? this.datePagination.genPreviousParam() : {},
            this.genFiltersParam()
        )
    }

    /**
     * Récupération des paramètres de filtre supplémentaires
     *
     * @return {Object}
     */
    private genFiltersParam (): object {
        Object.keys(this.filters).forEach(
            (_filterKey) => {
                const _filterValue = this.filters[_filterKey]
                if (typeof (_filterValue) !== "boolean") {
                    return
                }
                this.filters[_filterKey] = _filterValue ? 1 : 0
            }
        )
        return this.filters
    }

    /**
     * Déclenchement de l'évenement Scroll d'un conteneur de données
     * @param {Object} container - Conteneur de donnée émettent l'évenement
     *
     * @return {boolean}
     */
    public triggerContainerScroll (container: HTMLDivElement): boolean {
        return container && (container.scrollHeight < (container.offsetHeight + container.scrollTop))
    }
}
