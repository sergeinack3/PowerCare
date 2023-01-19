<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import SampleMovie from "../../models/SampleMovie"
import OxCollection from "@/core/models/OxCollection"
import {
    deleteJsonApiObject,
    getCollectionFromJsonApiRequest, getObjectFromJsonApiRequest
} from "@/core/utils/OxApiManager"
import { OxCheckbox, OxChip, OxDatepicker, OxButton, OxContextMenu, OxIcon, OxAlert } from "oxify"
import SampleMovieCard from "../../components/SampleMovieCard/SampleMovieCard.vue"
import OxSort from "@/core/components/OxSort/OxSort.vue"
import { prepareForm } from "@/core/utils/OxSchemaManager"
import OxAutocomplete from "@/core/components/OxAutocomplete/OxAutocomplete.vue"
import SampleCategory from "../../models/SampleCategory"
import SampleMovieAutocomplete from "../../components/SampleMovieAutocomplete/SampleMovieAutocomplete.vue"
import SampleNationality from "../../models/SampleNationality"
import { OxUrlBuilder } from "@/core/utils/OxUrlTools"
import OxInfiniteScroll from "@/core/components/OxInfiniteScroll/OxInfiniteScroll.vue"
import { OxUrlFilter } from "@/core/types/OxUrlTypes"
import { defineComponent, PropType } from "@vue/composition-api"
import { addInfo } from "@/core/utils/OxNotifyManager"
import { OxEntryPointLinks } from "@/core/types/OxEntryPointTypes"
import SampleCasting from "@modules/sample/vue/models/SampleCasting"
import { isEmpty } from "lodash"
const SampleMovieForm = () => import("../../components/SampleMovieForm/SampleMovieForm.vue")

