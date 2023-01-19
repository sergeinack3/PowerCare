<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->
<script lang="ts">
import { defineComponent } from "@vue/composition-api"

export default defineComponent({
    name: "DocPastille",
    components: { },
    props: {
        active: {
            type: Boolean,
            default: true
        },
        type: {
            type: String,
            required: true
        },
        count: {
            type: Number,
            default: 0
        },
        showCount: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        label (): string {
            switch (this.type) {
            case "document":
                return this.$tr("common-Doc")
            case "prescription":
                return this.$tr("common-Presc")
            case "form":
                return this.$tr("common-Form")
            case "cerfa":
                return this.$tr("Cerfa")
            default:
                break
            }

            return ""
        },
        avatarColor (): string {
            switch (this.type) {
            case "document":
                return "rgba(94, 53, 177, 0.18)"
            case "prescription":
                return "rgba(38, 125, 212, 0.18)"
            case "form":
                return "rgba(88, 185, 195, 0.18)"
            case "cerfa":
                return "rgba(194, 24, 91, 0.18)"
            default:
                break
            }

            return ""
        },
        borderColor (): string {
            let color = ""
            switch (this.type) {
            case "document":
                color = "rgba(94, 53, 177, 0.5)"
                break
            case "prescription":
                color = "rgba(25, 118, 210, 0.5)"
                break
            case "form":
                color = "rgba(0, 151, 167, 0.5)"
                break
            case "cerfa":
                color = "rgba(194, 24, 91, 0.5)"
                break
            default:
                break
            }

            return "border-color : " + color
        },
        textColor (): string {
            let color = ""
            switch (this.type) {
            case "document":
                color = "#5E35B1"
                break
            case "prescription":
                color = "#1976D2"
                break
            case "form":
                color = "#0097A7"
                break
            case "cerfa":
                color = "#C2185B"
                break
            default:
                break
            }

            return "color : " + color
        }
    }
})
</script>

<template>
  <div
    v-if="active"
    class="DocPastille"
    :style="borderColor"
  >
    <div
      class="DocPastille-label"
      :style="textColor"
    >
      {{ label }}
    </div>
    <v-avatar
      v-if="showCount"
      :color="avatarColor"
      size="20"
    >
      <span
        class="DocPastille-number"
        :style="textColor"
      >{{ count }}</span>
    </v-avatar>
  </div>
</template>

<style src="./DocPastille.scss" lang="scss"></style>
