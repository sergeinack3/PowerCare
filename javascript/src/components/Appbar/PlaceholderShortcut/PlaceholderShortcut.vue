<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <ox-tooltip ref="placeholder" position="bottom">
    <template v-slot:content="{ on }">
      <ox-badge
        v-if="showCounter"
        color="secondary"
        :content="placeholder.counter + ''"
        :dot="badged"
      >
        <div
          class="PlaceholderShortcut"
          :id="placeholder._id + '_placeholder'"
          v-on="on"
          @click="click"
        >
          <svg
            v-if="showAppfine"
            class="PlaceholderShortcut-customIcon"
            viewBox="0 0 138.4 80.2" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
            <path d="m33.8 31.2" stroke-linecap="round" stroke-linejoin="round"
                stroke-miterlimit="10" stroke-width="4"/>
            <path d="m24.3 49.7" stroke-linecap="round" stroke-linejoin="round"
                  stroke-miterlimit="10" stroke-width="4"/>
            <path d="m1.5 49.7" stroke-linecap="round" stroke-linejoin="round"
                  stroke-miterlimit="10" stroke-width="4"/>
            <circle cx="3.2" cy="49.7" r="3.2"/>
            <path
              d="m4.4 47.7h18.9l9.2-17.2s0.5-1.2 1.9-1.2c0 0 1.5 0.2 2 1.9l8.1 30.8 9.4-21.2s0.7-2 3.6-2h32.7v3.8l-32.9 0.1-11 25.4s-0.5 2-2.1 2c0 0-1.8 0.2-2.2-2l-8.2-31.3-6.8 12.9s-0.9 2.1-3.1 2.1c-2.7 0-19.9-0.2-19.9-0.2z"/>
            <path
              d="m59 32c3.7-18.3 19.9-32 39.3-32 22.2 0 40.1 18 40.1 40.1s-17.9 40.1-40.1 40.1c-19.1 0-35-13.3-39.1-31.2h5.2c4 14.6 17.4 25.3 33.3 25.3 19.1 0 34.6-15.5 34.6-34.6s-15.5-34.5-34.6-34.5c-16.4 0-30.2 11.5-33.7 26.8z"/>
            <path
              d="m79.7 31.9c3.2-6.5 9.8-11 17.6-11 10.8 0 19.5 8.7 19.5 19.5s-8.7 19.5-19.5 19.5c-7.7 0-13.9-4.4-17.1-10.9h-6.3c0.3 0.7 0.6 1.4 0.9 2.1 4.1 8.3 12.6 14 22.5 14 13.8 0 25-11.2 25-25s-11.2-25-25-25c-9.9 0-18.5 5.8-22.5 14.1-0.4 0.8-0.8 1.7-1.1 2.6 0 0.1-0.1 0.1-0.1 0.2z"/>
            <path d="m120.3 40.6"/>
            <circle cx="97.9" cy="40.1" r="10.2"/>
          </svg>
          <ox-icon
            v-else-if="showIcon"
            :icon="placeholder.icon"
            :size="16"
          />
          <div
            v-else
            class="PlaceholderShortcut-labelNumber"
          >
            +{{ placeholder.icon }}
          </div>
        </div>
      </ox-badge>
      <div
        v-else
        class="PlaceholderShortcut"
        :id="placeholder._id + '_placeholder'"
        v-on="on"
        @click="click"
      >
        <div
          v-if="showEcap"
          class="PlaceholderShortcut-ecap"
        ></div>
        <svg
          v-else-if="showAppfine"
          class="PlaceholderShortcut-customIcon"
          viewBox="0 0 138.4 80.2" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
          <path d="m33.8 31.2" stroke-linecap="round" stroke-linejoin="round"
                stroke-miterlimit="10" stroke-width="4"/>
          <path d="m24.3 49.7" stroke-linecap="round" stroke-linejoin="round"
                stroke-miterlimit="10" stroke-width="4"/>
          <path d="m1.5 49.7" stroke-linecap="round" stroke-linejoin="round"
                stroke-miterlimit="10" stroke-width="4"/>
          <circle cx="3.2" cy="49.7" r="3.2"/>
          <path
            d="m4.4 47.7h18.9l9.2-17.2s0.5-1.2 1.9-1.2c0 0 1.5 0.2 2 1.9l8.1 30.8 9.4-21.2s0.7-2 3.6-2h32.7v3.8l-32.9 0.1-11 25.4s-0.5 2-2.1 2c0 0-1.8 0.2-2.2-2l-8.2-31.3-6.8 12.9s-0.9 2.1-3.1 2.1c-2.7 0-19.9-0.2-19.9-0.2z"/>
          <path
            d="m59 32c3.7-18.3 19.9-32 39.3-32 22.2 0 40.1 18 40.1 40.1s-17.9 40.1-40.1 40.1c-19.1 0-35-13.3-39.1-31.2h5.2c4 14.6 17.4 25.3 33.3 25.3 19.1 0 34.6-15.5 34.6-34.6s-15.5-34.5-34.6-34.5c-16.4 0-30.2 11.5-33.7 26.8z"/>
          <path
            d="m79.7 31.9c3.2-6.5 9.8-11 17.6-11 10.8 0 19.5 8.7 19.5 19.5s-8.7 19.5-19.5 19.5c-7.7 0-13.9-4.4-17.1-10.9h-6.3c0.3 0.7 0.6 1.4 0.9 2.1 4.1 8.3 12.6 14 22.5 14 13.8 0 25-11.2 25-25s-11.2-25-25-25c-9.9 0-18.5 5.8-22.5 14.1-0.4 0.8-0.8 1.7-1.1 2.6 0 0.1-0.1 0.1-0.1 0.2z"/>
          <path d="m120.3 40.6"/>
          <circle cx="97.9" cy="40.1" r="10.2"/>
        </svg>
        <ox-icon
          v-else-if="showIcon"
          :icon="placeholder.icon"
          :size="16"
        />
        <div
          v-else
          class="PlaceholderShortcut-labelNumber"
        >
          +{{ placeholder.icon }}
        </div>
      </div>
    </template>
    <div v-if="showLabel">{{ placeholder.label }}</div>
  </ox-tooltip>
</template>

<script src="./PlaceholderShortcut.ts" lang="ts"></script>
<style src="./PlaceholderShortcut.scss" lang="scss"></style>
