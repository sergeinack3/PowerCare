<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="TabSelector"
  >
    <tab-line
      v-for="tab in tabs"
      :is-active="checkTabActive(tab.tab_name)"
      :key="tab.tab_name"
      :module-name="moduleName"
      :tab="tab"
      @changePin="pinTab"
    />
    <div
      v-if="showDivider"
      class="TabSelector-divider"
    ></div>
    <tab-line
      v-if="param"
      :is-active="checkTabActive('', 'param')"
      :module-name="moduleName"
      :param="true"
      :show-pin="false"
      :tab="paramTab"
    />
    <tab-line
      v-if="configure"
      :is-active="checkTabActive('', 'config')"
      :module-name="moduleName"
      :show-pin="false"
      :tab="configTab"
    />
  </div>
</template>

<script src="./TabSelector.ts" lang="ts"></script>
<style src="./TabSelector.scss" lang="scss"></style>
