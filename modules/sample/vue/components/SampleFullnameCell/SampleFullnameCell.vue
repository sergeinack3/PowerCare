<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->
<script lang="ts">
import { defineComponent } from "@vue/composition-api"

export default defineComponent({
    name: "SampleFullnameCell",
    props: {
        text: String,
        src: String
    }
})
</script>

<template>
  <div class="SampleFullnameCell">
    <img
      :alt="text"
      class="SampleFullnameCell-img"
      :src="src"
    />
    <span class="SampleFullnameCell-text">
      {{ text }}
    </span>
  </div>
</template>

<style src="./SampleFullnameCell.scss" lang="scss"></style>
