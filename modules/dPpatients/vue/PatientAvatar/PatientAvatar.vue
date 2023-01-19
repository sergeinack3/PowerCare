<!--
  @package Mediboard\Patient
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <v-avatar
    :color="patientSexeColor"
    :size="size"
  >
    <img
      v-if="profilId"
      :src="imgSrc"
    >
    <ox-icon
      v-else
      icon="account"
      :size="iconSize"
    />
  </v-avatar>
</template>

<script src="./PatientAvatar.ts" lang="ts" />
