<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="OxCardHeader">
    <div class="OxCardHeader-icon">
      <ox-icon
          :icon="icon"
          size="18"
      />
    </div>
    <div class="OxCardHeader-label">
      {{label}}
    </div>
  </div>
</template>

<script src="./OxCardHeader.ts" lang="ts"></script>
<style src="./OxCardHeader.scss">

</style>
