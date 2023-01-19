<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
      class="NavModulesTamm"
      ref="NavModulesTamm"
      :class="classes"
      v-click-outside="clickOutside"
      @scroll="onScroll"
  >
    <div class="NavModulesTamm-base">
      <div class="NavModulesTamm-homeSection">
        <div
          class="NavModulesTamm-homeButton"
          v-ripple
        >
          <ox-icon icon="home" size="20"/>
          <a
            class="NavModulesTamm-homeText"
            :href="homeLink"
          >
            Accueil
          </a>
        </div>
      </div>
      <div class="NavModulesTamm-linksSection">
        <div
          v-for="(section, index) in tammMenu"
          :key="'section-' + index"
          class="NavModulesTamm-linksGroup"
        >
          <div class="NavModulesTamm-linkTitle">
            {{ section.title }}
          </div>
          <div class="NavModulesTamm-links">
            <tamm-menu-link
              v-for="(link, index) in section.links"
              :key="'link-' + index"
              :link="link"
            />
          </div>
        </div>
      </div>
      <div
        v-if="canSeeModules"
        class="NavModulesTamm-divider"
      ></div>
      <div
        v-if="!mobile && canSeeModules"
        class="NavModulesTamm-cta"
        @click="toggleNav"
      >
        <span
          v-if="!expand"
          class="NavModulesTamm-ctaLabel"
        >
          {{ tr('Appbar-NavModule-plus') }}
        </span>
        <span
          v-else
          class="NavModulesTamm-ctaLabel"
        >
          {{ tr('Appbar-NavModule-less') }}
        </span>
      </div>
      <div
        v-if="canSeeModules"
        class="NavModulesTamm-modulesSection"
      >
        <div class="NavModulesTamm-modulesList">
          <div class="NavModulesTamm-title">
            {{ tr("CModule|pl") }}
          </div>
          <div  class="NavModulesTamm-searchSection">
            <ox-text-field
              ref="searchField"
              class="NavModulesTamm-searchField"
              :dense="true"
              icon="search"
              :placeholder="tr('Appbar-NavModule-search-module')"
              :rounded="true"
              :value="moduleFilter"
              @change="filterModules"
              @keydown.esc="resetSearch"
              @keydown.enter="accessToFirstModule"
            />
          </div>
          <div
            class="NavModulesTamm-modules"
            :class="modulesClasses"
          >
            <div
              v-for="(module) in modules"
              :key="module.mod_name"
            >
              <v-lazy
                v-model="module.isActive"
                height="50"
                transition=""
              >
                <module-line
                  :is-active="checkActive(module.mod_name)"
                  :module="module"
                  @detailClick="affectDetailledModule"
                />
              </v-lazy>
            </div>
          </div>
          <div
            v-if="displayEmpty"
            class="NavModulesTamm-empty"
          >
            {{ tr("Appbar-NavModule-Modules-None") }} <br>
            {{ tr("Appbar-NavModule-CTA-search") }} <br>
            <a
              class="NavModules-CTAInit"
              @click="resetSearch"
            >
              {{ tr("Appbar-NavModule-CTA-init-search") }}
            </a>
          </div>
        </div>
        <div
          v-if="showDetail"
          key="NavModulesTamm-details"
          class="NavModulesTamm-details"
        >
          <module-detail-mobile
            v-if="mobile"
            :module="detailModule"
            :showBack="true"
            @close="resetNavModules"
          />
          <module-detail
            :module="detailModule"
            @addPin="addPin"
            @changePin="setPinnedTabs"
            @removePin="removePin"
          />
        </div>
        <div
          v-else
          key="NavModulesTamm-emptyDetail"
          class="NavModulesTamm-emptyDetail"
        ></div>
      </div>
    </div>
  </div>
</template>

<script src="./NavModulesTamm.ts" lang="ts"></script>
<style src="./NavModulesTamm.scss" lang="scss"></style>
