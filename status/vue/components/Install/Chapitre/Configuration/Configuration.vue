<!-- eslint-disable vue/multi-word-component-names -->
<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="Configuration">
    <div v-if="!loaded">
      <INLoading />
    </div>
    <div v-else>
      <div class="Chapitre-title leftShifted">
        {{ tr('Configuration') }}
      </div>
      <div class="Chapitre-field">
        <INField
          :placeholder="tr('ToFilter')"
          @input="filterConfiguration"
          :can-reset="true"/>
      </div>
      <div
        v-for="(config, configIndex) in configs"
        :key="configIndex"
        class="install-conf">
        <transition
          enter-active-class="enter"
          leave-active-class="leave">
          <div
            class="Chapitre-line"
            v-if="config.displayed">
            <INLineElement
              :label="config.label"
              :value="config.value"/>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script src="./Configuration.ts" lang="ts"></script>

<style src="./Configuration.scss" lang="scss" scoped>

</style>
