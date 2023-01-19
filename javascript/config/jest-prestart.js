// JEST global setup file
// This script is executed before each test file
import Vue from "vue"
import Vuetify from "vuetify"

Vue.use(Vuetify)

Vue.config.productionTip = false;

window.$T = function (trad) { return trad }
