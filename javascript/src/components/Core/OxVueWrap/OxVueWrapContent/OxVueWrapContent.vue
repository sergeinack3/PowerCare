<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div>
    <v-app>
      <slot></slot>
      <ox-loading />
      <ox-notify :notification-manager="notifyManager"/>
      <ox-alert :alert-manager="alertManager"/>
      <ox-style-loader />
    </v-app>
  </div>
</template>

<script src="./OxVueWrapContent.ts" lang="ts"></script>
