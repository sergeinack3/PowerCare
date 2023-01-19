<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { defineComponent, PropType } from "@vue/composition-api"
import { OxBigIcon, OxButton, OxDivider, OxForm } from "oxify"
import SamplePerson from "../../models/SamplePerson"
import SampleNationality from "../../models/SampleNationality"
import { createJsonApiObjects, updateJsonApiObject } from "@/core/utils/OxApiManager"
import { cloneObject } from "@/core/utils/OxObjectTools"
import { prepareForm } from "@/core/utils/OxSchemaManager"
import OxField from "@/core/components/OxField/OxField.vue"
import OxAutocomplete from "@/core/components/OxAutocomplete/OxAutocomplete.vue"
import { addInfo } from "@/core/utils/OxNotifyManager"

export default defineComponent({
    name: "SamplePersonForm",
    components: {
        OxAutocomplete,
        OxField,
        OxButton,
        OxDivider,
        OxBigIcon
    },
    props: {
        createLink: [String, undefined] as PropType<string | undefined>,
        nationalitiesLink: String,
        person: {
            type: Object as PropType<SamplePerson>,
            required: false
        }
    },
    data () {
        return {
            formReady: false,
            formValidated: false,
            resourceName: "sample_person",
            mutatedPerson: {} as SamplePerson,
            sampleNationalityClass: SampleNationality
        }
    },
    computed: {
        /**
         * Generates the right title
         *
         * @return {string} The right action button label
         */
        actionBtnLabel (): string {
            return !this.person
                ? this.$tr("CSamplePerson-title-add")
                : this.$tr("CSamplePerson-title-modify")
        }
    },
    async created () {
        this.mutatedPerson = this.person ? cloneObject(this.person) : new SamplePerson()
        this.formReady = await prepareForm(this.resourceName, ["default", "extra"])
    },
    methods: {
        async save () {
            const validate = (this.$refs.form as OxForm).validate()

            if (!validate) {
                return
            }

            try {
                if (this.person) {
                    await updateJsonApiObject(this.person, this.mutatedPerson)
                    addInfo(this.$tr("CSamplePerson-msg-modify"))
                }
                else if (this.createLink) {
                    await createJsonApiObjects(this.mutatedPerson, this.createLink)
                    addInfo(this.$tr("CSamplePerson-msg-create"))
                }

                this.$emit("dataSaved")
            }
            catch (e) {
                console.error(e)
            }
        },

        /**
         * Delete person event propagation
         *
         * @param {SamplePerson} person - The deleted person
         */
        deletePerson (person: SamplePerson) {
            this.$emit("deletePerson", person)
        }
    }
})
</script>
<template>
  <v-form
    v-model="formValidated"
    ref="form"
    @submit.prevent="save"
  >
    <div class="SamplePersonForm">
      <div class="SamplePersonForm-fieldGroup">
        <ox-field
          v-model="mutatedPerson.lastName"
          field-name="last_name"
          :ready="formReady"
          :resource-name="resourceName"
        />
        <ox-field
          v-model="mutatedPerson.firstName"
          field-name="first_name"
          :ready="formReady"
          :resource-name="resourceName"
        />
      </div>
      <div class="SamplePersonForm-fieldGroup">
        <ox-field
          v-model="mutatedPerson.sex"
          :options="{expand: true, row: true}"
          field-name="sex"
          :ready="formReady"
          :resource-name="resourceName"
        />
        <ox-autocomplete
          v-model="mutatedPerson.nationality"
          item-text="name"
          :label="$tr('CSampleNationality')"
          :ox-object="sampleNationalityClass"
          search-field="name"
          :url="nationalitiesLink"
        />
        <ox-field
          v-model="mutatedPerson.birthdateData"
          field-name="birthdate"
          :ready="formReady"
          :resource-name="resourceName"
        />
      </div>
      <div class="SamplePersonForm-fieldGroup">
        <ox-field
          v-model="mutatedPerson.activityStartData"
          field-name="activity_start"
          :ready="formReady"
          :resource-name="resourceName"
        />
        <div class="SamplePersonForm-director">
          <ox-big-icon icon="film" />
          <div class="SamplePersonForm-directorField">
            <ox-field
              v-model="mutatedPerson.isDirector"
              field-name="is_director"
              :ready="formReady"
              :resource-name="resourceName"
            />
            <span class="SamplePersonForm-directorDesc">
              {{ $tr("common-label-This person will always be an actor") }}
            </span>
          </div>
        </div>
      </div>
      <div class="SamplePersonForm-actions">
        <ox-divider />
        <v-card-actions>
          <v-spacer></v-spacer>
          <ox-button
            v-if="person"
            button-style="tertiary"
            :label="$tr('Delete')"
            :title="$tr('Delete')"
            @click="deletePerson(person)"
          />
          <ox-button
            button-style="primary"
            :label="actionBtnLabel"
            :title="actionBtnLabel"
            type="submit"
          />
        </v-card-actions>
      </div>
    </div>
  </v-form>
</template>

<style src="./SamplePersonForm.scss" lang="scss"></style>
