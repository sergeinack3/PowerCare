<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="install-element-tile-container">
    <div class="install-element">
      <div class="install-element-label">
        {{ label }}
      </div>
      <div class="install-element-value">
        <INValue :field="value"/>
      </div>
    </div>
  </div>
</template>

<script src="./INLineElement.ts" lang="ts"></script>

<style src="./INLineElement.scss" lang="scss" scoped>

</style>
