<!-- eslint-disable vue/multi-word-component-names -->
<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="Information">
    <INLoading v-if="!loaded"/>
    <div
      v-else
      class="Information-content">
      <div class="Chapitre-title">
        {{ tr('Information') }}
      </div>
      <div
        class="Information-dom"
        v-html="informationDOM">
      </div>
    </div>
  </div>
</template>

<script src="./Information.ts" lang="ts"></script>

<style src="./Information.scss" lang="scss">

</style>
