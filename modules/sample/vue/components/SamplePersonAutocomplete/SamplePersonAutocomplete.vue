<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import SamplePerson from "../../models/SamplePerson"
import { defineComponent, PropType } from "@vue/composition-api"

export default defineComponent({
    name: "SamplePersonAutocomplete",
    props: {
        person: {
            required: true,
            type: Object as PropType<SamplePerson>
        }
    }
})
</script>

<template>
  <div class="SamplePersonAutocomplete">
    <v-avatar
      class="SamplePersonAutocomplete-avatarChips"
      size="30"
    >
      <img
        :alt="person.fullName"
        class="SamplePersonAutocomplete-image"
        :src="person.profilePicture"
      />
    </v-avatar>
    <div class="SamplePersonAutocomplete-text">
      {{ person.fullName }}
    </div>
  </div>
</template>

<style src="./SamplePersonAutocomplete.scss" lang="scss"></style>
