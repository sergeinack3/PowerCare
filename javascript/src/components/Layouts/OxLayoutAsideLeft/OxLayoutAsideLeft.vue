<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="OxLayoutAsideLeft">
    <div class="OxLayoutAsideLeft-asideContainer">
      <div class="OxLayoutAsideLeft-aside">
        <slot name="aside"></slot>
      </div>
    </div>
    <div
      class="OxLayoutAsideLeft-contentContainer"
      :style="asideStyle">
      <div class="OxLayoutAsideLeft-content">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script src="./OxLayoutAsideLeft.ts" lang="ts"></script>
<style src="./OxLayoutAsideLeft.scss" lang="scss">

</style>
