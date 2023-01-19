<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <a
    ref="tabLineLink"
    class="TabLine"
    :class="classes"
    :href="tabLink"
    tabindex="0"
    v-ripple
    @keyup="keydownTabLine"
  >
    <div class="TabLine-name">
      {{ tabName }}
      <div class="TabLine-badge">
        <tab-badge
          :tab="tab"
        />
      </div>
    </div>
    <div
      v-if="showPin"
      class="TabLine-pin"
      @click.prevent="clickPin"
      @mousedown.stop=""
    >
      <ox-icon icon="pin" />
    </div>
  </a>
</template>

<script src="./TabLine.ts" lang="ts"></script>
<style src="./TabLine.scss" lang="scss"></style>
