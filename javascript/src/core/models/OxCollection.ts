/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxObject from "@/core/models/OxObject"
import { OxCollectionLinks, OxCollectionMeta } from "@/core/types/OxCollectionTypes"
import Vue from "vue"

/**
 * Parent class for all object collections
 */
export default class OxCollection<T extends OxObject> {
    private _objects!: T[]
    private _links!: OxCollectionLinks
    private _meta?: OxCollectionMeta

    get objects (): T[] {
        return this._objects
    }

    set objects (value: T[]) {
        this._objects = value
    }

    get links (): OxCollectionLinks {
        return this._links
    }

    set links (value: OxCollectionLinks) {
        this._links = value
    }

    get meta (): OxCollectionMeta | undefined {
        return this._meta
    }

    set meta (value: OxCollectionMeta | undefined) {
        this._meta = value
    }

    get total (): number {
        return this._meta?.total ?? 0
    }

    set total (value: number) {
        if (this._meta) {
            Vue.set(this._meta, "total", value)
        }
    }

    get count (): number {
        return this._meta?.count ?? 0
    }

    set count (count: number) {
        if (this._meta) {
            Vue.set(this._meta, "count", count)
        }
    }

    get self (): string | undefined {
        return this.links.self
    }

    get next (): string | undefined {
        return this.links.next
    }

    set next (url: string | undefined) {
        Vue.set(this._links, "next", url)
    }

    get prev (): string | undefined {
        return this.links.prev
    }

    set prev (url: string | undefined) {
        Vue.set(this._links, "prev", url)
    }

    get length (): number {
        return this.objects.length
    }

    /**
     * Remove given item from object collection and update new total value
     * @param {OxObject} itemToDelete
     */
    public deleteItem (itemToDelete: T) {
        const itemIndex = this.objects.findIndex((item) => {
            return item.id === itemToDelete.id
        })
        if (itemIndex !== -1) {
            this.objects.splice(itemIndex, 1)
            this.total--
        }
    }
}
