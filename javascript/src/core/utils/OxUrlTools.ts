/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import {
    OxUrlQueryParameters,
    OxUrlSorter,
    OxUrlFilter, OxUrlOperator
} from "@/core/types/OxUrlTypes"

export function getUrlParams (url: string) {
    const urlBuilder = new OxUrlBuilder(url)

    return {
        fieldsets: urlBuilder.queryParameters.fieldsets,
        relations: urlBuilder.queryParameters.relations,
        filters: urlBuilder.queryParameters.filters,
        sort: urlBuilder.queryParameters.sort.map((sort) => {
            return transformSortStringToObject(sort)
        }),
        offset: Number(urlBuilder.queryParameters.offset),
        limit: Number(urlBuilder.queryParameters.limit),
        search: urlBuilder.queryParameters.search,
        otherParameters: urlBuilder.otherParameters
    }
}

export function transformSortStringToObject (sort: string): OxUrlSorter {
    return { sort: sort.startsWith("-") ? "DESC" : "ASC", choice: sort.replace(/-/g, "") }
}

export class OxUrlBuilder {
    private _url!: URL
    private _queryParameters: OxUrlQueryParameters = {
        fieldsets: [],
        relations: [],
        filters: [],
        sort: [],
        offset: "",
        limit: "",
        search: ""
    }

    private _otherParameters: Array<{parameter: string, value: string}>

    constructor (originUrl?: string) {
        let url = ""
        if (originUrl) {
            url = (originUrl.includes("http") ? "" : window.location.origin) + originUrl
        }
        else {
            url = window.location.href
        }
        this._url = new URL(url)
        this._queryParameters.fieldsets = this._url.searchParams.get("fieldsets")?.split(",") ?? []
        this._queryParameters.relations = this._url.searchParams.get("relations")?.split(",") ?? []
        this._queryParameters.filters = this._url.searchParams.get("filter")?.split(",") ?? []
        this._queryParameters.sort = this._url.searchParams.get("sort")?.split(",") ?? []
        this._queryParameters.offset = this._url.searchParams.get("offset") ?? ""
        this._queryParameters.limit = this._url.searchParams.get("limit") ?? ""
        this._queryParameters.search = this._url.searchParams.get("search") ?? ""
        this._otherParameters = []
    }

    get queryParameters () : OxUrlQueryParameters {
        return this._queryParameters
    }

    get otherParameters () : Array<{parameter: string, value: string}> {
        return this._otherParameters
    }

    withOffset (offset: string | null): OxUrlBuilder {
        this._queryParameters.offset = offset

        return this
    }

    withLimit (limit: string | null): OxUrlBuilder {
        this._queryParameters.limit = limit

        return this
    }

    withSearch (search: string | null): OxUrlBuilder {
        this._queryParameters.search = search

        return this
    }

    withFieldsets (fieldsets: string[]): OxUrlBuilder {
        this._queryParameters.fieldsets = fieldsets

        return this
    }

    addFieldset (fieldset: string): OxUrlBuilder {
        if (!this._queryParameters.fieldsets.includes(fieldset)) {
            this._queryParameters.fieldsets.push(fieldset)
        }

        return this
    }

    withRelations (relations: string[]): OxUrlBuilder {
        this._queryParameters.relations = relations

        return this
    }

    addRelation (relation: string): OxUrlBuilder {
        if (!this._queryParameters.relations.includes(relation)) {
            this._queryParameters.relations.push(relation)
        }

        return this
    }

    withFilters (...filters: OxUrlFilter[]): OxUrlBuilder {
        this._queryParameters.filters = filters.map(
            filter => {
                if (!Array.isArray(filter.value)) {
                    filter.value = [filter.value]
                }

                return [filter.key, filter.operator, ...filter.value].join(".")
            }
        )

        return this
    }

    addFilter (key: string, operator: OxUrlOperator, value: string | string[]): OxUrlBuilder {
        if (!Array.isArray(value)) {
            value = [value]
        }

        const filter = [key, operator, ...value].join(".")

        this._queryParameters.filters = this._queryParameters.filters.filter((_filter) => {
            return !_filter.includes([key, operator].join("."))
        })

        // Check if value contains a valid value
        const hasString = value.findIndex((element) => {
            return !!element
        })
        if (hasString !== -1) {
            this._queryParameters.filters.push(filter)
        }

        return this
    }

    withSort (...sorters: OxUrlSorter[]): OxUrlBuilder {
        this._queryParameters.sort = sorters.map(
            sorter => (sorter.sort === "DESC" ? "-" : "") + sorter.choice
        )

        return this
    }

    addSort (sorter: OxUrlSorter): OxUrlBuilder {
        this._queryParameters.sort = this._queryParameters.sort.filter(
            sort => sort !== sorter.choice && sort !== "-" + sorter.choice
        )

        const valueSort = (sorter.sort === "DESC" ? "-" : "") + sorter.choice
        this._queryParameters.sort.push(valueSort)

        return this
    }

    addParameter (parameter: string, value: string): OxUrlBuilder {
        this._otherParameters.push({ parameter, value })

        return this
    }

    removeParameter (parameter: string): OxUrlBuilder {
        const index = this._otherParameters.findIndex((param) => {
            return param.parameter === parameter
        })
        if (index > -1) {
            this._otherParameters.splice(index, 1)
        }

        this._url.searchParams.delete(parameter)

        return this
    }

    withPermissions () {
        this._otherParameters.push({ parameter: "permissions", value: "true" })

        return this
    }

    withSchema () {
        this._otherParameters.push({ parameter: "schema", value: "true" })

        return this
    }

    buildUrl (): URL {
        this.buildLimit()

        this.buildOffset()

        this.buildSort()

        this.buildRelations()

        this.buildFieldsets()

        this.buildFilters()

        this.buildSearch()

        if (this._otherParameters.length > 0) {
            this.buildOtherParameters()
        }

        return this._url
    }

    toString (): string {
        return this.buildUrl().toString()
    }

    private buildLimit () {
        if (this._queryParameters.limit) {
            this._url.searchParams.set("limit", this._queryParameters.limit)
        }
        else {
            this._url.searchParams.delete("limit")
        }
    }

    private buildOffset () {
        if (this._queryParameters.offset) {
            this._url.searchParams.set("offset", this._queryParameters.offset)
        }
        else {
            this._url.searchParams.delete("offset")
        }
    }

    private buildSort () {
        if (this._queryParameters.sort.length > 0) {
            this._url.searchParams.set("sort", this._queryParameters.sort.join(","))
        }
        else {
            this._url.searchParams.delete("sort")
        }
    }

    private buildRelations () {
        if (this._queryParameters.relations.length > 0) {
            this._url.searchParams.set("relations", this._queryParameters.relations.join(","))
        }
        else {
            this._url.searchParams.delete("relations")
        }
    }

    private buildFieldsets () {
        if (this._queryParameters.fieldsets.length > 0) {
            this._url.searchParams.set("fieldsets", this._queryParameters.fieldsets.join(","))
        }
        else {
            this._url.searchParams.delete("fieldsets")
        }
    }

    private buildFilters () {
        if (this._queryParameters.filters.length > 0) {
            this._url.searchParams.set("filter", this._queryParameters.filters.join(","))
        }
        else {
            this._url.searchParams.delete("filter")
        }
    }

    private buildSearch () {
        if (this._queryParameters.search) {
            this._url.searchParams.set("search", this._queryParameters.search)
        }
        else {
            this._url.searchParams.delete("search")
        }
    }

    private buildOtherParameters () {
        this._otherParameters.forEach((param) => {
            this._url.searchParams.set(param.parameter, param.value)
        })
    }
}