export default defineComponent({
    components: {
        OxInfiniteScroll,
        SampleMovieAutocomplete,
        OxSort,
        OxCheckbox,
        OxChip,
        OxDatepicker,
        SampleMovieCard,
        OxButton,
        OxAutocomplete,
        OxContextMenu,
        SampleMovieForm,
        OxAlert,
        OxIcon
    },
    props: {
        links: Object as PropType<OxEntryPointLinks>
    },
    data () {
        return {
            // Page main datas
            filtersExpand: false,
            loading: false,
            movieClass: SampleMovie,
            movies: {} as OxCollection<SampleMovie>,
            moviesAutocompleteUrl: new OxUrlBuilder(this.links?.movies).addRelation("category").toString(),
            moviesPerSection: 20,
            noData: false,
            noSearchResult: false,

            // Filters
            categoryFilter: new SampleCategory(),
            csaFilter: [] as string[],
            dateMaxFilter: "",
            dateMinFilter: "",
            nationalityFilter: new SampleNationality(),

            // Form datas
            currentMovie: new SampleMovie(),
            currentMovieCasting: [] as SampleCasting[],
            showDeleteModal: false,
            showUpdateForm: false
        }
    },
    computed: {
        usePlural (): boolean {
            return this.movies.total > 1
        },
        pageClasses (): { [key: string]: boolean } {
            return {
                filtersExpand: this.filtersExpand
            }
        },
        showResetButton (): boolean {
            return !!this.categoryFilter?.id ||
                !!this.nationalityFilter?.id ||
                this.csaFilter.length > 0 ||
                !!this.dateMinFilter ||
                !!this.dateMaxFilter
        },
        isCurrentMovieExist (): boolean {
            return this.currentMovie.id !== ""
        },
        showNoData (): boolean {
            return this.noData && !this.noSearchResult
        }
    },
    async created () {
        this.loading = true
        this.movies = await getCollectionFromJsonApiRequest(
            SampleMovie,
            new OxUrlBuilder(this.links?.movies)
                .withRelations(["category"])
                .withLimit(this.moviesPerSection.toString())
                .toString()
        )
        this.noData = this.movies.length < 1
        await prepareForm("sample_movie")
        this.loading = false
    },
    methods: {
        // UI Manipulation
        expandFilters () {
            this.filtersExpand = true
        },
        collapseFilters () {
            this.filtersExpand = false
        },

        // Filters manipulation
        changeDateMin (date: string) {
            this.dateMinFilter = date
            this.updateMovies()
        },
        changeDateMax (date: string) {
            this.dateMaxFilter = date
            this.updateMovies()
        },
        changeCategory (category: SampleCategory) {
            this.categoryFilter = category
            this.updateMovies()
        },
        changeNationality (nationality: SampleNationality) {
            this.nationalityFilter = nationality
            this.updateMovies()
        },
        changeCSA (csa: string[]) {
            this.csaFilter = csa
            this.updateMovies()
        },
        resetFilters () {
            this.dateMinFilter = ""
            this.dateMaxFilter = ""
            this.categoryFilter = new SampleCategory()
            this.nationalityFilter = new SampleNationality()
            this.csaFilter = []
            this.updateMovies()
        },

        // Data fetching / update
        /**
         * Get movies from API based on filters
         */
        async updateMovies () {
            const url = new OxUrlBuilder(this.movies.self).withOffset(null)
            const filters: OxUrlFilter[] = []
            if (this.nationalityFilter && this.nationalityFilter.id) {
                url.addParameter("nationality_id", this.nationalityFilter.id)
            }
            else {
                url.removeParameter("nationality_id")
            }
            if (this.dateMinFilter) {
                filters.push({ key: "release", operator: "greaterOrEqual", value: this.dateMinFilter })
            }
            if (this.dateMaxFilter) {
                filters.push({ key: "release", operator: "lessOrEqual", value: this.dateMaxFilter })
            }
            if (this.categoryFilter && this.categoryFilter.id) {
                filters.push({ key: "category_id", operator: "equal", value: this.categoryFilter.id })
            }
            if (this.csaFilter.length > 0) {
                filters.push({ key: "csa", operator: "in", value: this.csaFilter })
            }
            url.withFilters(...filters)
            this.movies = await getCollectionFromJsonApiRequest(SampleMovie, url.toString())
            this.noData = this.movies.length < 1
        },
        /**
         * Get movies corresponding to research
         * @param {string} search - The research
         */
        async makeSearch (search: string | null) {
            const url = new OxUrlBuilder(this.movies.self).withOffset(null).withFilters(...[])
            if (search !== null) {
                url.addFilter("name", "contains", search)
            }
            this.movies = await getCollectionFromJsonApiRequest(SampleMovie, url.toString())
            this.noSearchResult = this.movies.length < 1 && !!search && search !== ""
        },
        /**
         * Get all fields of a movie
         * @param {SampleMovie} movie - The movie to update
         */
        async updateMovie (movie: SampleMovie) {
            this.currentMovie = await getObjectFromJsonApiRequest(
                SampleMovie,
                new OxUrlBuilder(movie.self)
                    .withFieldsets(["all"])
                    .withRelations(["director", "category"])
                    .toString()
            )
            this.currentMovieCasting = (await getCollectionFromJsonApiRequest(
                SampleCasting,
                new OxUrlBuilder(this.currentMovie.castingUrl).withRelations(["actor"]).toString()
            )).objects

            this.showUpdateForm = true
        },
        /**
         * Delete current selected movie
         */
        async deleteMovie (): Promise<void> {
            if (this.currentMovie.id !== "") {
                await deleteJsonApiObject(this.currentMovie)
                this.movies.deleteItem(this.currentMovie)
                this.currentMovie = new SampleMovie()
                this.currentMovieActors = []
                this.noData = this.movies.length < 1
                addInfo(this.$tr("CSampleMovie-msg-delete"))
            }
        },

        // Movie form management
        /**
         * Display movie adding form
         */
        addMovie () {
            this.currentMovie = new SampleMovie()
            this.currentMovieCasting = []
            this.showUpdateForm = true
        },
        /**
         * Update movie list after update form is closed
         * @param {string} save - Form save mod (update for film updating or create for film creation)
         * @param {updatedMovie} updatedMovie - The updated movie
         */
        resetEditForm (save: string, updatedMovie: SampleMovie) {
            this.currentMovie = new SampleMovie()
            this.currentMovieCasting = []
            this.showUpdateForm = false
            if (save === "update") {
                this.updateSingleMovie(updatedMovie)
            }
            else if (save === "create") {
                this.updateMovies()
            }
        },
        resetDeleteMovie () {
            this.currentMovie = new SampleMovie()
            this.currentMovieCasting = []
        },
        updateSingleMovie (movie: SampleMovie) {
            const index = this.movies.objects.findIndex(_movie => _movie.id === movie.id)
            this.movies.objects.splice(index, 1, movie)
            this.noData = this.movies.length < 1
        },

        // Navigation
        displayMovieDetails (movie: SampleMovie | null) {
            if (movie) {
                window.location.href = movie.detailLink
            }
        },
        askDeleteMovie (movieToDelete: SampleMovie) {
            this.currentMovie = movieToDelete
            this.showDeleteModal = true
        }
    }
})
</script>

