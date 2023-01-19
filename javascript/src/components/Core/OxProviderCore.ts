/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import axios, { AxiosStatic } from "axios"
import OxVueApi from "@/components/Core/OxVueApi"
import OxSerializerCore from "@/components/Core/OxSerializerCore"
import { ApiParameters, ApiResponse, ApiTranslatedResponse, BulkElement, BulkResponse } from "@/components/Models/ApiResponseModel"
import { OxDate } from "oxify"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"
import OxBulkCore from "@/components/Core/OxBulkCore"
import OxNotifyManagerApi from "@/components/Core/OxNotify/OxNotifyManagerApi"

/**
 * OxProviderCore
 *
 * Provider de donnees
 */
export default class OxProviderCore {
    protected useRawUrl = false
    protected params: object = {}
    protected consecutiveFails = 0
    protected secondChance = false
    protected indexSpecs = false
    private mocker: AxiosStatic | false = false

    /**
     * Est actuellement en mode bulk
     */
    private onBulk = false

    /**
     * Setter mock
     * @param mock {AxiosTransformer} - Transformer Axios alternatif
     *
     * @return {this}
     */
    public setMock (mock: AxiosStatic): this {
        this.mocker = mock
        return this
    }

    /**
     * Forme de base d'une ressource vide
     */
    private get emptyResponse (): ApiTranslatedResponse {
        return {
            data: [],
            links: {},
            meta: {},
            status: 401
        }
    }

    /**
     * Appel via bulk : Plusieurs requêtes en un appel
     * @param {BulkElement[]} bulks - Liste des options de bulk
     *
     * @return {Promise<OxBulkCore>} - Gestionnaire de retour Bulk
     *
     */
    static async bulkApi (bulks: BulkElement[]): Promise<OxBulkCore> {
        /* eslint-disable  @typescript-eslint/no-explicit-any */
        const body: { data: any[] } = { data: [] }
        const baseUrl = await OxVueApi.getBaseUrl()
        bulks.forEach(
            (bulk) => {
                if (bulk.url.indexOf(baseUrl) === -1) {
                    bulk.url = baseUrl + bulk.url
                }
                body.data.push(
                    {
                        path: bulk.url,
                        method: bulk.method,
                        parameters: OxProviderCore.staticParseParams(bulk.parameters),
                        id: bulk.id
                    }
                )
            }
        )
        const provider = new OxProviderCore()
        provider.onBulk = true
        const responses = await provider.postApi(baseUrl + "bulkOperations", body)
        return new OxBulkCore(responses as unknown as BulkResponse[])
    }

    /**
     * Récupération de données depuis le fournisseur API de l'application
     *
     * @param {string} url - Complément d'url (ou complète) à atteindre
     * @param {Object} params - Paramètres supplémentaires
     * @param {Object} opt - Mise en cache des données récupérées
     * @param {boolean} opt.useCache - Mise en cache des données récupérées
     * @param {boolean} opt.useSecondChance - Lance un second appel en cas d'echec
     * @param {boolean} opt.indexSpecs - Récupère les spec après la récupération des données
     * @param {boolean} opt.getBulk - Récupère l'appel préparée pour une utilisation sous Bulk
     * @param {boolean} opt.transformer - Fonction de transformation des données à récupérer
     *
     * @return {Promise<ApiTranslatedResponse>}
     */
    public async getApi (
        url = "",
        params: {} = {},
        opt?: ApiParameters
    ): Promise<ApiTranslatedResponse | BulkElement> {
        let response
        const options: ApiParameters = Object.assign(
            {
                useCache: false,
                useSecondChance: false,
                indexSpecs: true,
                getBulk: false
            },
            opt
        )

        this.parseParams(params)
        this.secondChance = !!options.useSecondChance
        this.indexSpecs = !!options.indexSpecs
        if (options.getBulk) {
            return this.generateBulkElement(url, params, options)
        }

        if (options.useCache) {
            response = this.getFromCache(url)
            if (response) {
                return response
            }
        }
        response = this.callApi(
            async (url) => {
                let transformer = axios
                if (this.mocker) {
                    transformer = this.mocker
                }
                return await transformer.get(
                    url,
                    {
                        params: this.params
                    }
                )
            },
            url,
            options.transformer
        )
        if (options.useCache) {
            this.setToCache(url, response)
        }
        return response
    }

    /**
     * Envoi de données [POST]
     * @param {string} url - Ressource à laquel soumettre les données
     * @param {object} params - Paramètres supplémentaires de l'appel
     *
     * @return {Promise<ApiTranslatedResponse>}
     */
    public async postApi (url = "", params?: {}): Promise<ApiTranslatedResponse> {
        const axiosConfig = {
            headers: {
                "Content-Type": "application/vnd.api+json"
            }
        }
        this.parseParams(params)
        return this.callApi(
            async (url) => {
                let transformer = axios
                if (this.mocker) {
                    transformer = this.mocker
                }
                return await transformer.post(url, this.params, axiosConfig)
            },
            url
        ) as Promise<ApiTranslatedResponse>
    }

    /**
     * Envoi de données [PUT]
     * @param {string} url - Ressource à laquel soumettre les données
     * @param {object} params - Paramètres supplémentaires de l'appel
     *
     * @return {Promise<ApiTranslatedResponse>}
     */
    public async putApi (url = "", params?: {}): Promise<ApiTranslatedResponse> {
        const axiosConfig = {
            headers: {
                "Content-Type": "application/vnd.api+json"
            }
        }
        this.parseParams(params)
        return this.callApi(
            async (url) => {
                let transformer = axios
                if (this.mocker) {
                    transformer = this.mocker
                }
                return await transformer.put(url, this.params, axiosConfig)
            },
            url
        ) as Promise<ApiTranslatedResponse>
    }

