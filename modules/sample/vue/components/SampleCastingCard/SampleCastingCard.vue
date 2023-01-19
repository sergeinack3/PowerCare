<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { defineComponent, PropType } from "@vue/composition-api"
import SampleCasting from "@modules/sample/vue/models/SampleCasting"
import { OxIcon, OxTooltip } from "oxify"
import SampleActorLine from "@modules/sample/vue/components/SampleActorLine/SampleActorLine.vue"

export default defineComponent({
    name: "SampleCastingCard",
    components: {
        SampleActorLine,
        OxIcon,
        OxTooltip
    },
    props: {
        casting: {
            type: Object as PropType<SampleCasting>,
            required: true
        }
    },
    computed: {
        cardClasses (): { [key: string]: boolean | undefined } {
            return {
                isMain: this.casting.mainActor
            }
        },
        mainActorLabel (): string {
            return this.casting.mainActor ? this.$tr("CSampleCasting-is_main_actor") : this.$tr("CSampleCasting-set-main-actor")
        }
    },
    methods: {
        changeMainActor () {
            this.$emit("changeMainActor", this.casting, !this.casting.mainActor)
        },
        removeCasting () {
            this.$emit("removeCasting", this.casting)
        }
    }
})
</script>

<template>
  <div
    class="SampleCastingCard"
    :class="cardClasses"
  >
    <div
      class="SampleCastingCard-mainActor"
      @click="changeMainActor"
    >
      <ox-tooltip position="bottom" >
        <template v-slot:content="{ on }">
          <div v-on="on">
            <ox-icon
              icon="star"
              :size="32"
            />
          </div>
        </template>
        <div>{{ mainActorLabel }}</div>
      </ox-tooltip>
    </div>

    <div
      class="SampleCastingCard-removeActor"
      @click="removeCasting"
    >
      <ox-icon
        icon="cancel"
        :size="28"
      />
    </div>

    <sample-actor-line
      :actor="casting.actor"
    />
  </div>
</template>

<style src="./SampleCastingCard.scss" lang="scss"></style>
