<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <v-dialog
    ref="dialog-date"
    v-model="modal"
    width="400px"
  >
    <template v-slot:activator="{ on }">
      <v-text-field
        :append-icon="calendarIcon"
        :background-color="fieldBG"
        :clearable="!notNull"
        :color="fieldColor"
        :dark="onPrimary"
        :disabled="disabled"
        filled
        hide-details="auto"
        :hint="hint"
        :label="labelComputed"
        :loading="showLoading"
        persistent-hint
        readonly
        :rules="fieldRules"
        v-on="on"
        :value="dateValue"
        @click:append="modal = true"
        @click:clear="mutatedValue = ''"
        @input="changeValue"
      />
    </template>
    <v-date-picker
      v-if="modalMode === 'date'"
      v-model="tmpDate"
      first-day-of-week="1"
      full-width
      scrollable
      :type="type"
      @click:date="selectDate"
      @click:month="selectMonth"
      @click:year="selectYear"
    >
      <ox-button
        v-if="showToday"
        button-style="tertiary"
        :label="tr('OxDatepicker-today')"
        @click="today"
      />
      <v-spacer v-if="showToday"></v-spacer>
      <ox-button
        button-style="secondary"
        :label="tr('Cancel')"
        @click="modal = false"
      />
      <v-spacer></v-spacer>
      <ox-button
        v-if="format === 'datetime'"
        button-style="primary"
        :label="tr('Next')"
        @click="modalMode = 'time'"
      />
      <ox-button
        v-else
        button-style="primary"
        :label="tr('Ok')"
        @click="updateDate"
      />
    </v-date-picker>
    <v-time-picker
      v-else-if="modalMode === 'time'"
      v-model="tmpTime"
      format="24hr"
      @click:hour="selectHour"
      @click:minute="selectMinute"
    >
      <ox-button
        v-if="showNow"
        button-style="tertiary"
        :label="tr('Now')"
        @click="now"
      />
      <v-spacer v-if="showNow"></v-spacer>
      <ox-button
        v-if="format === 'time'"
        button-style="secondary"
        :label="tr('Cancel')"
        @click="modal = false"
      />
      <ox-button
        v-if="format === 'datetime'"
        button-style="secondary"
        :label="tr('Previous')"
        @click="modalMode = 'date'"
      />
      <v-spacer></v-spacer>
      <ox-button
        button-style="primary"
        :label="tr('Ok')"
        @click="updateDate"
      />
    </v-time-picker>
  </v-dialog>
</template>

<script src="./OxDatepicker.ts" lang="ts"></script>
