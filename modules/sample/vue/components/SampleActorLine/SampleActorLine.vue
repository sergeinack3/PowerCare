<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->
<script lang="ts">
import SamplePerson from "@modules/sample/vue/models/SamplePerson"
import { defineComponent, PropType } from "@vue/composition-api"

export default defineComponent({
    name: "SampleActorLine",
    props: {
        actor: Object as PropType<SamplePerson>
    }
})
</script>

<template>
  <div class="SampleActorLine">
    <img
      v-if="actor.profilePicture"
      :alt="actor.fullName"
      class="SampleActorLine-image"
      :src="actor.profilePicture"
    />
    <div
      v-else
      class="SampleActorLine-image"
    />
    <div class="SampleActorLine-content">
      {{ actor.fullName }}
    </div>
  </div>
</template>

<style src="./SampleActorLine.scss" lang="scss"></style>
