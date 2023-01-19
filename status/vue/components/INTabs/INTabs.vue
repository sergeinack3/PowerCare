<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="INTabs">
    <div class="INTabs-tabs">
      <div
        class="INTabs-tab"
        v-for="(tab, tabIndex) in tabs"
        :class="{active: tab.id === currentTab}"
        :key="'tab-' + tabIndex"
        @click="tabClick(tab.id)">
        {{ tab.label }}
      </div>
    </div>
    <div class="INTabs-content">
      <slot></slot>
    </div>
  </div>
</template>

<script src="./INTabs.ts" lang="ts"></script>

<style src="./INTabs.scss" lang="scss" scoped>
</style>
