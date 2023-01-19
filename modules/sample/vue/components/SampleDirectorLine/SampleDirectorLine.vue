<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import SamplePerson from "../../models/SamplePerson"
import { defineComponent, PropType } from "@vue/composition-api"

export default defineComponent({
    name: "SampleDirectorLine",
    props: {
        director: Object as PropType<SamplePerson>
    }
})
</script>

<template>
  <div class="SampleDirectorLine">
    <!-- Avatar director -->
    <img
      v-if="director && director.profilePicture"
      :alt="director.fullName"
      class="SampleDirectorLine-image"
      :src="director.profilePicture"
    />
    <div
      v-else-if="director && !director.profilePicture"
      class="SampleDirectorLine-image"
    />
    <v-skeleton-loader
      v-else
      class="SampleDirectorLine-image"
      :height="32"
      type="image"
      :width="32"
    />

    <!-- Name director -->
    <div
      v-if="director"
      class="SampleDirectorLine-content"
    >
      {{ director.fullName }}
    </div>
    <v-skeleton-loader
      v-else
      class="SampleDirectorLine-content"
      type="text"
      :width="100"
    />
  </div>
</template>

<style src="./SampleDirectorLine.scss" lang="scss"></style>
