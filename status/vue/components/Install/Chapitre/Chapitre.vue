<!-- eslint-disable vue/multi-word-component-names -->
<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="Chapitre"
    @scroll="scroll">
    <slot/>
  </div>
</template>

<script src="./Chapitre.ts" lang="ts"></script>

<style src="./Chapitre.scss" lang="scss"></style>
