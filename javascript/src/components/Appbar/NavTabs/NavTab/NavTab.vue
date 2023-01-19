<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="NavTab"
    :class="classes"
    @mouseout="leaveTab"
    @mouseover="enterTab"
  >
    <a
      class="NavTab-content"
      :class="contentClasses"
      :href="tabLink"
      @click="clickTab"
      @contextmenu="show"
    >
      {{ tabName }}
    </a>
    <div class="NavTab-badge">
      <tab-badge
        :tab="tab"
      />
    </div>
    <v-menu
      v-if="showPin"
      v-model="showMenu"
      absolute
      offset-y
      :position-x="x"
      :position-y="y"
    >
      <v-list>
        <v-list-item-group>
          <v-list-item
            v-if="!isPinned"
            key="addPinned"
            @click="addPinnedClick"
          >
            <v-list-item-content>
              <v-list-item-title>{{ tr('Appbar-Tab-add-pinned') }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            v-else
            key="removePinned"
            @click="removePinnedClick"
          >
            <v-list-item-content>
              <v-list-item-title>{{ tr('Appbar-Tab-remove-pinned') }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-item-group>
      </v-list>
    </v-menu>
  </div>
</template>

<script src="./NavTab.ts" lang="ts"></script>
<style src="./NavTab.scss" lang="scss"></style>
