<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="INButton"
    :class="className"
    @click="click">
    <div>
      <INIcon
        v-if="icon"
        :icon="icon" />
      {{ label }}
    </div>
  </div>
</template>

<script src="./INButton.ts" lang="ts"></script>

<style src="./INButton.scss" lang="scss" scoped>
</style>
