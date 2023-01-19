<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import SampleMovie from "../../models/SampleMovie"
import { OxButton, OxCaution, OxChip, OxForm, OxSections } from "oxify"
import OxField from "@/core/components/OxField/OxField.vue"
import { prepareForm } from "@/core/utils/OxSchemaManager"
import { cloneObject } from "@/core/utils/OxObjectTools"
import { cloneDeep, isEqual } from "lodash"
import { createJsonApiObjects, updateJsonApiObject } from "@/core/utils/OxApiManager"
import OxAutocomplete from "@/core/components/OxAutocomplete/OxAutocomplete.vue"
import SamplePerson from "../../models/SamplePerson"
import SampleCategory from "../../models/SampleCategory"
import SamplePersonAutocomplete from "../SamplePersonAutocomplete/SamplePersonAutocomplete.vue"
import SampleCastingCard from "../SampleCastingCard/SampleCastingCard.vue"
import { defineComponent, PropType } from "@vue/composition-api"
import { OxUrlBuilder } from "@/core/utils/OxUrlTools"
import { addInfo } from "@/core/utils/OxNotifyManager"
import SampleCasting from "@modules/sample/vue/models/SampleCasting"

export default defineComponent({
    name: "SampleMovieForm",
    components: {
        SamplePersonAutocomplete,
        SampleCastingCard,
        OxAutocomplete,
        OxField,
        OxSections,
        OxButton,
        OxChip,
        OxCaution
    },
    props: {
        movie: {
            required: true,
            type: Object as PropType<SampleMovie>
        },
        casting: {
            required: false,
            type: Array as PropType<SampleCasting[]>
        },
        personsUrl: {
            type: String,
            required: true
        },
        categoriesUrl: String,
        nationalitiesUrl: String,
        movieUrl: String
    },
    data: () => {
        return {
            formReady: false,
            formValidated: false,
            oldMovie: null,
            movieMutated: {} as SampleMovie,
            newCastings: [] as SampleCasting[],
            optionsCsa: { row: true },
            optionsLanguages: { multiple: true },
            personFilter: new SamplePerson(),
            resourceName: "sample_movie",
            samplePersonClass: SamplePerson,
            sampleCategoryClass: SampleCategory
        }
    },
    computed: {
        directorUrl (): string {
            return new OxUrlBuilder(this.personsUrl).addFilter("is_director", "equal", "1").toString()
        },
        title (): string {
            return this.movie.id !== "" ? this.$tr("CSampleMovie-title-modify") : this.$tr("CSampleMovie-title-create")
        },
        // @TODO: adding trad, adding return type => export OxSection type in Oxify
        sections () {
            return [
                {
                    title: "Informations principales",
                    name: "informations"
                },
                {
                    title: "Casting",
                    name: "casting"
                },
                {
                    title: "Autres informations",
                    name: "others"
                }
            ]
        },
        showEmptyCasting (): boolean {
            return this.newCastings.length === 0
        }
    },
    methods: {
        async save () {
            const validate = (this.$refs.form as OxForm).validate()

            if (!validate) {
                return
            }

            if (this.movie.id !== "") {
                await updateJsonApiObject(this.movie, this.movieMutated)
                if (!isEqual(this.newCastings, this.casting)) {
                    const url = new OxUrlBuilder(this.movie?.castingUrl).addRelation("actor")
                    await createJsonApiObjects(this.newCastings, url.toString())
                }
                this.$emit("close", "update", this.movieMutated)
                addInfo(this.$tr("CSampleMovie-msg-modify"))
            }
            else if (this.movieUrl) {
                const newMovie = await createJsonApiObjects(this.movieMutated, this.movieUrl)
                if (!isEqual(this.newCastings, this.casting)) {
                    const url = new OxUrlBuilder(newMovie?.castingUrl)
                    await createJsonApiObjects(this.newCastings, url.toString())
                }
                this.$emit("close", "create", this.movieMutated)
                addInfo(this.$tr("CSampleMovie-msg-create"))
            }
        },
        removeCasting (casting: SampleCasting) {
            this.newCastings = this.newCastings.filter((_casting) => {
                return _casting.actor.id !== casting.actor.id
            })
        },
        closeForm () {
            this.$emit("close")
        },
        updateDuration (duration: string) {
            if (duration.length === 5) {
                // Set to default time format
                this.movieMutated.duration = duration + ":00"
            }
        },
        addCasting (person: SamplePerson) {
            this.personFilter = {} as SamplePerson

            const castingAlreadyExists = this.newCastings.findIndex((_casting) => {
                return _casting.actor.id === person.id
            })

            if (castingAlreadyExists > -1) {
                addInfo("Actor already in the casting")
                return
            }

            const casting = new SampleCasting()
            casting.actor = person
            this.newCastings.push(casting)
        },
        changeMainActor (casting: SampleCasting, isMainActor: boolean) {
            this.newCastings = this.newCastings.map((_casting) => {
                _casting.mainActor = _casting.actor.id === casting.actor.id && isMainActor
                return _casting
            })
        }
    },
    async created () {
        this.movieMutated = cloneObject(this.movie)

        if (this.casting) {
            this.newCastings = cloneDeep(this.casting)
        }

        this.formReady = await prepareForm(this.resourceName, ["default", "details"])
    }
})
</script>

