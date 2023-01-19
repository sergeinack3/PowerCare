<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">

import SampleMovie from "../../models/SampleMovie"
import { OxBeautify } from "oxify"
import { defineComponent, PropType } from "@vue/composition-api"

export default defineComponent({
    name: "SampleMovieAutocomplete",
    components: {
        OxBeautify
    },
    props: {
        movie: Object as PropType<SampleMovie>
    }
})
</script>

<template>
  <a
    class="SampleMovieAutocomplete"
    :href="movie.detailLink"
    @click.stop=""
  >
    <div class="SampleMovieAutocomplete-cover">
      <img
        :alt="movie.title"
        class="SampleMovieAutocomplete-coverImage"
        :src="movie.image"
      />
    </div>
    <div class="SampleMovieAutocomplete-content">
      <div class="SampleMovieAutocomplete-title">
        {{ movie.title }}
      </div>
      <div class="SampleMovieAutocomplete-infos">
        {{ movie.category.name }} - <ox-beautify :inline="true" :value="movie.duration"/> - {{ movie.releaseYear }}
      </div>
    </div>
  </a>
</template>

<style src="./SampleMovieAutocomplete.scss" lang="scss"></style>
