import Vue from "vue"
import OxVueApi from "./components/Core/OxVueApi"
import Appbar from "./components/Appbar/Appbar.vue"
import OxVueWrap from "./core/components/OxVueWrap/OxVueWrap.vue"

/* eslint-disable */
window.initVueRoots = () => {
  const element = $("Appbar")

  if (!element) {
    return
  }

  if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = Array.prototype.forEach
  }

  OxVueApi.init([], document.location.origin, "")

  let vueProps = ""
  for (let i = 0; i < element.attributes.length; i++){
    const _attribute = element.attributes[i]
    const _attributeName = _attribute.nodeName
    if ((_attributeName.indexOf("vue-") === 0 || _attributeName.indexOf(":vue-") === 0)) {
      vueProps += _attributeName.replace("vue-", "") + "='" + _attribute.nodeValue + "' "
    }
  }

  new Vue({
    template: '<OxVueWrap><appbar ' + vueProps + '/></OxVueWrap>',
    components: {
      Appbar,
      OxVueWrap
    }
  }).$mount('#Appbar')
}

window.addEventListener("DOMContentLoaded", () => {
  initVueRoots()
});