<template>
  <div
    class="SampleMovieList"
    :class="pageClasses"
  >
    <!-- Aside left section (filters) -->
    <div
      class="SampleMovieList-filters"
      @mouseleave="collapseFilters"
    >
      <div class="SampleMovieList-filtersContent">
        <div
          v-if="showResetButton"
          class="SampleMovieList-filterReset"
        >
          <ox-button
            button-style="tertiary"
            :label="$tr('Reset')"
            @click="resetFilters"
          />
        </div>
        <div class="SampleMovieList-filterGroup">
          <div class="SampleMovieList-filterName">
            {{ $tr("CSampleMovie-release") }}
          </div>
          <div class="SampleMovieList-filterContent">
            <ox-datepicker
              :label="$tr('CSampleMovie-release-minValue')"
              :value="dateMinFilter"
              @change="changeDateMin"
            />
            <span class="SampleMovieList-filterSeparator"> - </span>
            <ox-datepicker
              :label="$tr('CSampleMovie-release-maxValue')"
              :value="dateMaxFilter"
              @change="changeDateMax"
            />
          </div>
        </div>
        <div class="SampleMovieList-filterGroup">
          <div class="SampleMovieList-filterName">
           {{ $tr("CSampleCategory") }}
          </div>
          <div class="SampleMovieList-filterContent">
            <ox-autocomplete
              v-model="categoryFilter"
              clearable
              item-text="name"
              :min-char-search="0"
              :placeholder="$tr('CSampleCategory-chooseLabel')"
              search-field="name"
              :url="links.categories"
              @input="changeCategory"
            />
          </div>
        </div>
        <div class="SampleMovieList-filterGroup">
          <div class="SampleMovieList-filterName">
            {{ $tr("CSamplePerson-searchDirectorNationality") }}
          </div>
          <div class="SampleMovieList-filterContent">
            <ox-autocomplete
              v-model="nationalityFilter"
              item-text="name"
              :placeholder="$tr('CSampleNationality-chooseLabel')"
              search-field="name"
              :url="links.nationalities"
              @input="changeNationality"
            />
          </div>
        </div>
        <div class="SampleMovieList-filterGroup">
          <div class="SampleMovieList-filterName">
            {{  $tr("CSampleMovie-csa-court") }}
          </div>
          <div class="SampleMovieList-filterContent">
            <div class="SampleMovieList-filtersCheckbox">
              <div class="SampleMovieList-filterCheckbox">
                <ox-checkbox
                  checkbox-value="18"
                  :value="csaFilter"
                  @change="changeCSA"
                >
                  <template #label>
                    <ox-chip
                      :error="true"
                      :small="true"
                    >
                      -18
                    </ox-chip>
                  </template>
                </ox-checkbox>
              </div>
              <div class="SampleMovieList-filterCheckbox">
                <ox-checkbox
                  checkbox-value="16"
                  :value="csaFilter"
                  @change="changeCSA"
                >
                  <template #label>
                    <ox-chip :small="true">
                      -16
                    </ox-chip>
                  </template>
                </ox-checkbox>
              </div>
              <div class="SampleMovieList-filterCheckbox">
                <ox-checkbox
                  checkbox-value="12"
                  :value="csaFilter"
                  @change="changeCSA"
                >
                  <template #label>
                    <ox-chip :small="true">
                      -12
                    </ox-chip>
                  </template>
                </ox-checkbox>
              </div>
              <div class="SampleMovieList-filterCheckbox">
                <ox-checkbox
                  checkbox-value="10"
                  :value="csaFilter"
                  @change="changeCSA"
                >
                  <template #label>
                    <ox-chip :small="true">
                      -10
                    </ox-chip>
                  </template>
                </ox-checkbox>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="SampleMovieList-filtersMore">
        <ox-button
          button-style="tertiary-dark"
          icon="chevronRight"
          @click="expandFilters"
        />
      </div>
    </div>

    <!-- Main content (movie list) -->
    <div class="SampleMovieList-content">
      <div class="SampleMovieList-header">
        <div class="SampleMovieList-title">
          OX Catalogue
        </div>
        <div class="SampleMovieList-search">
          <ox-autocomplete
            item-text="title"
            :ox-object="movieClass"
            placeholder="Rechercher un film"
            rounded
            searchable
            :url="moviesAutocompleteUrl"
            @input="displayMovieDetails"
            @enter="makeSearch"
          >
            <template #item="{ item: movie }">
              <sample-movie-autocomplete :movie="movie"/>
            </template>
          </ox-autocomplete>
        </div>
      </div>
      <div class="SampleMovieList-filmsContainer">
        <div class="SampleMovieList-filmsHeader">
          <div
            v-if="loading"
            class="SampleMovieList-sortSection"
          >
            <v-skeleton-loader
              type="text"
              :width="140"
            />
          </div>
          <div
            v-else
            class="SampleMovieList-sortSection"
          >
            <div class="SampleMovieList-counter">
              {{ movies.total }} {{ $tr("sample_movie-available movies", null, usePlural) }}
            </div>
            <div class="SampleMovieList-sortComponent">
              <ox-sort
                v-model="movies"
                :choices="['name', 'release', 'duration']"
                resource-name="sample_movie"
              />
            </div>
          </div>
          <ox-button
            button-style="primary"
            icon="add"
            :label="$tr('CSampleMovie-title-create')"
            @click="addMovie"
          />
        </div>
        <div
          v-if="loading"
          class="SampleMovieList-films"
        >
          <v-skeleton-loader
            v-for="i in moviesPerSection"
            :key="'initialSkeleton' + i"
            type="card"
          ></v-skeleton-loader>
        </div>
        <ox-infinite-scroll
          v-else
          v-model="movies"
          :bottom-px-trigger="300"
          class="SampleMovieList-films"
        >
          <div
            v-for="movie in movies.objects"
            :key="movie.id"
            class="SampleMovieList-film"
          >
            <ox-context-menu>
              <template #activator="{ on }">
                <sample-movie-card
                  :movie="movie"
                  :on="on"
                />
              </template>
              <template #menu>
                <v-list>
                  <v-list-item-group>
                    <v-list-item @click="updateMovie(movie)">
                      <v-list-item-icon class="SampleMovieList-contextMenu">
                        <ox-icon icon="edit"/>
                      </v-list-item-icon>
                      <v-list-item-content>
                        <v-list-item-title>{{ $tr('Edit') }}</v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item @click="askDeleteMovie(movie)">
                      <v-list-item-icon class="SampleMovieList-contextMenu">
                        <ox-icon icon="delete"/>
                      </v-list-item-icon>
                      <v-list-item-content>
                        <v-list-item-title>{{ $tr('Delete') }}</v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list-item-group>
                </v-list>
              </template>
            </ox-context-menu>
          </div>
          <template #loading>
            <v-skeleton-loader
              v-for="i in moviesPerSection"
              :key="'skeleton' + i"
              height="256"
              type="card"
            ></v-skeleton-loader>
          </template>
        </ox-infinite-scroll>
        <div
          v-if="showNoData"
          class="SampleMovieList-noData"
        >
          <img
            alt="No movie illustration"
            class="SampleMovieList-noDataIllus"
            src="modules/sample/images/empty.svg"
          />
          <div class="SampleMovieList-noDataTitle">
            {{ $tr("CSampleMovie-no-movies") }}
          </div>
          <div class="SampleMovieList-noDataDesc">
            {{ $tr("CSampleMovie-no-movies-desc") }}
          </div>
        </div>
        <div
          v-if="noSearchResult"
          class="SampleMovieList-noResult"
        >
          <div class="SampleMovieList-noResultLabel">
            {{ $tr("CSampleMovie-no-result") }}
          </div>
        </div>
      </div>
    </div>

    <!-- Update Form Movie-->
    <div
      v-if="showUpdateForm"
      class="SampleMovieList-form"
    >
      <sample-movie-form
        :casting="currentMovieCasting"
        :categories-url="links.categories"
        :movie="currentMovie"
        :movie-url="links.movies"
        :nationalities-url="links.nationalities"
        :persons-url="links.persons"
        @close="resetEditForm"
      />
    </div>

    <!-- Alert delete movie -->
    <ox-alert
      v-if="isCurrentMovieExist"
      v-model="showDeleteModal"
      :label-accept="$tr('Delete')"
      :label-cancel="$tr('Cancel')"
      show-cancel
      :title="$tr('sample_movie-confirm-Delete this object-1') + '?'"
      @accept="deleteMovie"
      @cancel="resetDeleteMovie"
    >
      <template #default>
        <div>
          {{ $tr('sample_movie-confirm-Delete this object-1') }}
          <span> "{{ currentMovie.title }}" </span>
          {{ $tr('sample_movie-confirm-Delete this object-2') }}
        </div>
      </template>
    </ox-alert>
  </div>
</template>

<style src="./SampleMovieList.scss" lang="scss"></style>
