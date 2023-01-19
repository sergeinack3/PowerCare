/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

export type OxUrlOperator = "equal" | "notEqual" | "less" | "lessOrEqual" | "greater" | "greaterOrEqual" | "in" | "notIn" | "isNull"
    | "isNotNull" | "beginWith" | "doNotBeginWith" | "contains" | "strictEqual" | "doNotContains" | "endWith"
    | "doNotEndWith" | "isEmpty" | "isNotEmpty"

export interface OxUrlQueryParameters {
    fieldsets: string[]
    relations: string[]
    filters: string[]
    sort: string[]
    offset: string | null
    limit: string | null
    search: string | null
}

export interface OxUrlSorter {
    sort: "ASC" | "DESC"
    choice: string
}

export interface OxUrlFilter {
    key: string
    operator: OxUrlOperator
    value: string | string[]
}
