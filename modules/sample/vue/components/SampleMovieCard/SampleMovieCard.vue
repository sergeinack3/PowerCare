<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import SampleMovie from "../../models/SampleMovie"
import { OxChip, OxBeautify, OxIcon, OxThemeCore, OxTooltip } from "oxify"
import { defineComponent, PropType } from "@vue/composition-api"

export default defineComponent({
    name: "SampleMovieCard",
    components: {
        OxChip,
        OxBeautify,
        OxIcon,
        OxTooltip
    },
    props: {
        movie: {
            type: Object as PropType<SampleMovie>,
            required: true
        },
        on: Object
    },
    data () {
        return {
            starColor: OxThemeCore.yellowText
        }
    },
    computed: {
        showCSA (): boolean {
            return this.movie.csa === "18"
        }
    }
})
</script>

<template>
  <a
    class="SampleMovieCard"
    :href="movie.detailLink"
    v-on="on"
  >
    <div class="SampleMovieCard-cover">
      <img
        :alt="movie.title"
        class="SampleMovieCard-coverImage"
        :src="movie.image"
      />
    </div>
    <div class="SampleMovieCard-content">
      <div class="SampleMovieCard-titleContainer">
        <ox-tooltip class="SampleMovieCard-title">
          <template v-slot:content="{ on }">
            <span v-on="on">
              {{ movie.title }}
            </span>
          </template>
          <div>
            {{ movie.title }}
          </div>
        </ox-tooltip>
        <ox-chip
          v-if="showCSA"
          :error="true"
          :small="true"
        >
          -18
        </ox-chip>
      </div>
      <div class="SampleMovieCard-informations">
        {{ movie.category.name }} - <ox-beautify :inline="true" :value="movie.duration"/>
      </div>
      <div class="SampleMovieCard-footer">
        <div class="SampleMovieCard-rating">
          <ox-icon
            :color="starColor"
            icon="star"
            size="20"
          />
          4,7
        </div>
        <div class="SampleMovieCard-date">{{ movie.releaseYear }}</div>
      </div>
    </div>
  </a>
</template>

<style src="./SampleMovieCard.scss" lang="scss"></style>
