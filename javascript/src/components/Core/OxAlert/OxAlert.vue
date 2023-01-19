<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="OxAlert">
    <div
        v-if="showAlert"
        class="OxAlert-wrapper"
    >
      <v-card>
        <v-card-text>
          {{alertMsg}}
        </v-card-text>
        <v-card-actions>
          <div class="OxAlert-actions">
            <ox-button
                v-if="showNok"
                buttonStyle="tertiary"
                :label="nokLabel"
                @click="onNokClick"
            />
            <ox-button
                :label="okLabel"
                @click="onOkClick"
            />
          </div>
        </v-card-actions>
      </v-card>
    </div>
  </div>
</template>

<script src="./OxAlert.ts" lang="ts"></script>
<style src="./OxAlert.scss" lang="scss">

</style>
