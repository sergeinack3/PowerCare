<!--
  @package Openxtrem\Sample
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { defineComponent } from "@vue/composition-api"
import { OxButton, OxDialog, OxDatepicker } from "oxify"

/* eslint-disable no-undef */
/* eslint-disable no-new */

export default defineComponent({
    name: "SampleLegacyCompat",
    components: {
        OxButton,
        OxDialog,
        OxDatepicker
    },
    props: {
        user: Object
    },
    data () {
        return {
            dialog: false,
            date: ""
        }
    },
    computed: {},
    methods: {
        openModal () {
            // @ts-ignore
            new Url("oxCabinet", "edit_info_perso")
                .modal({ width: "90%", height: "90%" })
        },
        requestUpdate () {
            // @ts-ignore
            new Url("patients", "vw_idx_patients")
                .addParam("board", 1)
            // .requestUpdate("sample-vue-container")
                .requestUpdate("sample-smarty-container")
        },
        tooltip (event) {
            // @ts-ignore
            ObjectTooltip.createEx(event.srcElement, this.user.guid)
        },
        consoleLogDatas () {
            console.log("datepicker => " + this.date)
            console.log("textarea => " + (this.$refs.textareaAideSaisie as HTMLTextAreaElement)?.value)
            console.log("inputSmarty => " + (document.getElementById("inputSmarty") as HTMLInputElement)?.value)
        },
        openIdex () {
            // @ts-ignore
            guid_ids(this.user.guid)
        },
        showIdex (event) {
            // @ts-ignore
            ObjectTooltip.createEx(event.srcElement, this.user.guid, "identifiers")
        },
        openNote () {
            // @ts-ignore
            Note.create(this.user.guid)
        },
        showNote (event) {
            // @ts-ignore
            ObjectTooltip.createEx(event.srcElement, this.user.guid, "objectNotes")
        },
        openHistory () {
            // @ts-ignore
            guid_log(this.user.guid)
        },
        showHistory (event) {
            // @ts-ignore
            ObjectTooltip.createEx(event.srcElement, this.user.guid, "objectViewHistory")
        }
    },
    mounted () {
    // @ts-ignore
        window.prepareForm(this.$refs.formAideSaisie)
        // @ts-ignore
        new AideSaisie.AutoComplete("aideSaisieText", { objectClass: "CSampleMovie", validateOnBlur: 0 })
    }
})
</script>

<template>
  <div class="SampleLegacyCompat">
    <h1>Hello vue</h1>
    <div>
      <ox-dialog
        v-model="dialog"
        title="Ox Modal"
        width="500"
      >
        <template #activator="{on}">
          <ox-button
            label="Access to legacy locales"
            v-on="on"
          />
        </template>
        {{ $tr('mod-sample-tab-legacy_compat') }}
      </ox-dialog>
    </div>

    <div>
      <ox-button label="Open legacy modal" @click="openModal"></ox-button>
    </div>

    <div>
      <ox-button label="Request update element" @click="requestUpdate"></ox-button>
      <div id="sample-vue-container"></div>
    </div>

    <div class="SampleLegacyCompat-user">
      <span class="SampleLegacyCompat-tooltip" @click="openNote" @mouseover="showNote">
        <v-icon color="primary" size="18">mdi-note</v-icon>
      </span>
      <label class="SampleLegacyCompat-label" @mouseover="tooltip">{{ user.name }}</label>
      <span class="SampleLegacyCompat-tooltip" @click="openIdex" @mouseover="showIdex">
        <v-icon color="primary" size="18">mdi-link-variant</v-icon>
      </span>
      <span class="SampleLegacyCompat-tooltip" @click="openHistory" @mouseover="showHistory">
        <v-icon color="primary" size="18">mdi-history</v-icon>
      </span>
    </div>

    <div>
      <!-- @todo styliser -->
      <ox-datepicker :value="date" @change="(value)=>date=value"></ox-datepicker>
      <form ref="formAideSaisie" method="post">
        <!-- need this input to persist ? -->
        <!-- <input type="hidden" name="@class" value="CConsultation" id="editFrmExamsRqs_@class">-->
        <!-- <input type="hidden" name="consultation_id" value="192" class="ref" id="editFrmExamsRqs_consultation_id">-->
        <textarea name="description" id="aideSaisieText" ref="textareaAideSaisie"
                  placeholder="Saisiez vos remarques ..."></textarea>
        <ox-button label="console.log datas" @click="consoleLogDatas"></ox-button>
      </form>
    </div>
  </div>
</template>

<style src="./SampleLegacyCompat.scss" lang="scss"></style>
