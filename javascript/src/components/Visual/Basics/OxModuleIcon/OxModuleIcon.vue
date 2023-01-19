<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="OxModuleIcon">
    <div
      v-if="!showCategoryIcon"
      key="customIcon"
      class="OxModuleIcon-icon"
      v-html="icon"
    >
    </div>
    <div
      v-else
      class="OxModuleIcon-defaultIcon"
      key="categoryIcon"
    >
      <ox-icon
        :icon="getCategoryIcon"
        :size="iconSize"
      />
    </div>
  </div>
</template>

<script src="./OxModuleIcon.ts" lang="ts"></script>
<style src="./OxModuleIcon.scss" lang="scss"></style>
