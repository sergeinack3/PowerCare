<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="NavTabs">
    <draggable
      v-if="showPinnedTabs"
      v-model="currentPinnedTabs"
      :animation="150"
      class="NavTabs-pinnedTabs"
      :disabled="disableDrag"
      @end="endDrag"
      @start="startDrag"
    >
        <nav-tab
            v-for="(tab) in currentPinnedTabs"
            :key="tab.tab_name"
            :is-dragging="drag"
            :parent-hover="hover"
            :tab="tab"
            :tab-active="isActive(tab.tab_name)"
            @enter="enterTabs"
            @leave="leaveTabs"
            @removePin="removePin"
        />
    </draggable>
    <div
      class="NavTabs-standard"
      @click="moreClick"
    >
      <nav-tab
        v-if="showCurrentStandardTab"
        :class="standardTabClasses"
        :is-pinned="false"
        :param="currentTabIsParam"
        :show-pin="tabActiveIsPinnable"
        :tab-active="true"
        :tab="{ tab_name: tabActive, _links: { tab_url: null } }"
        @addPin="addPin"
      />
    </div>
    <div
      class="NavTabs-moreContainer"
      v-click-outside="{
        handler: clickOutside,
        include: includeGroup
      }"
    >
      <div
        v-if="showMoreTabs"
        class="NavTabs-moreTab"
        :class="moreTabsTabClass"
        @click="moreClick"
      >
        <ox-icon icon="chevronDown" />
      </div>
      <div
        v-if="showMoreTabs"
        class="NavTabs-tabSelector"
      >
        <keep-alive>
          <tab-selector
            v-if="showSelector"
            :configure="configureTab"
            :param="paramTab"
            :tabs="currentStandardTabs"
            :value="showSelector"
            @addPin="addPin"
            @removePin="removePin"
          />
        </keep-alive>
      </div>
    </div>
  </div>
</template>

<script src="./NavTabs.ts" lang="ts"></script>
<style src="./NavTabs.scss" lang="scss"></style>
