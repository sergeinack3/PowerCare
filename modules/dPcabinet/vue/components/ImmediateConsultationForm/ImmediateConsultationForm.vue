<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { defineComponent, PropType } from "@vue/composition-api"
import { OxButton, OxDate, OxDatepicker, OxDivider, OxForm, OxSelect, OxTextarea } from "oxify"
import Mediuser from "@modules/mediusers/vue/Models/Mediuser"
import OxAutocomplete from "@/core/components/OxAutocomplete/OxAutocomplete.vue"
import Patient from "@modules/dPpatients/vue/models/Patient"
import Consultation from "@modules/dPcabinet/vue/models/Consultation"
import { createJsonApiObjects } from "@/core/utils/OxApiManager"
import { OxUrlBuilder } from "@/core/utils/OxUrlTools"

export default defineComponent({
    name: "ImmediateConsultationForm",
    components: {
        OxAutocomplete,
        OxDatepicker,
        OxButton,
        OxDivider,
        OxSelect,
        OxTextarea
    },
    props: {
        users: Array as PropType<Mediuser[]>,
        userId: String,
        patientsLink: String,
        addConsultationLink: String,
        reset: Boolean,
        type: {
            type: String,
            default: "consultation"
        }
    },
    data () {
        return {
            formValidated: false,
            selectedUserId: "",
            selectedDatetime: "",
            patientClass: Patient,
            consultation: {} as Consultation
        }
    },
    computed: {
        preparedLink (): string {
            if (!this.addConsultationLink) {
                return ""
            }

            return new OxUrlBuilder(this.addConsultationLink)
                .addParameter("immediate", "1")
                .addParameter("user_id", this.selectedUserId.toString())
                .addParameter("datetime", this.selectedDatetime).toString()
        }
    },
    created () {
        this.resetData()
    },
    methods: {
        async save (openModal: boolean): Promise<void> {
            const validate = (this.$refs.form as OxForm).validate()

            if (!validate) {
                return
            }

            const newConsult = await createJsonApiObjects(this.consultation, this.preparedLink)
            this.close((openModal && newConsult.id) ? newConsult.id : "")
        },
        close (consultId = "") {
            this.$emit("close", this.type === "consultation" ? (this.userId === this.selectedUserId || !this.userId) : false, consultId)
        },
        resetData () {
            this.selectedDatetime = OxDate.getYMDHms(new Date())
            this.selectedUserId = this.userId ? this.userId.toString() : ""
            this.consultation = new Consultation()
            if (this.type === "consultation") {
                this.consultation.chrono = "32"
            }
            this.consultation.typeConsultation = this.type
        }
    },
    watch: {
        userId () {
            this.selectedUserId = this.userId ? this.userId.toString() : ""
        },
        reset () {
            if (this.reset) {
                this.resetData()
            }
        }
    }
})
</script>
<template>
  <v-form
    v-model="formValidated"
    ref="form"
    @submit.prevent="save(false)"
  >
    <div class="ImmediateConsultationForm">
      <div class="ImmediateConsultationForm-filterName">
        {{ $tr("Rendez-vous") }}
      </div>
      <div class="ImmediateConsultationForm-filterContent">
        <ox-datepicker
          format="datetime"
          label="Date"
          :not-null="true"
          :value="selectedDatetime"
          v-on:change="(value) => {this.selectedDatetime = value}"
        />
      </div>
      <div class="ImmediateConsultationForm-filterName">
        {{ $tr("common-Patient") }}
      </div>
      <div class="ImmediateConsultationForm-filterContent">
        <ox-autocomplete
          v-model="consultation.patient"
          item-text="shortView"
          :not-null="true"
          :ox-object="patientClass"
          :placeholder="$tr('oxCabinet-action-Selecting patient')"
          :url="patientsLink"
        />
      </div>
      <div class="ImmediateConsultationForm-filterName">
        {{ $tr("common-Practitioner") }}
      </div>
      <div class="ImmediateConsultationForm-filterContent">
        <ox-select
          v-model="selectedUserId"
          option-id="id"
          option-view="fullName"
          :label="$tr('selection_du_praticien')"
          :list="users"
          :not-null="true"
        />
      </div>
      <div
        v-if="type === 'suivi_patient'"
        class="ImmediateConsultationForm-filterName"
      >
        {{ $tr("common-Additional information") }}
      </div>
      <div
        v-if="type === 'suivi_patient'"
        class="ImmediateConsultationForm-filterContent"
      >
        <ox-textarea
          v-model="consultation.remarques"
          :label="$tr('common-Notice')"
          :rows="2"
        />
      </div>
      <div class="ImmediateConsultationForm-actions">
        <ox-divider/>
        <v-card-actions>
          <v-spacer></v-spacer>
          <ox-button
            button-style="secondary"
            type="submit"
            :label="$tr('common-action-Save and close')"
            :title="$tr('common-action-Save and close')"
          />
          <ox-button
            button-style="primary"
            :label="$tr('Open')"
            :title="$tr('Open')"
            @click="save(true)"
          />
        </v-card-actions>
      </div>
    </div>
  </v-form>
</template>

<style src="./ImmediateConsultationForm.scss" lang="scss"></style>
