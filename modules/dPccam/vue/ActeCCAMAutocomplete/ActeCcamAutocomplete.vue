<!--
  @package Mediboard\CCAM
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="ActeCcamAutocomplete"
    :title="item.libelle_long"
  >
    <v-list-item-content>
      <v-list-item-title v-text="item.code"></v-list-item-title>
      <v-list-item-subtitle v-text="item.libelle_long"></v-list-item-subtitle>
    </v-list-item-content>
  </div>
</template>

<script src="./ActeCcamAutocomplete.ts" lang="ts"></script>
<style src="./ActeCcamAutocomplete.scss" lang="scss"></style>
