/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { ApiData, ApiLinks, ApiMeta, ApiTranslatedResponse, TranslatedApiData } from "@/components/Models/ApiResponseModel"
import OxSpecsApi from "@/components/Core/OxSpecsApi"

/**
 * OxSerializerCore
 *
 * Parseur de données en objets
 */
export default class OxSerializerCore {
    // Ressources annexes
    /* eslint-disable  @typescript-eslint/no-explicit-any */
    private included: any[] = []
    // Ressources brutes
    private data
    // Metadonnées
    private meta: ApiMeta
    // Liens de la donnée
    private links: ApiLinks
    // Activation des spécifications de l'object
    private index = false

    /**
     * @inheritDoc
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any */
    public constructor (data: ApiData | ApiData[], meta: ApiMeta, links: ApiLinks, included: any[] = [], indexSpecs = false) {
        this.meta = meta
        this.links = links

        included.forEach(
            (_inc) => {
                if (Array.isArray(_inc)) {
                    this.included = included.concat(_inc)
                }
                else {
                    this.included.push(_inc)
                }
            }
        )
        this.data = data
        this.index = indexSpecs
        return this
    }

    /**
     * Retour des données avec modification du format et et liaison avec les données annexes
     *
     * @return {Promise<ApiTranslatedResponse>}
     */
    public async translateData (): Promise<ApiTranslatedResponse> {
        if (this.index) {
            await this.indexSpecs()
        }
        return {
            data:
                Array.isArray(this.data)
                    ? await Promise.all(
                        this.data.map(
                            async (_data) => {
                                return this.translateObject(_data)
                            }
                        )
                    )
                    : this.translateObject(this.data),
            meta: this.meta,
            links: this.links
        }
    }

    /**
     * Modification du format d'un objet donné et liaison avec ses données annexes
     *
     * @param {object} data - Données d'un objet unique
     *
     * @return {TranslatedApiData}
     */
    public translateObject (data): TranslatedApiData {
        const ex = {}
        if (data && data.relationships) {
            Object.keys(data.relationships).forEach(
                (_relIndex) => {
                    const _rel = data.relationships[_relIndex]
                    if (!_rel.data || (!Array.isArray(_rel.data) && !_rel.data.type)) {
                        return
                    }
                    /* eslint-disable  @typescript-eslint/no-explicit-any */
                    let _inc: any
                    let type: string
                    if (Array.isArray(_rel.data)) {
                        type = _rel.data[0].type
                        _inc = []
                        _rel.data.forEach((_data) => {
                            _inc.push(this.extractInclude(_data.type, _data.id))
                        })
                    }
                    else {
                        type = _rel.data.type
                        _inc = this.extractInclude(_rel.data.type, _rel.data.id)
                    }

                    if (!type || !_inc) {
                        return
                    }
                    ex["_" + type] = _inc
                }
            )
        }
        return Object.assign(
            {
                _type: data && data.type ? data.type : null,
                _id: data && data.id ? data.id : null,
                _links: data && data.links ? data.links : {}
            },
            data?.attributes,
            ex
        )
    }

    /**
     * Récupération d'une donnée annexe depuis les données indéxées
     *
     * @param {string} type - Type de ressource
     * @param {string} id - Identifiant de la ressource
     *
     * @return {any}
     */
    /* eslint-disable  @typescript-eslint/no-explicit-any */
    private extractInclude (type: string, id: string): any {
        const inc = this.included.filter(
            (_inc: { type: string; id: string }) => {
                return _inc.type === type && _inc.id === id
            }
        )
        return inc.length > 0 ? this.translateObject(inc[0]) : false
    }

    /**
     * Indexation  des spécifications d'objets
     *
     * @return {Promise<voir>}
     */
    private async indexSpecs (): Promise<void> {
        if (!this.data || !this.data.length) {
            return
        }
        await this.indexSpec(Array.isArray(this.data) ? this.data[0] : this.data)
    }

    /**
     * Indexation des spécifications d'un objet donné
     * @param {object} data - Données à stocker
     *
     * @return {Promise<void>}
     */
    private async indexSpec (data): Promise<void> {
        if (!data.links || !data.links.schema) {
            return
        }
        await OxSpecsApi.setSpecsByLink(data.links.schema, data.type, true)
    }
}
