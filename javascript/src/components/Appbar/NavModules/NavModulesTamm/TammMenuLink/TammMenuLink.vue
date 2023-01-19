<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <a
    v-if="isLink"
    class="TammMenuLink"
    :href="link.href"
    :target="target"
    v-ripple
  >
    {{ link.title }}
  </a>
  <div
    v-else
    class="TammMenuLink"
    v-ripple
    @click="callFunction"
  >
    {{ link.title }}
  </div>
</template>

<script src="./TammMenuLink.ts" lang="ts"></script>
<style src="./TammMenuLink.scss" lang="scss"></style>
