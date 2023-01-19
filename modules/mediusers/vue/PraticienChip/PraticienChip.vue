<!--
  @package Mediboard\Mediusers
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <v-avatar
    v-if="small"
    :color="color"
    :size="28"
  >
    <span class="white--text PraticienChip-initials">
      {{initials}}
    </span>
  </v-avatar>
  <v-chip v-else>
    <v-avatar
      :color="color"
      left
    >
      <span class="white--text PraticienChip-initials">
        {{initials}}
      </span>
    </v-avatar>
    <span class="PraticienChip-name">
      {{name}}
    </span>
  </v-chip>
</template>

<script src="./PraticienChip.ts" lang="ts" />
<style src="./PraticienChip.scss" lang="scss">

</style>
