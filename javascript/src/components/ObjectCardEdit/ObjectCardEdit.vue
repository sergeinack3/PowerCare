<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <v-card class="ObjectCardEdit">
    <div class="ObjectCardEdit">
      <div class="ObjectCardEdit-head">
        <div class="ObjectCardEdit-titleContainer">
          <div class="ObjectCardEdit-iconContainer">
            <ox-icon
              class-name="ObjectCardEdit-icon"
              :icon="icon"
              size="18"
            />
          </div>
          <div class="ObjectCardEdit-title">
            {{title}}
          </div>
        </div>
        <slot name="head"></slot>
      </div>
      <div class="ObjectCardEdit-body">
        <slot name="body"></slot>
      </div>
      <v-spacer></v-spacer>
      <div class="ObjectCardEdit-actions">
        <slot name="actions"></slot>
      </div>
    </div>
  </v-card>
</template>

<script src="./ObjectCardEdit.ts" lang="ts"></script>
<style src="./ObjectCardEdit.scss" lang="scss">

</style>