<template>
  <v-form
    v-model="formValidated"
    class="SampleMovieForm"
    ref="form"
    @submit.prevent="save"
  >
    <div class="SampleMovieForm-header">
      <h5 class="SampleMovieForm-title">{{ title }}</h5>
      <div class="SampleMovieForm-headerActions">
        <ox-button
          button-style="tertiary-dark"
          :label="$tr('Cancel')"
          @click="closeForm"
        />
        <ox-button
          button-style="primary"
          :label="$tr('Save')"
          type="submit"
        />
      </div>
    </div>
    <ox-sections :sections="sections">
      <template #informations>
        <div class="SampleMovieForm-content">
          <ox-field
            v-model="movieMutated.title"
            field-name="name"
            :ready="formReady"
            :resource-name="resourceName"
          />

          <ox-field
            v-model="movieMutated.description"
            field-name="description"
            :ready="formReady"
            :resource-name="resourceName"
          />

          <div class="SampleMovieForm-date">
            <ox-field
              v-model="movieMutated.releaseData"
              field-name="release"
              :ready="formReady"
              :resource-name="resourceName"
            />
            <ox-field
              :custom-schema="{ type: 'duration', placeholder: '01:45' }"
              field-name="duration"
              :ready="formReady"
              :resource-name="resourceName"
              :rules="[
                v => /[0-9]{2}:[0-5][0-9]/.test(v) || $tr('CSampleMovie-duration-invalidField')
              ]"
              :value="movieMutated.duration"
              @input="updateDuration"
            />
          </div>

          <ox-autocomplete
            v-model="movieMutated.category"
            item-text="name"
            :label="$tr('CSampleMovie-category_id')"
            :min-char-search="1"
            not-null
            :ox-object="sampleCategoryClass"
            :placeholder="$tr('CSampleCategory-chooseLabel')"
            search-field="name"
            :url="categoriesUrl"
          />
        </div>
      </template>

      <template #casting>
        <div class="SampleMovieForm-content">
          <ox-autocomplete
            v-model="movieMutated.director"
            item-text="fullName"
            :label="$tr('CSamplePerson-is_director')"
            not-null
            :placeholder="$tr('CSamplePerson-choose-director')"
            :ox-object="samplePersonClass"
            :url="directorUrl"
          >
            <template #item="{ item: person }">
              <sample-person-autocomplete :person="person" />
            </template>
          </ox-autocomplete>

          <div class="SampleMovieForm-casting">
            <div class="SampleMovieForm-castingTitle">{{$tr("CSampleCasting-actors")}}</div>
            <div class="SampleMovieForm-castingContent">
              <ox-caution
                v-if="showEmptyCasting"
                show-icon="true"
                type="info"
              >
                <template #subcontent>
                  {{ $tr('CSampleMovie-no-casting-desc') }}
                </template>
                {{ $tr('CSampleMovie-no-casting') }}
              </ox-caution>
              <sample-casting-card
                v-for="(casting) of newCastings"
                :key="'casting-' + casting.actor.id"
                :casting="casting"
                @changeMainActor="changeMainActor"
                @removeCasting="removeCasting"
              />
            </div>
          </div>
          <ox-autocomplete
            item-text="fullName"
            :placeholder="$tr('CSamplePerson-choose-actors')"
            :url="personsUrl"
            :value="personFilter"
            @input="addCasting"
          >
            <template #item="{ item: person }">
              <sample-person-autocomplete :person="person" />
            </template>
          </ox-autocomplete>
        </div>
      </template>

      <template #others>
        <div class="SampleMovieForm-content">
          <ox-field
            v-model="movieMutated.csa"
            field-name="csa"
            :options="optionsCsa"
            :ready="formReady"
            :resource-name="resourceName"
          >
            <template #label="slotProps">
              <ox-chip
                :error="slotProps.label === '-18'"
                :small="true"
              >
                {{slotProps.label}}
              </ox-chip>
            </template>
          </ox-field>

          <ox-field
            v-model="movieMutated.languagesData"
            field-name="languages"
            :options="optionsLanguages"
            :ready="formReady"
            :resource-name="resourceName"
          />
        </div>
      </template>
    </ox-sections>
  </v-form>
</template>

<style lang="scss" src="./SampleMovieForm.scss" />
