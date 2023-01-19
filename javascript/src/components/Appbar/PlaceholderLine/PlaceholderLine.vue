<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="PlaceholderLine"
    @click="click"
  >
    <placeholder-shortcut
      :flat="true"
      :placeholder="placeholder"
      :show-label="false"
    />

    <div>{{ placeholder.label }}</div>
  </div>
</template>

<script src="./PlaceholderLine.ts" lang="ts"></script>
<style src="./PlaceholderLine.scss" lang="scss"></style>
