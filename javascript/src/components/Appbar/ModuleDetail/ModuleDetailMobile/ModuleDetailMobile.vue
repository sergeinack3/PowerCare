<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="ModuleDetailMobile">
    <div
      v-if="showBack"
      class="ModuleDetailMobile-back"
    >
      <ox-button
        button-style="tertiary"
        icon="chevronLeft"
        :label="tr('CModule.all')"
        @click="back"
      />
    </div>

    <div
      v-if="module"
      key="content"
      class="ModuleDetailMobile-mainContent"
    >
      <div class="ModuleDetailMobile-header">
        <div class="ModuleDetailMobile-icon">
          <ox-module-icon
            :module-category="module.mod_category"
            :module-name="module.mod_name"
          />
        </div>
        <div
          class="ModuleDetailMobile-title"
          @click="redirectToModule"
        >
          {{ tr("module-" + module.mod_name + "-court") }}
        </div>
      </div>
      <div class="ModuleDetailMobile-content">
        <tab-line
          v-for="(tab, index) in module.pinned_tabs"
          :key="index"
          :is-active="checkTabActive(tab.tab_name)"
          :module-name="module.mod_name"
          :tab="tab"
          :pined="true"
          @changePin="removePin"
        />

        <div v-if="showStandardTabs">
          <tab-line
            v-for="(tab, index) in standardTabs"
            :key="index"
            class="ModuleDetailMobile-tab"
            :is-active="checkTabActive(tab.tab_name)"
            :module-name="module.mod_name"
            :tab="tab"
            @changePin="addPin"
          />
        </div>
      </div>
      <div
        v-if="showFooter"
        class="ModuleDetailMobile-footer"
      >
        <tab-line
          v-if="showParam"
          :is-active="checkTabActive('', 'param')"
          :module-name="module.mod_name"
          :param="true"
          :show-pin="false"
          :tab="paramTab"
        />
        <tab-line
          v-if="showConfig"
          :is-active="checkTabActive('','config')"
          :module-name="module.mod_name"
          :show-pin="false"
          :tab="configTab"
        />
      </div>
    </div>

    <div
      v-else
      key="skeleton"
      class="ModuleDetailMobileSkeleton"
    >
      <div class="ModuleDetailMobileSkeleton-header">
        <div class="ModuleDetailMobileSkeleton-icon"></div>
        <div class="ModuleDetailMobileSkeleton-title"></div>
      </div>
      <div class="ModuleDetailMobileSkeleton-content">
        <div class="ModuleDetailMobileSkeleton-tabLine"></div>
        <div class="ModuleDetailMobileSkeleton-tabLine variantWidth1"></div>
      </div>
    </div>
  </div>
</template>

<script src="./ModuleDetailMobile.ts" lang="ts"></script>
<style src="./ModuleDetailMobile.scss" lang="scss"></style>
