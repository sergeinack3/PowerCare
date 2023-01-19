<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->
<script lang="ts">
import vuetify from "@/core/plugins/OxVuetifyCore"
import pinia from "@/core/plugins/OxPiniaCore"
import Vue from "vue"
import VueCompositionAPI from "@vue/composition-api"
import OxTranslator from "@/core/plugins/OxTranslator"
import OxModuleExists from "@/core/plugins/OxModuleExists"

Vue.use(OxTranslator)
Vue.use(VueCompositionAPI)
Vue.use(OxModuleExists)

export default {
    vuetify: vuetify,
    pinia
}
</script>

<template>
  <div class="OxVueWrap">
    <v-app>
      <slot></slot>
    </v-app>
  </div>
</template>

<style lang="scss">
  .OxVueWrap {
    padding: 0!important;
  }
</style>
