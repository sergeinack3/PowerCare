<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="ModuleLine"
    :class="classes"
    tabindex="0"
  >
    <a
      ref="moduleLineLink"
      class="ModuleLine-content"
      :href="moduleLink"
      v-ripple
      @keydown.right="clickDetail($event, true)"
    >
      <div class="ModuleLine-icon">
        <ox-module-icon
          :module-category="module.mod_category"
          :module-name="module.mod_name"
        />
      </div>
      <div class="ModuleLine-name">
        {{ tr("module-" + module.mod_name + "-court") }}
      </div>
    </a>
    <div
      class="ModuleLine-action"
      tabindex="0"
      @click="clickDetail"
      @keydown.enter="clickDetail"
    >
      <ox-icon icon="vDots"/>
    </div>
  </div>
</template>

<script src="./ModuleLine.ts" lang="ts"></script>
<style src="./ModuleLine.scss" lang="scss"></style>
