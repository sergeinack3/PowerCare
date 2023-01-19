<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="OxCard">
    <div class="OxCard-header">
      <slot name="header"></slot>
    </div>
    <div class="OxCard-content">
      <slot></slot>
    </div>
    <div class="OxCard-actions">
      <slot name="action"></slot>
    </div>
  </div>
</template>

<script src="./OxCard.ts" lang="ts"></script>
<style src="./OxCard.scss">

</style>
