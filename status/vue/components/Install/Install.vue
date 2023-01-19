<!-- eslint-disable vue/multi-word-component-names -->
<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="Install">
    <div
      class="Install-menu"
      :class="menuClassName">
      <Menu
        ref="Menu"
        :compact="getCompact"
        :selected-chapter="selectedChapter"
        :connected="connected"
        @disconnect="disconnect"
        @chapterclick="chapterClick"/>
    </div>
    <div
      class="Install-content"
      :class="contentClassName">
      <Chapitre v-if="!connected">
        <Connexion
          ref="Connexion"
          v-show="selectedChapter === 'Connexion'"
          @connect="connect"/>
      </Chapitre>
      <Chapitre
        ref="Chapitres"
        v-show="connected"
        @compact="setCompact">
        <Prerequis
          ref="Prerequis"
          v-show="selectedChapter === 'Prerequis'"/>
        <Installation
          ref="Installation"
          v-show="selectedChapter === 'Installation'"/>
        <Configuration
          ref="Configuration"
          v-show="selectedChapter === 'Configuration'"/>
        <Information
          ref="Information"
          v-show="selectedChapter === 'Information'"/>
        <ErreurLog
          ref="ErreurLog"
          v-show="selectedChapter === 'ErreurLog'"
          @compact="setCompact"/>
      </Chapitre>
      <transition
        enter-active-class="enter"
        leave-active-class="leave">
        <div
          class="Install-goTopContainer"
          v-show="getCompact">
          <GoTopButton @click="goTop"/>
        </div>
      </transition>
    </div>
  </div>
</template>

<script src="./Install.ts" lang="ts"></script>

<style src="./Install.scss" lang="scss"></style>
