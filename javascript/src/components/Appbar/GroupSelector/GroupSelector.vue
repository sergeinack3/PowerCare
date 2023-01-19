<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="GroupSelector"
    v-click-outside="{
      handler: clickOutside,
      include: includeGroup
    }"
    @click.stop=""
  >
    <div
      v-if="useSearchField"
      class="GroupSelector-search"
    >
      <ox-text-field
        ref="searchField"
        :dense="true"
        icon="search"
        :placeholder="tr('Appbar-Group-search')"
        :rounded="true"
        :value="filter"
        @change="changeFilter"
        @keydown.esc="resetFilter"
      />
    </div>
    <div
      v-if="loadGroups"
      class="GroupSelector-loading">
      <div :class="showRadio ? 'GroupSelector-skeletonCard' : 'GroupSelector-skeleton'">
        <div
          v-if="showRadio"
          class="GroupSelector-skeletonCardRadio"
        />
        <div class="GroupSelector-skeletonContent">
          <div class="GroupSelector-skeletonHeader"></div>
          <div class="GroupSelector-skeletonSubHeader"></div>
        </div>
      </div>
      <div :class="showRadio ? 'GroupSelector-skeletonCard' : 'GroupSelector-skeleton'">
        <div
          v-if="showRadio"
          class="GroupSelector-skeletonCardRadio"
        />
        <div class="GroupSelector-skeletonContent">
          <div class="GroupSelector-skeletonHeader variantMinWidth"></div>
          <div class="GroupSelector-skeletonSubHeader"></div>
        </div>
      </div>
      <div :class="showRadio ? 'GroupSelector-skeletonCard' : 'GroupSelector-skeleton'">
        <div
          v-if="showRadio"
          class="GroupSelector-skeletonCardRadio"
        />
        <div class="GroupSelector-skeletonContent">
          <div class="GroupSelector-skeletonHeader variantMaxWidth"></div>
          <div class="GroupSelector-skeletonSubHeader variantMinWidth"></div>
        </div>
      </div>
    </div>
    <component
      v-if="showCurrentGroup"
      :actived="true"
      :group="groupSelected"
      :is="groupLineOrGroupRadioComponent"
    >
    </component>
    <component
      v-for="group in filtredGroups"
      :actived="isSelected(group)"
      :function-name="getFunctionName(group)"
      :group="group"
      :is="groupLineOrGroupRadioComponent"
      :key="group._id"
      ref="groups"
      @click="selectGroup"
    />
  </div>
</template>

<script src="./GroupSelector.ts" lang="ts"></script>
<style src="./GroupSelector.scss" lang="scss"></style>
