<!-- eslint-disable vue/multi-word-component-names -->
<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="Menu">
    <div class="Menu-content">
      <div
        class="Menu-title"
        @click="menuClick"
        :title="tr('BackToMediboard')">
        <div class="Menu-titleIcon">
          <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16.2375 24.3C11.7 26.775 8.1375 24.2625 7.9125 21.3375C7.5 16.6125 13.5 7.19999 23.475 0.899994C22.2 2.13749 2.0625 23.025 16.2375 24.3Z" fill="#FFFFFF"/>
            <path d="M19.875 11.55C24.4125 9.07498 27.975 11.5875 28.2 14.5125C28.575 19.2375 22.575 28.65 12.6375 34.9875C13.9125 33.7125 34.0125 12.825 19.875 11.55Z" fill="#FFFFFF"/>
          </svg>
        </div>
        <div
          class="Menu-titleLabel"
          :class="labelClassName">
          {{ tr('AssistantStatus') }}
        </div>
      </div>
      <MenuIcon
        :compact="compact"
        v-if="!connected"
        icon="key"
        chapter="Connexion"
        :label="tr('Connexion')"
        :selected-chapter="selectedChapter"/>

      <MenuIcon
        :compact="compact"
        v-if="connected"
        @chapterclick="chapterClick"
        icon="tasks"
        chapter="Prerequis"
        :label="tr('Prerequis')"
        :selected-chapter="selectedChapter"/>
      <MenuIcon
        :compact="compact"
        v-if="connected"
        @chapterclick="chapterClick"
        icon="cogs"
        chapter="Installation"
        :label="tr('Status')"
        :selected-chapter="selectedChapter"
        :has-flag="true"
        :flag-icon="checkDependancesInfo.icon"
        :flag-state="checkDependancesInfo.state"/>
      <MenuIcon
        :compact="compact"
        v-if="connected"
        @chapterclick="chapterClick"
        icon="wrench"
        chapter="Configuration"
        :label="tr('Configuration')"
        :selected-chapter="selectedChapter"/>
      <MenuIcon
        :compact="compact"
        v-if="connected"
        @chapterclick="chapterClick"
        icon="info"
        chapter="Information"
        :label="tr('Information')"
        :selected-chapter="selectedChapter"/>
      <MenuIcon
        :compact="compact"
        v-if="connected"
        @chapterclick="chapterClick"
        icon="file-medical-alt"
        chapter="ErreurLog"
        :label="tr('ErreurLog')"
        :selected-chapter="selectedChapter"/>
      <div
        v-if="connected"
        class="Menu-disconnect">
        <Deconnexion
          :lib="tr('ToDisconnect')"
          @disconnect="disconnect"/>
      </div>
    </div>
  </div>
</template>

<script src="./Menu.ts" lang="ts"></script>

<style src="./Menu.scss" scoped lang="scss"></style>
