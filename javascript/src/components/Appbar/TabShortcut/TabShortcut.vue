<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <a
    class="TabShortcut"
    :href="tabLink"
    v-ripple
  >
    <div class="TabShortcut-icon">
      <ox-module-icon
        :module-name="tab.mod_name"
        :module-category="moduleCategory"
        :small="!mobile"
      />
    </div>
    <div class="TabShortcut-content">
      <div class="TabShortcut-module">
        {{ moduleName }}
      </div>
      <div class="TabShortcut-name">
        {{ tabName }}
      </div>
    </div>
    <div class="TabShortcut-badge">
      <tab-badge
        :tab="tab"
      />
    </div>
  </a>
</template>

<script src="./TabShortcut.ts" lang="ts"></script>
<style src="./TabShortcut.scss" lang="scss"></style>
