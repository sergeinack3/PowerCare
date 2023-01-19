<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="INValueBool">
    <div
      class="INValueBool-icon true"
      v-if="field">
      <INIcon icon="check"/>
    </div>
    <div
      class="INValueBool-icon false"
      v-else-if="!field">
      <INIcon icon="times"/>
    </div>
  </div>
</template>

<script src="./INValueBool.ts" lang="ts"></script>

<style src="./INValueBool.scss" lang="scss" scoped>
</style>
