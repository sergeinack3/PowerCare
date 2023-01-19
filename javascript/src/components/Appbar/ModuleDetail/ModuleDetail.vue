<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="ModuleDetail">
    <div
      v-if="showDetail"
      key="content"
      class="ModuleDetail-mainContent"
    >
      <div class="ModuleDetail-header">
        <div
          class="ModuleDetail-title"
          @click="redirectToModule"
        >
          {{ tr("module-" + module.mod_name + "-court") }}
        </div>
        <div class="ModuleDetail-icon">
          <ox-module-icon
            :module-category="module.mod_category"
            :module-name="module.mod_name"
          />
        </div>
      </div>
      <div class="ModuleDetail-content">
        <draggable
          v-if="showPinnedTabs"
          v-model="currentPinnedTabs"
          :animation="150"
          :disabled="disableDrag"
        >
          <tab-line
            v-for="(tab, index) in module.pinned_tabs"
            :key="index"
            :is-active="checkTabActive(tab.tab_name)"
            :is-focus="checkFocus(index)"
            :module-name="module.mod_name"
            :tab="tab"
            :pined="true"
            @changePin="removePin"
            @unsetFocus="unsetFocus"
          />
        </draggable>
        <div v-if="showStandardTabs">
          <tab-line
            v-for="(tab, index) in standardTabs"
            :key="index"
            class="ModuleDetail-tab"
            :is-active="checkTabActive(tab.tab_name)"
            :is-focus="checkFocusForStandard(index)"
            :module-name="module.mod_name"
            :tab="tab"
            @changePin="addPin"
            @unsetFocus="unsetFocus"
          />
        </div>
      </div>
      <div
        v-if="showFooter"
        class="ModuleDetail-footer"
      >
        <tab-line
          v-if="showParam"
          :is-active="checkTabActive('', 'param')"
          :is-focus="checkFocusForParam"
          :module-name="module.mod_name"
          :param="true"
          :show-pin="false"
          :tab="paramTab"
          @unsetFocus="unsetFocus"
        />
        <tab-line
          v-if="showConfig"
          :is-active="checkTabActive('','config')"
          :is-focus="checkFocusForConfig"
          :module-name="module.mod_name"
          :show-pin="false"
          :tab="configTab"
          @unsetFocus="unsetFocus"
        />
      </div>
      <div class="ModuleDetail-cta">
        <ox-button
          button-style="tertiary"
          :label="tr('Appbar-NavModule-DetailModule-CTA-access')"
          @click="redirectToModule"
        />
      </div>
    </div>
    <div
      v-else
      key="skeleton"
      class="ModuleDetailSkeleton"
    >
      <div class="ModuleDetailSkeleton-header">
        <div class="ModuleDetailSkeleton-title"></div>
        <div class="ModuleDetailSkeleton-icon"></div>
      </div>
      <div class="ModuleDetailSkeleton-content">
        <div class="ModuleDetailSkeleton-tabLine"></div>
        <div class="ModuleDetailSkeleton-tabLine opacity-80 variantWidth1"></div>
        <div class="ModuleDetailSkeleton-tabLine opacity-70"></div>
        <div class="ModuleDetailSkeleton-tabLine opacity-60 variantWidth2"></div>
        <div class="ModuleDetailSkeleton-tabLine opacity-50 variantWidth3"></div>
      </div>
      <div class="ModuleDetailSkeleton-cta">
        <ox-button
          button-style="tertiary"
          :label="tr('Appbar-NavModule-DetailModule-CTA-access')"
        />
      </div>
    </div>
  </div>
</template>

<script src="./ModuleDetail.ts" lang="ts"></script>
<style src="./ModuleDetail.scss" lang="scss"></style>
