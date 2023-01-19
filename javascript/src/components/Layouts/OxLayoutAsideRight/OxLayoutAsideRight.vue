<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="OxLayoutAsideRight">
    <div class="OxLayoutAsideRight-contentContainer">
      <div class="OxLayoutAsideRight-content">
        <slot></slot>
      </div>
    </div>
    <div
      class="OxLayoutAsideRight-asideContainer"
      :style="asideStyle">
      <div class="OxLayoutAsideRight-aside">
        <slot name="aside"></slot>
      </div>
    </div>
  </div>
</template>

<script src="./OxLayoutAsideRight.ts" lang="ts"></script>
<style src="./OxLayoutAsideRight.scss" lang="scss">

</style>
