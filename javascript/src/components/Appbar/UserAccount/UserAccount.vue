<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="UserAccount"
    v-click-outside="clickOutside"
  >
    <div class="UserAccount-accountCard">
      <div class="UserAccount-accountInfo">
        <div
          class="UserAccount-avatar"
          :style="{backgroundColor: '#' + userInfo._color, color: '#' + userInfo._font_color}"
        >
          {{ userInfo._initial }}
        </div>
        <div class="UserAccount-user">
          <div class="UserAccount-username">
            {{ userInfo._user_first_name }} {{ userInfo._user_last_name}}
          </div>
          <div class="UserAccount-login">
            {{ userInfo._user_username }}
          </div>
        </div>
      </div>
      <div class="UserAccount-accountCTA">
        <ox-button
          v-if="!isTamm"
          button-style="tertiary"
          :label="tr('Appbar-manage-my-account')"
          @click="editAccount"
        />
      </div>
      <div
        class="UserAccount-accountBackground"
        :style="{backgroundColor: '#' + userInfo._color}"
      ></div>
    </div>
    <v-list class="UserAccount-actions">
      <v-list-item-group active-class="">
        <v-list-item
          v-if="isNotPatient"
          @click="lockSession"
        >
          <v-list-item-icon class="UserAccount-listItemIcon">
            <ox-icon icon="lock" />
          </v-list-item-icon>
          <v-list-item-content>
            <v-list-item-title>{{ tr("menu-lockSession") }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item
          v-if="isNotPatient"
          @click="switchUser"
        >
          <v-list-item-icon class="UserAccount-listItemIcon">
            <ox-icon icon="accountSwitch" />
          </v-list-item-icon>
          <v-list-item-content>
            <v-list-item-title>{{ tr("menu-switchUser") }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item
          v-if="showPassword"
          @click="changePassword">
          <v-list-item-icon class="UserAccount-listItemIcon">
            <ox-icon icon="key" />
          </v-list-item-icon>
          <v-list-item-content>
            <v-list-item-title>{{ tr('menu-changePassword') }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item @click="showCGU">
          <v-list-item-icon class="UserAccount-listItemIcon">
          </v-list-item-icon>
          <v-list-item-content>
            <v-list-item-title class="UserAccount-cgu">{{ tr('ProSanteConnect-cgu') }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-divider></v-divider>
        <v-list-item
          inactive
          :ripple="false"
        >
          <v-list-item-content>
            <v-list-item-title>{{ tr('pref-mediboard_ext_dark') }}</v-list-item-title>
          </v-list-item-content>
          <v-list-item-icon>
            <ox-switch
              :value="darkTheme"
              @change="switchTheme"
            />
          </v-list-item-icon>
        </v-list-item>
        <v-divider></v-divider>
        <v-list-item
          inactive
          :ripple="false"
        >
          <ox-button
            :block="true"
            :label="tr('menu-logout')"
            @click="logout"
          />
        </v-list-item>
      </v-list-item-group>
    </v-list>

    <keep-alive include="GroupSelector">
      <div
        v-if="showGroup"
        class="UserAccount-groups"
      >
        <div>{{ tr('Appbar-Groups') }}</div>

        <group-selector
          :functions="functions"
          :group-selected="groupSelected"
          :show-radio="true"
        />
      </div>
    </keep-alive>

    <div
      v-if="showMajInfo"
      class="UserAccount-majInfo"
      :title="infoMaj.title"
    >
      {{ infoMaj.release_title }}
    </div>
  </div>
</template>

<script src="./UserAccount.ts" lang="ts"></script>
<style src="./UserAccount.scss" lang="scss"></style>
