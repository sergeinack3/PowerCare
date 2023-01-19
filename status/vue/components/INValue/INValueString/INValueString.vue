<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="INValueString">
    <div :class="className">
      {{ fieldView }}
    </div>

    <transition name="install-field-pop">
      <div
        v-if="needShowMore() && (longFieldDisplayed)"
        class="INValueString-complete_field">
        {{ field }}
      </div>
    </transition>
    <div
      v-if="needShowMore()"
      class="install-field-string-show-more">
      <INButton
        :label="tr('Show more')"
        button-class="tertiary"
        @click="showLongField"/>
      <div
        class="INValueString-long_field"
        v-show="longFieldDisplayed"
        @click="hideLongField">
      </div>
    </div>
  </div>
</template>

<script src="./INValueString.ts" lang="ts"></script>

<style src="./INValueString.scss" lang="scss" scoped>
</style>
