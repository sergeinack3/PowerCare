<!-- eslint-disable vue/multi-word-component-names -->
<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="Prerequis">
    <INLoading v-if="!loaded"/>
    <div
      v-else
      class="Prerequis-content">
      <INTabs
        :current-tab="currentTab"
        :tabs="tabs"
        @selecttab="selectTab">
        <div v-show="currentTab === 'PHPSQLVersions'">
          <div
            class="Chapitre-ambiant Chapitre-ambiantLowEmphasis"
            v-html="tr('ambiant-PHP')">
          </div>
          <div
            class="PHPSQLVersions"
            :class="{checked: phpVersion.check, unchecked: !phpVersion.check}">
            <div class="PHPSQLVersions-content">
              <div class="PHPSQLVersions-title">
                {{ tr('PHPVersions') }}
              </div>
              <div class="PHPSQLVersions-description">
                <div v-if="!phpVersion.check">
                  {{ tr('PHPVersions-nameRequired')}} : {{ phpVersion.nameRequired }} &#183; {{ tr('PHPSQLVersions-' + (phpVersion.check ? 'checked' : 'unchecked')) }}
                </div>
                <div v-if="phpVersion.check">
                  {{ phpVersion.nameInstalled }}
                </div>
                <div v-if="phpVersion.check">
                  {{ tr('PHPVersions-nameRequired') }} : {{ phpVersion.nameRequired }}
                </div>
              </div>
            </div>
            <div class="PHPSQLVersions-illustrationContainer">
              <svg class="PHPSQLVersions-illustration" xmlns="http://www.w3.org/2000/svg" viewBox="0 -1 100 50">
                <path fill="#FFFFFF" d="m7.579 10.123 14.204 0c4.169 0.035 7.19 1.237 9.063 3.604 1.873 2.367 2.491 5.6 1.855 9.699-0.247 1.873-0.795 3.71-1.643 5.512-0.813 1.802-1.943 3.427-3.392 4.876-1.767 1.837-3.657 3.003-5.671 3.498-2.014 0.495-4.099 0.742-6.254 0.742l-6.36 0-2.014 10.07-7.367 0 7.579-38.001 0 0m6.201 6.042-3.18 15.9c0.212 0.035 0.424 0.053 0.636 0.053 0.247 0 0.495 0 0.742 0 3.392 0.035 6.219-0.3 8.48-1.007 2.261-0.742 3.781-3.321 4.558-7.738 0.636-3.71 0-5.848-1.908-6.413-1.873-0.565-4.222-0.83-7.049-0.795-0.424 0.035-0.83 0.053-1.219 0.053-0.353 0-0.724 0-1.113 0l0.053-0.053"/>
                <path fill="#FFFFFF" d="m41.093 0 7.314 0-2.067 10.123 6.572 0c3.604 0.071 6.289 0.813 8.056 2.226 1.802 1.413 2.332 4.099 1.59 8.056l-3.551 17.649-7.42 0 3.392-16.854c0.353-1.767 0.247-3.021-0.318-3.763-0.565-0.742-1.784-1.113-3.657-1.113l-5.883-0.053-4.346 21.783-7.314 0 7.632-38.054 0 0"/>
                <path fill="#FFFFFF" d="m70.412 10.123 14.204 0c4.169 0.035 7.19 1.237 9.063 3.604 1.873 2.367 2.491 5.6 1.855 9.699-0.247 1.873-0.795 3.71-1.643 5.512-0.813 1.802-1.943 3.427-3.392 4.876-1.767 1.837-3.657 3.003-5.671 3.498-2.014 0.495-4.099 0.742-6.254 0.742l-6.36 0-2.014 10.07-7.367 0 7.579-38.001 0 0m6.201 6.042-3.18 15.9c0.212 0.035 0.424 0.053 0.636 0.053 0.247 0 0.495 0 0.742 0 3.392 0.035 6.219-0.3 8.48-1.007 2.261-0.742 3.781-3.321 4.558-7.738 0.636-3.71 0-5.848-1.908-6.413-1.873-0.565-4.222-0.83-7.049-0.795-0.424 0.035-0.83 0.053-1.219 0.053-0.353 0-0.724 0-1.113 0l0.053-0.053"/>
              </svg>
            </div>
          </div>
          <div
            class="PHPSQLVersions"
            :class="{checked: sqlVersion.check, unchecked: !sqlVersion.check}">
            <div class="PHPSQLVersions-content">
              <div class="PHPSQLVersions-title">
                {{ tr('SQLVersions') }}
              </div>
              <div class="PHPSQLVersions-description">
                <div v-if="!sqlVersion.check">
                  {{ tr('PHPVersions-nameRequired')}} : {{ sqlVersion.nameRequired }} &#183; {{ tr('PHPSQLVersions-' + (sqlVersion.check ? 'checked' : 'unchecked')) }}
                </div>
                <div v-if="sqlVersion.check">
                  {{ sqlVersion.nameInstalled }}
                </div>
                <div v-if="sqlVersion.check">
                  {{ tr('PHPVersions-nameRequired') }} : {{ sqlVersion.nameRequired }}
                </div>
              </div>
            </div>
            <div class="PHPSQLVersions-illustrationContainer">
              <svg width="340.52" height="100" version="1.1" viewBox="0 0 255.38883 74.999644" xmlns="http://www.w3.org/2000/svg">
                <g transform="matrix(.69999 0 0 .66963 0 11.25)">
                  <g transform="translate(0,-1.7854)" style="fill:#fff;stroke-width:.87088">
                    <path d="m0 62.235h12.842v-50.711s19.881 44.208 19.881 44.208c2.3457 5.351 5.5567 7.244 11.854 7.244 6.2978 0 9.3847-1.893 11.731-7.244l19.88-44.208v50.711h12.843v-50.628c0-4.939-1.9766-7.327-6.0508-8.561-9.7549-3.046-16.3-0.412-19.264 6.174l-19.51 43.63-18.892-43.63c-2.8399-6.586-9.5079-9.22-19.263-6.174-4.0752 1.234-6.0508 3.622-6.0508 8.561v50.628z" style="fill:#fff"/>
                  </g>
                  <g transform="translate(99.706,19.038)" style="fill:#fff;stroke-width:.87088">
                    <path d="m2e-3 0.136h12.838v27.938c-0.12 1.518 0.487 5.08 7.522 5.191 3.589 0.056 27.7 0 27.925 0v-33.265h12.868c0.059 0-0.013 45.364-0.012 45.557 0.071 11.188-13.882 13.618-20.313 13.806h-40.549v-8.639c0.071 0 40.519 8e-3 40.621-2e-3 8.265-0.873 7.289-4.981 7.288-6.364v-3.368h-27.295c-12.699-0.117-20.786-5.659-20.886-12.035-9e-3 -0.591 0.274-28.54-7e-3 -28.819z" style="fill:#fff"/>
                  </g>
                  <g transform="translate(170.76,1.1786)" style="fill:#fff;stroke-width:.87088">
                    <path d="m0 59.271h36.92c4.322 0 8.521-0.905 11.855-2.47 5.556-2.551 8.273-6.008 8.273-10.537v-9.384c0-3.705-3.087-7.163-9.138-9.467-3.211-1.236-7.162-1.894-10.99-1.894h-15.559c-5.185 0-7.655-1.564-8.272-5.021-0.124-0.412-0.124-0.742-0.124-1.153v-5.844c0-0.33 0-0.66 0.124-1.071 0.617-2.634 1.976-3.376 6.544-3.787 0.371 0 0.864-0.083 1.235-0.083h36.674v-8.56h-36.057c-5.186 0-7.903 0.329-10.372 1.069-7.656 2.388-10.99 6.175-10.99 12.761v7.491c0 5.763 6.545 10.702 17.534 11.854 1.236 0.082 2.47 0.164 3.704 0.164h13.336c0.495 0 0.989 0 1.359 0.083 4.075 0.33 5.803 1.07 7.039 2.552 0.74 0.74 0.987 1.482 0.987 2.305v7.491c0 0.906-0.617 2.059-1.851 3.046-1.112 0.988-2.964 1.647-5.434 1.812-0.494 0-0.864 0.082-1.358 0.082h-35.439z" style="fill:#fff"/>
                  </g>
                  <g transform="translate(307.92,1.1786)" style="fill:#fff;stroke-width:.87088">
                    <path d="m0 44.371c0 8.808 6.544 13.748 19.757 14.736 1.234 0.082 2.47 0.164 3.704 0.164h33.464v-8.561h-33.711c-7.532 0-10.372-1.894-10.372-6.421v-44.289h-12.842z" style="fill:#fff"/>
                  </g>
                  <g transform="translate(236,1.2876)" style="fill:#fff;stroke-width:.87088">
                    <path d="m0 44.711v-30.522c0-7.754 5.445-12.457 16.209-13.942 1.114-0.164 2.352-0.247 3.465-0.247h24.376c1.237 0 2.352 0.083 3.589 0.247 10.765 1.485 16.209 6.188 16.209 13.942v30.522c0 6.291-2.312 9.658-7.641 11.852l12.65 11.418h-14.911l-10.234-9.238-10.303 0.653h-13.735c-2.351 0-4.825-0.331-7.548-1.073-8.166-2.227-12.126-6.517-12.126-13.612zm13.861-0.742c0 0.413 0.123 0.825 0.247 1.32 0.742 3.547 4.083 5.527 9.156 5.527h11.667l-10.717-9.675h14.911l9.347 8.438c1.722-0.918 2.856-2.322 3.252-4.125 0.123-0.412 0.123-0.825 0.123-1.237v-29.285c0-0.33 0-0.743-0.123-1.156-0.743-3.299-4.084-5.196-9.033-5.196h-19.427c-5.691 0-9.403 2.475-9.403 6.352z" style="fill:#fff"/>
                  </g>
                </g>
              </svg>

            </div>
          </div>
        </div>
        <div v-show="currentTab === 'PHPExtensions'">
          <div class="Chapitre-title">
            {{ tr('PHPExtensions') }}
          </div>
          <div
            class="Chapitre-ambiant Chapitre-ambiantLowEmphasis"
            v-html="tr('ambiant-PHPExtensions')">
          </div>
          <INTable
            :data="displayedPHPExtensions"
            :columns="phpExtensionsColumns"
            header-tr-prefix="PHPExtensions"
            @filter="filterPHPExtensions"/>
        </div>
        <div v-show="currentTab === 'URLRestrictions'">
          <div class="Chapitre-title">
            {{ tr('URLRestrictions') }}
          </div>
          <div
            class="Chapitre-ambiant Chapitre-ambiantLowEmphasis"
            v-html="tr('ambiant-URLRestrictions')">
          </div>
          <INTable
            :data="displayedURLRestrictions"
            :columns="urlRestrictionsColumns"
            header-tr-prefix="URLRestrictions"
            @filter="filterURLRestrictions"/>
        </div>
        <div v-show="currentTab === 'PathAccess'">
          <div class="Chapitre-title">
            {{ tr('PathAccess') }}
          </div>
          <div
            class="Chapitre-ambiant Chapitre-ambiantLowEmphasis"
            v-html="tr('ambiant-PathAccess')">
          </div>
          <INTable
            :data="displayedPathAccess"
            :columns="pathAccessColumns"
            header-tr-prefix="PathAccess"
            @filter="filterPathAccess"/>
        </div>
      </INTabs>
    </div>
  </div>
</template>

<script src="./Prerequis.ts" lang="ts"></script>

<style src="./Prerequis.scss" lang="scss" scoped>

</style>
