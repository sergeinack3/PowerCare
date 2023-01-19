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
 * Une fois un provider de donn�es renseign�, la classe g�re automatiquement les liens pour proposer une pagination.
 */
export default class OxPaginationCore {
    // Provider de donn�es associ�
    private provider!: OxProviderCore

    // Dans le cas d'une pagination par date, une OxDatePaginationCore interm�diaire est n�cessaire
    public datePagination!: OxDatePaginationCore | false

    public filters = {}

    public loading = false

    // Liens de la pagination. Se met � jour en fonction du provider
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
     * Retour basic de donn�es depuis le provider de donn�es.
     *
     * @return {Promise<object>}
     */
    public async getData (): Promise<object> {
        return this.dataTraitment(await this.launchApi("", this.genFiltersParam()))
    }

    /**
     * Fixe manuellement la page courante
     * Actuellement disponible que pour la pagination en mode Date (n�cessite une instance de OxDatePaginationCore)
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
     * Traitement des donn�es annexes depuis le provider (links, curseur) puis retourne les donn�es (data)
     *
     * @param {any} data object Retour d'un provider de donn�es
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
     * Mise � jour des links de la paginations
     *
     * @param {object} providerLinks Liens r�cup�r�es depuis un retour provider
     */
    private setLinks (providerLinks: { next?: string; first?: string; last?: string; prev?: string; self?: string }): void {
        this.links.next = providerLinks.next ?? ""
        this.links.first = providerLinks.first ?? ""
        this.links.last = providerLinks.last ?? ""
        this.links.previous = providerLinks.prev ?? ""
        this.links.self = providerLinks.self ?? ""
    }

    /**
     * Mise � jour des curseurs de la paginations
     *
     * @param providerCursor object Curseurs r�cup�r�s depuis un retour provider
     */
    private setCursor (): void {
        this.currentPage = this.extractCursorFromLink(this.links.self)
    }

    /**
     * R�cup�ration des curseurs depuis  le lien d'une ressource
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
     * Mise en chargement et r�cup�ration de donn�es
     * @param {string} url - Lien de la ressource � r�cup�rer
     * @param {object} params - Param�tres de l'appel
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
     * Rafraichissement des donn�es (application du lien Self)
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
     * R�cup�ration des donn�es de la page suivante (application du lien Next)
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
     * R�cup�ration des donn�es de la page pr�c�dente (application du lien Prev)
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
     * R�cup�ration des donn�es de la derni�re page (application du lien Last)
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
     * R�cup�ration des donn�es de la premi�re page (application du lien First)
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
     * Retourne la disponibilit� du lien vers la page suivante
     *
     * @return {boolean}
     */
    public hasSelf (): boolean {
        return typeof (this.links.self) !== "undefined" &&
            this.links.self !== ""
    }

    /**
     * Retourne la disponibilit� du lien vers la page suivante
     *
     * @return {boolean}
     */
    public hasNext (): boolean {
        return typeof (this.links.next) !== "undefined" &&
            this.links.next !== "" &&
            this.links.next !== this.links.self
    }

    /**
     * Retourne la disponibilit� du lien vers la page pr�c�dente
     *
     * @return {boolean}
     */
    public hasPrevious (): boolean {
        return typeof (this.links.previous) !== "undefined" &&
            this.links.previous !== "" &&
            this.currentPage > 1
    }

    /**
     * Retourne la disponibilit� du lien vers la premi�re page
     *
     * @return {boolean}
     */
    public hasFirst (): boolean {
        return typeof (this.links.first) !== "undefined" &&
            this.links.first !== "" &&
            this.currentPage > 1
    }

    /**
     * Retourne la disponibilit� du lien vers la derni�re page
     *
     * @return {boolean}
     */
    public hasLast (): boolean {
        return typeof (this.links.last) !== "undefined" &&
            this.links.last !== "" &&
            this.links.last !== this.links.self
    }

    /**
     * R�cup�ration de la page de rafraichissement (self)
     *
     * @return {string}
     */
    private getSelfUrl (): string {
        return this.links.self
    }

    /**
     * R�cup�ration de la page suivante
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
     * R�cup�ration de la page pr�c�dente
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
     * R�cup�ration de la premi�re page
     *
     * @return {string}
     */
    private getFirstUrl (): string {
        return this.hasFirst() ? this.links.first : ""
    }

    /**
     * R�cup�ration de la derni�re page
     *
     * @return {string}
     */
    private getLastUrl (): string {
        return this.hasLast() ? this.links.last : ""
    }

    /**
     * R�cup�ration des param�tres pour afficher la page rafraichie
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
     * R�cup�ration des param�tres pour afficher la page suivante
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
     * R�cup�ration des param�tres pour afficher la page pr�c�dente
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
     * R�cup�ration des param�tres de filtre suppl�mentaires
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
     * D�clenchement de l'�venement Scroll d'un conteneur de donn�es
     * @param {Object} container - Conteneur de donn�e �mettent l'�venement
     *
     * @return {boolean}
     */
    public triggerContainerScroll (container: HTMLDivElement): boolean {
        return container && (container.scrollHeight < (container.offsetHeight + container.scrollTop))
    }
}
