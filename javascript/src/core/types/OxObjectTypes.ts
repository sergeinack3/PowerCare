/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxObject from "@/core/models/OxObject"

export type OxAttr<T> = T | undefined
export type OxAttrNullable<T> = T | null | undefined

export interface OxObjectAttributes {
    [key: string]: any
}

export interface OxObjectRelation {
    type: string,
    id: string | null,
    relation?: string,
    attributes?: OxObjectAttributes
}

export interface OxObjectRelationships {
    [key: string]: {
        data: OxObjectRelation | OxObjectRelation[] | null
    }
}

export interface OxObjectLinks {
    self?: string
    schema?: string
    history?: string
    [key: string]: string | undefined
}

export interface OxObjectIncludedTypes {
    [key: string]: typeof OxObject
}

export interface OxObjectMeta {
    permissions: {[key: string]: string}
}
