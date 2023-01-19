<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="OxCardAction">
    <ox-button
      button-style="tertiary"
      :icon="icon"
      :label="label"
      @click="click"
    />
  </div>
</template>

<script src="./OxCardAction.ts" lang="ts"></script>
