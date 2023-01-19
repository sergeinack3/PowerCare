<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <v-card
    class="GroupRadio"
    :class="radioClass"
    outlined
    tabindex="0"
    @click="selectGroup"
  >
    <v-radio-group v-model="actived">
      <v-radio :value="true"/>
    </v-radio-group>
    <div class="GroupRadio-content">
      <div class="GroupRadio-name">
        {{ group.text }}
      </div>
      <div
        v-if="functionName"
        class="GroupRadio-function"
      >
        {{ functionName }}
      </div>
    </div>
  </v-card>
</template>

<script src="./GroupRadio.ts" lang="ts"></script>
<style src="./GroupRadio.scss" lang="scss"></style>
