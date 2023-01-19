<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="OxNotify">
      <div
        v-for="notification in notificationDisplayed"
        :key="'notif' + notification.key"
        class="OxNotify-line"
        :class="notifyClass(notification)"
      >
        <v-container>
          <v-row align="center">
            <v-col class="grow py-0 pr-0">
              <div class="OxNotify-libelle">
                {{ notification.libelle }}
              </div>
            </v-col>
            <v-col
                v-if="notification.button"
                class="shrink py-0 pl-2 pr-0"
            >
              <ox-button
                :custom-class="closeClass(notification)"
                button-style="tertiary-light"
                :label="notification.button.libelle"
                @click="notificationButtonCallback(notification)"
              />
            </v-col>
            <v-col class="shrink py-0 pl-2 pr-0">
              <ox-button
                :custom-class="closeClass(notification) + ' mainButton'"
                button-style="tertiary-light"
                icon="cancel"
                @click="removeNotification(notification)"
              />
            </v-col>
          </v-row>
        </v-container>
      </div>
  </div>
</template>

<script src="./OxNotify.ts" lang="ts"></script>
<style src="./OxNotify.scss" lang="scss">

</style>
