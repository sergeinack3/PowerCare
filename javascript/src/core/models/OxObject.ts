/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import {
    OxObjectAttributes,
    OxObjectIncludedTypes,
    OxObjectLinks,
    OxObjectMeta,
    OxObjectRelation,
    OxObjectRelationships
} from "@/core/types/OxObjectTypes"
import { getObject, storeObject } from "@/core/utils/OxStorage"
import Vue from "vue"

/**
 * Parent class for all front objects
 */
export default class OxObject {
    protected _id!: string
    protected _type!: string
    protected _attributes: OxObjectAttributes = {}
    protected _relationships: OxObjectRelationships = {}
    protected _links: OxObjectLinks = {}
    protected _relationsTypes!: OxObjectIncludedTypes
    protected _meta?: OxObjectMeta
    protected _joinAttributes: OxObjectAttributes = {}

    constructor () {
        this.id = ""
        this.type = "ox_object"
    }

    get id (): string {
        return this._id
    }

    set id (id: string) {
        this._id = id
    }

    get type (): string {
        return this._type
    }

    set type (value: string) {
        this._type = value
    }

    get attributes (): OxObjectAttributes {
        return this._attributes
    }

    set attributes (value: OxObjectAttributes) {
        this._attributes = value
    }

    get relationships (): OxObjectRelationships {
        return this._relationships
    }

    set relationships (value: OxObjectRelationships) {
        this._relationships = value
    }

    get links (): OxObjectLinks {
        return this._links
    }

    set links (value: OxObjectLinks) {
        this._links = value
    }

    get self (): string | undefined {
        return this.links.self
    }

    get relationsTypes (): OxObjectIncludedTypes {
        return this._relationsTypes
    }

    set relationsTypes (value: OxObjectIncludedTypes) {
        this._relationsTypes = value
    }

    get meta (): OxObjectMeta | undefined {
        return this._meta
    }

    set meta (value: OxObjectMeta | undefined) {
        this._meta = value
    }

    get joinAttributes (): OxObjectAttributes {
        return this._joinAttributes
    }

    set joinAttributes (joinAttributes: OxObjectAttributes) {
        this._joinAttributes = joinAttributes
    }

    protected get<T> (attribute: string): T {
        return this.attributes[attribute]
    }

    protected set<T> (attribute: string, value: T) {
        Vue.set(this.attributes, attribute, value)
    }

    /**
     * Set an OxObject as relation
     * @param {string} relation - The relation's name
     * @param {OxObject} object - The relation's object
     */
    setForwardRelation<T extends OxObject> (relation: string, object: T | null) {
        if (!object) {
            if (!this.relationships[relation]) {
                this.relationships[relation] = {
                    data: null
                }
            }
            else {
                this.relationships[relation].data = null
            }

            return
        }

        if (!this.relationships[relation] || !this.relationships[relation].data) {
            Vue.set(this.relationships, relation, {
                data: {
                    type: object.type,
                    id: object.id
                } as OxObjectRelation
            })
        }
        else {
            const dataRelations = this.relationships[relation].data as OxObjectRelation | OxObjectRelation[]

            Vue.set(dataRelations, "type", object.type)
            Vue.set(dataRelations, "id", object.id)
        }

        storeObject<T>(object)
    }

    /**
     * Return the object corresponding to the relation
     * @param {string} relation - Relation's name
     */
    loadForwardRelation<T extends OxObject> (relation: string): T | null {
        if (!this.relationships[relation] || !this.relationships[relation].data) {
            return null
        }

        const dataRelations = this.relationships[relation].data as OxObjectRelation | OxObjectRelation[]

        if (Array.isArray(dataRelations)) {
            throw new Error("Multiple forward relations found")
        }

        if (!dataRelations.id) {
            throw new Error("Id relations not found")
        }

        return getObject<T>(dataRelations.type, dataRelations.id)
    }

    /**
     * Add object to a multiple relation
     * @param {string} relation - Relation's name
     * @param {OxObject} object - Relation's object
     */
    addBackwardRelation<T extends OxObject> (relation: string, object: T) {
        if (!this.relationships[relation]) {
            this.relationships[relation] = {
                data: []
            }
        }

        (this.relationships[relation].data as OxObjectRelation[]).push({
            type: object.type,
            id: object.id,
            attributes: object.attributes
        })

        if (object.id) {
            storeObject<T>(object)
        }
    }

    /**
     * Return the objects corresponding to the relation
     * @param {string} relation - Relation's name
     */
    loadBackwardRelation<T extends OxObject> (relation: string): T[] {
        if (!this.relationships[relation] || !this.relationships[relation].data) {
            return []
        }

        const dataRelations = this.relationships[relation].data

        if (!Array.isArray(dataRelations)) {
            throw new Error("Backward relation is not an array")
        }

        return dataRelations.map((relationData) => {
            return getObject(relationData.type, relationData.id as string) as T
        })
    }
}
