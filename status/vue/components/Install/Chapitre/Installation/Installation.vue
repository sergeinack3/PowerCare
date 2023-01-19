<!-- eslint-disable vue/multi-word-component-names -->
<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="Installation">
    <div class="Installation-content">
      <INTabs
        :current-tab="currentTab"
        :tabs="tabs"
        @selecttab="selectTab">
        <div v-show="currentTab === 'Packages'">
          <INLoading v-if="!paquetsLoaded"/>
          <div v-else>
            <div class="Chapitre-title">
              {{ tr('Packages') }}
            </div>
            <div
              class="Chapitre-ambiant Chapitre-ambiantLowEmphasis"
              v-html="tr('ambiant-Paquets', this.composerUrl, this.packagistUrl)">
            </div>
            <INTable
              :data="packagesDisplayed"
              :columns="packagesColumns"
              header-tr-prefix="Packages"
              @filter="filterPackages"
              :has-action="true"
              icon-action="external-link-alt"
              field-action="url"
              @lineaction="lineClick"/>
          </div>
        </div>
        <div v-show="currentTab === 'Libraries'">
          <INLoading v-if="!librairiesLoaded"/>
          <div v-else>
            <div class="Chapitre-title">
              {{ tr('Libraries') }}
            </div>
            <div
              class="Chapitre-ambiant Chapitre-ambiantLowEmphasis"
              v-html="tr('ambiant-Librairies')">
            </div>
            <INTable
              :data="librariesDisplayed"
              :columns="librariesColumns"
              header-tr-prefix="Libraries"
              @filter="filterLibraries"
              :has-action="true"
              icon-action="external-link-alt"
              field-action="url"
              @lineaction="lineClick"/>
          </div>
        </div>
      </INTabs>
    </div>
  </div>
</template>

<script src="./Installation.ts" lang="ts"></script>

<style src="./Installation.scss" lang="scss" scoped>

</style>
