<!-- eslint-disable vue/multi-word-component-names -->
<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="Connexion">
    <div class="Connexion-card">
      <INCard>
        <div class="Connexion-error"
             v-if="connectError">
          <div v-if="connectMessage">
            {{ connectMessage }}
          </div>
          <div v-else>
            {{ tr('Connexion-errorDefaultMessage') }}
          </div>
        </div>

        <INLoading
          v-show="connecting"
          :fadein="false"/>
        <div
          v-show="!connecting"
          class="Connexion-content">
          <INField
            :placeholder="tr('Connexion-login')"
            v-model="login"
            @enter="connect"
          />
          <INField
            :placeholder="tr('Connexion-password')"
            :is-password="true"
            v-model="password"
            @enter="connect"
          />

          <div class="Connexion-action">
            <INButton
              label="Connexion"
              @click="connect"/>
          </div>
        </div>
      </INCard>
    </div>
  </div>
</template>

<script src="./Connexion.ts" lang="ts"></script>

<style src="./Connexion.scss" lang="scss"></style>
