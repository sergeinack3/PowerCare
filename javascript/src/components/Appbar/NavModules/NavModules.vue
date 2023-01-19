<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
      class="NavModules"
      :class="classes"
      v-click-outside="clickOutside"
  >
    <div class="NavModules-navigation">
      <a
        v-if="showHomeLink"
        :href="homeLink"
        class="NavModules-homeButton"
      >
        <ox-button
          button-style="tertiary"
          icon="home"
          :label="tr('Home')"
        />
      </a>

      <div class="NavModules-title">
        {{ tr("Appbar-NavModule-tab-frequently-consulted") }}
      </div>
      <div
        v-if="showFavTabs"
        key="favTabs"
        class="NavModules-shortcuts"
      >
        <div
          v-for="(tab, index) in tabsFav"
          :key="index"
          class="NavModules-tabShortcut"
        >
          <tab-shortcut
            :key="index"
            ref="shortcuts"
            :module-category="getModuleCategory(tab.mod_name)"
            :mobile="mobile"
            :tab="tab"
          />
        </div>
      </div>
      <div
        v-else
        key="noFavTabs"
        class="NavModules-emptyShortcuts"
      >
        <svg class="NavModules-noFavTabIllus" viewBox="0 0 155 72" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="77" cy="36" r="36" fill="black" fill-opacity="0.03"/>
          <rect y="11" width="127" height="35" rx="4" fill="white"/>
          <rect x="0.5" y="11.5" width="126" height="34" rx="3.5" stroke="#263238" stroke-opacity="0.12"/>
          <ellipse cx="12.7001" cy="23.6001" rx="7.05556" ry="7" fill="#3F51B5"/>
          <rect x="27.5166" y="18" width="70.5556" height="7.7" rx="3.85" fill="#ECEFF1"/>
          <rect x="27.5166" y="31.3" width="91.0167" height="8.4" rx="4.2" fill="#CFD8DC"/>
          <rect x="28" y="24" width="127" height="35" rx="4" fill="white"/>
          <rect x="28.5" y="24.5" width="126" height="34" rx="3.5" stroke="#263238" stroke-opacity="0.12"/>
          <ellipse cx="40.7001" cy="36.6001" rx="7.05556" ry="7" fill="#03A9F4"/>
          <rect x="55.5166" y="31" width="57.15" height="7.7" rx="3.85" fill="#ECEFF1"/>
          <rect x="55.5166" y="44.3" width="74.0833" height="8.4" rx="4.2" fill="#CFD8DC"/>
        </svg>
        <div>
          <div class="NavModules-noFavTabTextTitle">
            {{ tr('Appbar-NavModule-noFavTab-title') }}
          </div>
          <div class="NavModules-noFavTabTextDesc">
            {{ tr('Appbar-NavModule-noFavTab-desc') }}
          </div>
        </div>
      </div>
      <div class="NavModules-title">
        {{ tr("CModule|pl") }}
      </div>
      <ox-text-field
        v-if="moreModules"
        ref="searchField"
        class="NavModules-searchField"
        :dense="true"
        icon="search"
        :placeholder="tr('Appbar-NavModule-search-module')"
        :rounded="true"
        :value="moduleFilter"
        @change="filterModules"
        @keydown.esc="resetSearch"
        @keydown.enter="accessToFirstModule"
      />
      <div
        class="NavModules-modules"
        :class="modulesClasses"
        v-scroll.self="onScroll"
      >
        <div
          v-for="(module, index) in modules"
          :key="module.mod_name"
        >
          <v-lazy
            v-model="module.isActive"
            height="50"
            transition=""
          >
            <module-line
              :is-active="checkActive(module.mod_name)"
              :is-focus="checkFocus(index)"
              :module="module"
              @detailClick="affectDetailledModule"
            />
          </v-lazy>
        </div>
      </div>
      <div
        v-if="displayEmpty"
        class="NavModules-empty"
      >
        {{ tr("Appbar-NavModule-Modules-None") }} <br>
        {{ tr("Appbar-NavModule-CTA-search") }}
        <a
          class="NavModules-CTAInit"
          @click="resetSearch"
        >
          {{ tr("Appbar-NavModule-CTA-init-search") }}
        </a>
      </div>
      <div
        v-if="moreModules && !displayEmpty && !mobile"
        class="NavModules-cta"
        @click="expandNav"
      >
        {{ tr('Appbar-NavModule-plus') }}
      </div>
    </div>
    <div
      v-if="showDetail"
      class="NavModules-details"
    >
      <module-detail-mobile
        v-if="mobile"
        :module="detailModule"
        :showBack="true"
        @close="resetNavModules"
      />
      <module-detail
        v-else
        :focus-tab-index="focusTabIndex"
        :module="detailModule"
        @addPin="addPin"
        @changePin="setPinnedTabs"
        @removePin="removePin"
        @unsetFocus="unsetFocusDetail"
      />
    </div>
  </div>
</template>

<script lang="ts" src="./NavModules.ts"></script>
<style src="./NavModules.scss" lang="scss"></style>
