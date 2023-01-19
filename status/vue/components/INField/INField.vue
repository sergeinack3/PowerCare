<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="INField">
    <div class="INField-content">
      <input
        class="INField-field"
        :class="inputClassName"
        :type="type"
        :value="value"
        ref="input"
        @keyup.enter="pressEnter"
        @input="updateInput($event.target.value)"
      />
      <label
        class="INField-label"
        @click="clickLabel" >
        {{placeholder}}
      </label>
      <div class="INField-border"></div>
      <div
        @click="resetField"
        v-if="canReset"
        class="INField-reset">
        <INIcon icon="times"/>
      </div>
    </div>
  </div>
</template>

<script src="./INField.ts" lang="ts"></script>

<style src="./INField.scss" lang="scss">
</style>
