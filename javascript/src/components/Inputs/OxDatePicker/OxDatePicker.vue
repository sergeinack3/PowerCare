<!--
  @package Openxtrem\Core
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="OxDatePicker">
    <form
      class="OxDatePicker-form"
      ref="form">
      <input
        name="input"
        type="hidden"
        :value="currentDate"/>
    </form>
  </div>
</template>

<script src="./OxDatePicker.ts" lang="ts"></script>
<style src="./OxDatePicker.scss" lang="scss">

</style>