    /**
     * Récupération de la clef de cache permettant de récupérer une ressource mise en cache
     * @param {string} url - Lien de base de la ressource en cache
     *
     * @return {string}
     */
    private getCacheKey (url: string): string {
        return url + JSON.stringify(this.params)
    }

    /**
     * Mise en cache d'une ressource
     * @param {string} url - Lien de base de la ressource en cache
     * @param {object} response - Ressource à mettre en cache
     */
    private setToCache (url: string, response: object): void {
        return OxStoreCore.commit("setApiCache", { key: this.getCacheKey(url), value: response })
    }

    /**
     * Récupération d'une ressource mise en cache
     * @param {string} url - Lien de base de la ressource
     *
     * @return {object}
     */
    private getFromCache (url: string): object {
        return OxStoreCore.getters.getApiCache(this.getCacheKey(url))
    }

    /**
     * Lancement d'un appel de base
     * @param {Promise} promise - Appel à réaliser
     * @param {string} url - Lien à atteindre
     * @param {Function} transformer
     *
     * @return {Promise<ApiTranslatedResponse>}
     */
    private async callApi (promise: Function, url = "", transformer?: Function): Promise<ApiTranslatedResponse | ApiTranslatedResponse[]> {
        const baseUrl = await OxVueApi.getBaseUrl()
        if (url.indexOf(baseUrl) === -1 && !this.useRawUrl) {
            url = baseUrl + url
        }
        try {
            const response = ((await Promise.resolve(promise(url))) as { data: ApiResponse; status: number })
            const apiResponse = response.data
            this.consecutiveFails = 0
            if (apiResponse.errors) {
                (new OxNotifyManagerApi(OxStoreCore)).addError(apiResponse.errors.message)
                OxStoreCore.commit("resetLoading")
                return this.emptyResponse
            }
            if (this.onBulk) {
                const responses: ApiTranslatedResponse[] = []
                await (apiResponse as unknown as BulkResponse[]).forEach(
                    async (data) => {
                        responses.push(
                            Object.assign(
                                {
                                    status: data.status,
                                    id: data.id.toString()
                                },
                                await (new OxSerializerCore(
                                    data.body.data,
                                    data.body.meta,
                                    data.body.links,
                                    data.body.included,
                                    this.indexSpecs
                                ).translateData())
                            )
                        )
                    }
                )
                return responses
            }
            const data = await (new OxSerializerCore(apiResponse.data, apiResponse.meta, apiResponse.links, apiResponse.included, this.indexSpecs))
                .translateData()
            if (transformer) {
                data.data = transformer(data.data)
            }
            return Object.assign(
                {
                    status: response.status
                },
                data
            )
        }
        catch (e: any) {
            this.consecutiveFails++
            if (this.secondChance && this.consecutiveFails < 2) {
                return await this.callApi(promise, url)
            }
            if (e.response && e.response.data && e.response.data.errors) {
                (new OxNotifyManagerApi(OxStoreCore)).addError(e.response.data.errors.message)
            }
            OxProviderCore.warn(e)
            OxStoreCore.commit("resetLoading")
        }
        return this.emptyResponse
    }

    /**
     * Récupération d'une liste
     * @param url
     * @param params
     *
     * @return {Promise<Array<any>>}
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any, @typescript-eslint/no-unused-vars */
    public async getAutocomplete (filter?: string): Promise<any[]> {
        return []
    }

    /**
     *
     * @param url
     * @param params
     *
     * @return {Promise<Array<any>>}
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any, @typescript-eslint/no-unused-vars */
    public async getAutocompleteById (filter?: string): Promise<any[]> {
        return []
    }

    /**
     * Retours d'erreurs rencontrés lors d'échanges du provider de données
     *
     * @param e
     */
    public static warn (e ?): void {
        console.warn("Error while trying to use a provider")
        console.warn(e)
    }

    /**
     * Mise en forme et setter de paramètres
     * @param {object} params - Paramètre à fixer
     */
    private parseParams (params?: {}): void {
        this.params = Object.assign(
            this.params,
            OxProviderCore.staticParseParams(params)
        )
    }

    /**
     * (static) Mise en forme et setter de paramètres
     * @param params
     *
     * @return {object} - Paramètres mis en forme pour un appel
     */
    static staticParseParams (params?: {}): object {
        if (!params || params === {}) {
            return {}
        }
        Object.keys(params).forEach(
            (_key) => {
                const _value = params[_key]
                if (_value === null || typeof (_value) === "undefined") {
                    delete (params[_key])
                }
                else if (typeof (_value) === "boolean") {
                    params[_key] = _value ? 1 : 0
                }
                else if (typeof (_value) === "string") {
                    if (_value.length === 16 && OxDate.isDate(_value)) {
                        params[_key] = _value + ":00"
                    }
                }
            }
        )
        return params
    }

    /**
     * Génération d'une option d'appel bulk
     * @param {string} url - Url cible de la requête de bulk
     * @param {params} params - Paramètres relatif à la requête bulk
     * @param {ApiParameters} options - Options propre à l'appel OxProviderCore
     *
     * @return {BulkElement}
     */
    private generateBulkElement (url, params, options): BulkElement {
        return {
            url: url,
            parameters: params,
            method: "GET",
            opt: Object.assign(options, { getBulk: false }),
            id: Math.ceil(Math.random() * Math.pow(10, 16)).toString(),
            transformer: options.transformer,
            data: null
        }
    }
}
