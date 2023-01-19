/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxObjectAttributes } from "@/core/types/OxObjectTypes"

export interface OxJsonApiDataLinks {
    self?: string
    schema?: string
    history?: string
    [key: string]: string | undefined
}

export interface OxJsonApiRelation {
    type: string,
    id: string,
    relation?: string,
    attributes?: OxObjectAttributes
}

export interface OxJsonApiRelationships {
    [key: string]: {
        data: OxJsonApiRelation | OxJsonApiRelation[]
    }
}

export interface OxJsonApiMeta {
    permissions: {[key: string]: string}
}

export interface OxJsonApiData {
    id: string
    type: string
    links?: OxJsonApiDataLinks
    attributes: OxObjectAttributes,
    relationships: OxJsonApiRelationships,
    meta?: OxJsonApiMeta
}

export interface OxJsonApiLinks {
    self?: string
    first?: string
    last?: string
    next?: string
    prev?: string
}

export interface OxApiSchema {
    data: any
}

export interface OxApiMeta {
    authors?: string
    count?: number
    date: string,
    copyright: string,
    schema?: OxApiSchema,
    total?: number
}

export interface OxApiError {
    code: number
    message: string
}

export interface OxJsonApi {
    data: OxJsonApiData | OxJsonApiData[]
    links: OxJsonApiLinks
    included: OxJsonApiData[]
    meta?: OxApiMeta
    errors?: OxApiError
}
