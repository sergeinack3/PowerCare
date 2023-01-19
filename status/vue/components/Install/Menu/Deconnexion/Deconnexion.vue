<!-- eslint-disable vue/multi-word-component-names -->
<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="Deconnexion"
    @click="disconnect">
    <div class="Deconnexion-label">
      {{ lib }}
    </div>
    <INIcon :icon="icon"/>
  </div>
</template>

<script src="./Deconnexion.ts" lang="ts"></script>

<style src="./Deconnexion.scss" lang="scss"></style>
