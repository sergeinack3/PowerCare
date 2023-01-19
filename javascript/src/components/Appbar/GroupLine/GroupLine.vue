<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="GroupLine"
    :class="lineClass"
    tabindex="0"
    v-ripple
    @click="selectGroup"
    @keydown.enter="selectGroup"
  >
    <div class="GroupLine-content">
      <div class="GroupLine-name">
        {{ group.text }}
      </div>
      <div
        v-if="functionName"
        class="GroupLine-function"
      >
        {{ functionName }}
      </div>
    </div>
    <div
      class="GroupLine-functionIndicator"
      :class="functionClass"
    ></div>
  </div>
</template>

<script src="./GroupLine.ts" lang="ts"></script>
<style src="./GroupLine.scss" lang="scss"></style>
