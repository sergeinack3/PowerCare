<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { defineComponent, PropType } from "@vue/composition-api"
import Mediuser from "@modules/mediusers/vue/Models/Mediuser"

export default defineComponent({
    name: "MediuserAvatar",
    props: {
        user: Object as PropType<Mediuser>
    }
})
</script>

<template>
  <div class="MediuserAvatar">
    <v-avatar
      left
      size="20"
      :color="user.color"
    >
      <span class="MediuserAvatar-praticienInitials">
        {{ user.initials }}
      </span>
    </v-avatar>
  </div>
</template>

<style src="./MediuserAvatar.scss" lang="scss"></style>
