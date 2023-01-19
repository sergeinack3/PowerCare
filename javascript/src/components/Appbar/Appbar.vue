<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="Appbar">
    <a
      class="Appbar-logo"
      :href="homeLink"
    >
      <svg
        v-if="!isTamm"
        class="Appbar-logoContent" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M16.2511 24.701C16.3478 24.6483 16.3987 24.5386 16.3763 24.4307C16.3539 24.3229 16.2636 24.2424 16.1539 24.2325C14.3817 24.0733 13.1839 23.6106 12.4216 22.9512C11.6665 22.2982 11.3117 21.4287 11.2705 20.3893C11.2289 19.3423 11.5068 18.1282 12.0189 16.8164C12.5302 15.507 13.2689 14.1154 14.1335 12.7179C17.5956 7.1218 23.0197 1.52161 23.6615 0.8992C23.7535 0.810046 23.7632 0.665834 23.684 0.565173C23.6048 0.464512 23.4624 0.439958 23.3541 0.5083C18.2578 3.72412 14.1752 7.73506 11.4172 11.5498C10.0381 13.4571 8.98735 15.3194 8.30711 17.0128C7.62855 18.7019 7.31182 20.2401 7.42105 21.4937C7.54324 23.0744 8.56571 24.5305 10.1527 25.2608C11.7459 25.9939 13.8876 25.9891 16.2511 24.701Z" stroke-width="0.5" stroke-linejoin="round" class="Appbar-logoPart up"/>
        <path d="M19.7503 11.2972C19.6535 11.3498 19.6026 11.4596 19.625 11.5675C19.6473 11.6754 19.7377 11.7559 19.8474 11.7658C21.6151 11.9248 22.8096 12.3867 23.5695 13.045C24.322 13.6969 24.6754 14.5651 24.7158 15.6031C24.7565 16.6488 24.4785 17.8615 23.9665 19.1719C23.4555 20.48 22.7175 21.8704 21.8539 23.267C18.3959 28.8591 12.9805 34.4601 12.3357 35.1031C12.2453 35.1932 12.2373 35.3369 12.3171 35.4364C12.3968 35.536 12.5387 35.5596 12.6464 35.4911C17.7244 32.2612 21.798 28.2471 24.5565 24.4338C25.9357 22.527 26.989 20.6666 27.6741 18.9755C28.3577 17.2882 28.6816 15.7524 28.5821 14.501C28.4602 12.922 27.4373 11.4675 25.8497 10.7383C24.2562 10.0064 22.1143 10.0111 19.7503 11.2972Z" stroke-width="0.5" stroke-linejoin="round" class="Appbar-logoPart bottom"/>
      </svg>
      <svg
        v-else
        class="Appbar-logoContent"
        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9.525 9.525">
        <path d="M5.316 1.834c-.819.002-1.374.365-1.697.729-.201.226-.21.316-.274.459-.285-.176-.582-.258-.819-.237-.314.029-.547.153-.547.153l-.004.002-.004.002c-.415.235-.601.591-.66.873-.031.145-.01.17-.002.264-.767.235-1.146.758-1.257 1.229a2.15 2.15 0 0 0 .006.966l.004.015c.209.626.61.976.955 1.152s.643.188.649.189c.096.017 2.243.379 3.662-1.309a.86.86 0 0 0 .289.054.9.9 0 0 0 .896-.896.9.9 0 0 0-.896-.894.9.9 0 0 0-.894.894c0 .197.067.377.176.526-1.259 1.464-3.169 1.1-3.169 1.1l-.019-.004h-.019s-.189-.006-.434-.132-.522-.354-.687-.84c-.002-.008-.086-.339-.002-.7.086-.367.295-.742 1.051-.899l.266-.055-.061-.264s-.027-.117.008-.285.119-.356.398-.517c.002-.002.14-.073.341-.092a.76.76 0 0 1 .668.251l.312.325.134-.431s.082-.266.329-.543.635-.551 1.303-.551h.011s.362-.01.718.174.706.519.706 1.378l.01.197.021.025c.061.067.153.109.251.103a.32.32 0 0 0 .243-.13l.019-.027-.023-.331-.002-.031c-.059-.897-.52-1.418-.982-1.655-.486-.256-.962-.237-.972-.237zm2.572 2.174l-.031.013c-.084.04-.151.119-.174.216s.002.195.057.27l.021.027.262.059.01.002.042.008.029.006.015.004a1.19 1.19 0 0 1 .498.266c.21.193.375.471.375.873v.019s.021.364-.115.712-.364.652-1.031.658l-2.704.019c-.23.182-.513.365-.922.532l3.624-.019c.859-.004 1.338-.517 1.527-.995.184-.467.153-.905.151-.928-.002-.551-.245-.984-.547-1.261-.233-.214-.482-.348-.729-.398l-.01-.002zM5.64 5.081a.38.38 0 0 1 .385.385.38.38 0 0 1-.385.385.38.38 0 0 1-.385-.385.38.38 0 0 1 .385-.385z" stroke-width=".019135" class="Appbar-logoPart bottom"/>
      </svg>
      <div
        v-if="isQualif"
        class="Appbar-qualif"
      >
        Qualif
      </div>
    </a>
    <div class="Appbar-navigation">
      <div v-click-outside="clickOutside">
        <div
          class="Appbar-module"
          :class="moduleClass"
          @click="displayNavModule"
        >
          <ox-icon
            class="Appbar-moduleIcon"
            icon="module"
            :size="24"
          />
          <div
            v-if="isM"
            class="Appbar-moduleName"
          >
            {{ tr("module-" + module.mod_name + "-court") }}
          </div>
        </div>
        <div
          v-if="!isTamm && showNavModule"
          class="Appbar-navModule"
        >
          <keep-alive>
            <nav-modules
              ref="navModule"
              :default-modules-data="defaultModules"
              :home-link="homeLink"
              :mobile="!isM"
              :show-home-link="!isXL"
              :tab-shortcuts="tabShortcutsData"
            />
          </keep-alive>
        </div>
        <div
          v-else-if="isTamm && showNavModule"
          class="Appbar-navModuleTamm"
        >
          <keep-alive>
            <nav-modules-tamm
              v-if="showNavModule"
              :can-see-modules="userInfo._is_admin"
              :default-modules-data="defaultModules"
              :home-link="userInfo._links.default"
              :mobile="!isM"
              ref="navModuleTamm"
              :tamm-menu="tammMenu"
            />
          </keep-alive>
        </div>
      </div>
      <div
        v-if="!isS"
        class="Appbar-tabs"
      >
        <nav-tabs
          v-if="showTabs"
        />
      </div>

      <div
        v-else
        class="Appbar-moduleCard"
        @click="displayTabs"
      >
        <div class="Appbar-moduleCardContent">
          <div class="Appbar-moduleCardTitle">
            {{ tr("module-" + module.mod_name + "-court") }}
          </div>
          <div class="Appbar-moduleCardTab">
            {{ tabName }}
          </div>
        </div>

        <ox-icon
          class="Appbar-moduleCardIcon"
          icon="chevronDown"
        />
      </div>
    </div>
    <div
      v-if="isXXL"
      id="nav-date"
      class="Appbar-date"
    >
      {{ dateNow }}
    </div>
    <div
      v-if="isXL || uniquePlaceholder"
      class="Appbar-placeholders"
    >
      <placeholder-shortcut
        v-for="(placeholder, index) in placeholders"
        :key="'placeholder-' + index"
        :placeholder="placeholder"
      />
    </div>
    <div
      v-else
      class="Appbar-placeholders"
    >
      <placeholder-shortcut
        :badged="isPlaceholderCounter"
        :placeholder="placeholderCompact"
        @click="openPlaceholders"
      />

      <v-card
        v-if="showPlaceholders"
        class="Appbar-placeholdersList"
        v-click-outside="closePlaceholders"
      >
        <placeholder-line
          v-for="(placeholder, index) of placeholders"
          :key="'placeholder-' + index"
          :placeholder="placeholder"
        />
      </v-card>
    </div>
    <div class="Appbar-context">
      <div
        v-if="isL"
        class="Appbar-group includeGroup"
        :class="groupClass"
        @click="displayGroups"
      >
        <div>
          <div class="Appbar-groupName">{{ group.text }}</div>
          <div class="Appbar-functionName">{{ currentFunction.text }}</div>
        </div>
        <ox-icon
          class="Appbar-dropIcon"
          icon="caretDown"
        />
        <keep-alive>
            <group-selector
              v-if="showGroups"
              class="Appbar-groupSelector"
              :functions="functions"
              :group-selected="group"
              v-model="showGroups"
            />
        </keep-alive>
      </div>

      <div
        v-if="isL"
        class="Appbar-account"
        :class="accountClass"
        @click="displayAccount"
      >
        <div>
          <div class="Appbar-userLastName">{{ userInfo._user_last_name }}</div>
          <div class="Appbar-userFirstName">{{ userInfo._user_first_name }}</div>
          <div class="Appbar-userInitials">{{ userInfo._initial }}</div>
        </div>
        <ox-icon
          class="Appbar-dropIcon"
          icon="caretDown"
        />
      </div>

      <div
        v-else
        @click="displayAccount"
      >
        <div
          class="Appbar-avatar"
          :style="{backgroundColor: '#' + userInfo._color, color: '#' + userInfo._font_color}"
        >
          {{ userInfo._initial }}
        </div>
      </div>

      <div
        v-if="showAccount"
        class="Appbar-userAccount"
      >
        <user-account
          :functions="functions"
          :group-selected="group"
          :info-maj="infoMaj"
          :is-tamm="isTamm"
          :show-group="!isL"
          :user-info="userInfo"
          v-model="showAccount"
        />
      </div>
    </div>

    <div
      v-if="showModuleDetail"
      class="Appbar-moduleDetail"
      v-click-outside="closeTabs"
    >
      <module-detail-mobile
        :module="module"
      />
    </div>
    <ox-notify />
  </div>
</template>

<script lang="ts" src="./Appbar.ts"></script>
<style src="./Appbar.scss" lang="scss"></style>
