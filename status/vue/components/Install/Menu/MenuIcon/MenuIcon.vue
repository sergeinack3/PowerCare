<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="MenuIcon"
    :class="className"
    @click="chapterClick">
    <div class="MenuIcon-container">
      <div
        class="MenuIcon-containerButton"
        :class="buttonClassName">
        <INIcon :icon="icon"/>
        <div
          v-if="hasFlag"
          class="MenuIconFlag"
          :class="'MenuIconFlag-' + flagState">
          <INIcon :icon="flagIcon"/>
        </div>
      </div>
      <div
        class="MenuIcon-label"
        :class="labelClassName">
        {{ label }}
      </div>
    </div>
    <div class="MenuIcon-indicator">
    </div>
  </div>
</template>

<script src="./MenuIcon.ts" lang="ts"></script>

<style src="./MenuIcon.scss" lang="scss" scoped>
</style>
