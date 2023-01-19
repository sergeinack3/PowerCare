/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component } from "vue-property-decorator"
import INVue from "../../INVue/INVue"

/**
 * Wrapper des chapitres (pages) de status
 */
@Component
export default class Chapitre extends INVue {
    public async load () :Promise<void> {

    }

    protected applyFilter (search: string, elements: any[], labelFields: string|string[]): object[] {
        if (typeof (labelFields) === "string") {
            labelFields = [labelFields]
        }
        for (let i = 0; i < elements.length; i++) {
            let display = false
            for (let j = 0; j < labelFields.length; j++) {
                let field = elements[i][labelFields[j]]
                if (typeof (field) === "undefined") {
                    continue
                }
                if (typeof (field) !== "string") {
                    field = field.toString()
                }
                if (field.toUpperCase().indexOf(search.toUpperCase()) > -1) {
                    display = true
                    break
                }
            }
            elements[i].displayed = display
        }
        return elements
    }

    protected extractData (collection: any[]): object[] {
        if (!collection) {
            return collection
        }
        for (let i = 0; i < collection.length; i++) {
            collection[i].displayed = true
        }
        return collection
    }

    public scroll (event: {target: any}, scrollTop?: number): void {
        let target = event.target
        if (target === false && typeof (this.$el.querySelector) === "undefined") {
            return
        }
        if (target === false) {
            target = this.$el.querySelector(".Chapitre-scrollable")
            if (target === null) {
                target = this.$el
            }
        }
        if (typeof (scrollTop) !== "undefined" && scrollTop !== null) {
            target.scrollTo(0, scrollTop)
        }
        const atTop = target.scrollTop < 10
        const toCompact = !atTop && (target.scrollHeight > (window.innerHeight - 70))
        this.$emit("compact", toCompact)
    }
}
