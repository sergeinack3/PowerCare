<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="Value">
    <INValueString
      v-if="isEmpty"
      :field="tr('Void')"
      :class="className"/>
    <div v-else>
      <INValueBool
        v-if="fieldType === 'boolean'"
        :field="field"/>
      <INValueString
        v-if="fieldType === 'string'"
        :field="field"
        :length="length"/>
      <INValueDatetime
        v-if="fieldType === 'datetime'"
        :field="field"/>
      <div v-if="fieldType !== 'boolean' && fieldType!== 'string' && fieldType !== 'datetime' && fieldType !== 'object'">
        {{ fieldView }}
      </div>
      <div v-if="fieldType === 'object'">
        <INValue
          v-for="(subField, fieldIndex) in field"
          :key="fieldIndex"
          :field="subField"/>
      </div>
    </div>
  </div>
</template>

<script src="./INValue.ts" lang="ts"></script>

<style src="./INValue.scss" lang="scss" scoped>
</style>
