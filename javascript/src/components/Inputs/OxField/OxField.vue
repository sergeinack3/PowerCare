<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="OxField">
    <ox-tooltip :delay="500">
      <template v-slot:content="{ attrs, on }">
        <div
          v-bind="attrs"
          v-on="on"
        >
          <ox-checkbox
            v-if="isBoolean"
            :disabled="disabled"
            :label="fieldLabel"
            :message="message"
            :not-null="isNotNull"
            :on-primary="onPrimary"
            :rules="fieldRules"
            :title="fieldTitle"
            :value="defaultValue"
            @change="change"
          />
          <ox-text-field
            v-else-if="isString || isNum || isCurrency"
            :icon="isCurrency ? 'currency' : undefined"
            :disabled="disabled"
            :label="fieldLabel"
            :message="message"
            :not-null="isNotNull"
            :number="isNum || isCurrency"
            :on-primary="onPrimary"
            :rules="fieldRules"
            :title="fieldTitle"
            :value="defaultValue"
            @change="change"
          />
          <ox-textarea
            v-else-if="isText"
            :autocomplete="hasAutocomplete"
            :disabled="disabled"
            :label="fieldLibelle"
            :message="message"
            :not-null="isNotNull"
            :on-primary="onPrimary"
            :rows="rows"
            :rules="fieldRules"
            :title="fieldTitle"
            :value="defaultValue"
            @change="change"
          />
          <ox-datepicker
            v-else-if="isDate"
            :default-date="defaultDate"
            :default-time="defaultTime"
            :disabled="disabled"
            :format="dateType"
            :label="fieldLabel"
            :message="message"
            :not-null="isNotNull"
            :on-primary="onPrimary"
            :rules="fieldRules"
            :show-now="specs.datepickerShowNow"
            :show-today="specs.datepickerShowToday"
            :title="fieldTitle"
            :value="defaultValue"
            @change="change"
          />
          <ox-select
            v-else-if="isList"
            :disabled="disabled"
            :label="fieldLabel"
            :list="list"
            :message="message"
            :not-null="isNotNull"
            :on-primary="onPrimary"
            :rules="fieldRules"
            :title="fieldTitle"
            :value="defaultValue"
            @change="change"
          />
        </div>
      </template>
      <div>
        {{fieldLongLabel}}
      </div>
    </ox-tooltip>
  </div>
</template>

<script src="./OxField.ts" lang="ts"></script>
