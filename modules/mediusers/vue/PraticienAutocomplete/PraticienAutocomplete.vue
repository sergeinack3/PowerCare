<!--
  @package Mediboard\Mediusers
  @package Mediboard\Planning@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="PraticienAutocomplete">
    <v-list-item-avatar
      class="headline font-weight-light white--text"
      :color="'#' + item._color"
    >
      {{ item.initials }}
    </v-list-item-avatar>
    <v-list-item-content>
      <v-list-item-title v-text="item._user_last_name"></v-list-item-title>
      <v-list-item-subtitle v-text="item._user_first_name"></v-list-item-subtitle>
    </v-list-item-content>
  </div>
</template>

<script src="./PraticienAutocomplete.ts" lang="ts"></script>
<style src="./PraticienAutocomplete.scss" lang="scss">

</style>
