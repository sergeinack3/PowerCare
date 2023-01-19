/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import Vue, { Component } from "vue"
import OxVueWrap from "@/core/components/OxVueWrap/OxVueWrap.vue"

/**
 * VueJS Entry points
 */
export function createLegacyEntryPoint (id: string, component: Component) {
    const element = document.getElementById(id)

    if (element) {
        if (window.NodeList && !NodeList.prototype.forEach) {
            // @ts-ignore
            NodeList.prototype.forEach = Array.prototype.forEach
        }

        let vueProps = ""
        for (let i = 0; i < element.attributes.length; i++) {
            const _attribute = element.attributes[i]
            const _attributeName = _attribute.nodeName
            if ((_attributeName.indexOf("vue-") === 0 || _attributeName.indexOf(":vue-") === 0)) {
                vueProps += _attributeName.replace("vue-", "") + "='" + _attribute.nodeValue + "' "
            }
        }

        new Vue({
            template: "<ox-vue-wrap><view-component " + vueProps + "/></ox-vue-wrap>",
            components: {
                ViewComponent: component,
                OxVueWrap
            }
        }).$mount(element)
    }
}
