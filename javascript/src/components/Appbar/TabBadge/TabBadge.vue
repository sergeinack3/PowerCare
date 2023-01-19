<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <v-badge
    class="TabBadge"
    :color="badgeColor"
    :content="counter"
    :inline="true"
    :value="showCounter"
  ></v-badge>
</template>

<script src="./TabBadge.ts" lang="ts"></script>
<style src="./TabBadge.scss" lang="scss"></style>
