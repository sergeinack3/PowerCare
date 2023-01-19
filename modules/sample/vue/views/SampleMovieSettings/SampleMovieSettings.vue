<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { defineComponent, PropType } from "@vue/composition-api"
import {
    OxAlert,
    OxButton,
    OxChipGroupChoiceModel,
    OxDialog,
    OxDropdownButton,
    OxDropdownButtonActionModel,
    OxIcon,
    OxThemeCore
} from "oxify"
import {
    deleteJsonApiObject,
    getCollectionFromJsonApiRequest,
    updateJsonApiObjectFields
} from "@/core/utils/OxApiManager"
import { OxUrlBuilder } from "@/core/utils/OxUrlTools"
import SamplePerson from "../../models/SamplePerson"
import OxCollection from "@/core/models/OxCollection"
import { OxDatagridColumn } from "@/core/types/OxDatagridTypes"
import OxDatagrid from "@/core/components/OxDatagrid/OxDatagrid.vue"
import SampleFullnameCell from "../../components/SampleFullnameCell/SampleFullnameCell.vue"
import { addInfo } from "@/core/utils/OxNotifyManager"
import { OxUrlFilter } from "@/core/types/OxUrlTypes"
import { OxEntryPointLinks } from "@/core/types/OxEntryPointTypes"
import { prepareForm } from "@/core/utils/OxSchemaManager"

const SamplePersonForm = () => import("../../components/SamplePersonForm/SamplePersonForm.vue")

export default defineComponent({
    name: "SampleMovieSettings",
    components: {
        SamplePersonForm,
        SampleFullnameCell,
        OxIcon,
        OxButton,
        OxDropdownButton,
        OxDialog,
        OxAlert,
        OxDatagrid
    },
    props: {
        links: Object as PropType<OxEntryPointLinks>
    },
    data () {
        return {
            altActions: [
                {
                    label: this.$tr("Delete"),
                    eventName: "actiondelete",
                    icon: "delete"
                },
                {
                    label: this.$tr("common-action-Mark as director"),
                    eventName: "actionmarkasdirector"
                }
            ] as OxDropdownButtonActionModel[],
            altMassActions: [
                {
                    label: this.$tr("common-action-Mark as director"),
                    eventName: "massactionmarkasdirector"
                }
            ] as OxDropdownButtonActionModel[],
            columns: [
                {
                    // "text"  : corresponds to the Datagrid column label
                    // "value" : corresponds to the object getter to display in the column
                    // "filterValues" : corresponds to the "true" attribute values used to make the API request
                    text: this.$tr("CSampleCasting-actor_id"),
                    value: "fullName",
                    filterValues: ["first_name", "last_name"]
                },
                {
                    text: this.$tr("CSampleMovie-director_id"),
                    value: "isDirectorString",
                    filterValues: "is_director"
                },
                {
                    value: "sex"
                },
                {
                    value: "birthdate"
                },
                {
                    text: this.$tr("CSampleNationality"),
                    value: "nationality.name",
                    filterValues: "",
                    sortable: false
                },
                {
                    value: "activityStart"
                }
            ] as OxDatagridColumn[],
            dialog: false,
            filterChoices: [
                {
                    label: this.$tr("CSamplePerson-is_director", null, true),
                    value: "directors"
                },
                {
                    label: this.$tr("CSamplePerson-Activity start > 2000"),
                    value: "activity_start_2000"
                }
            ] as OxChipGroupChoiceModel[],
            pageReady: false,
            person: null as SamplePerson | null,
            personsCollection: {} as OxCollection<SamplePerson>,
            samplePersonClass: SamplePerson,
            selectedPersons: [] as SamplePerson[],
            showDeleteModal: false,
            showMassDeleteModal: false
        }
    },
    computed: {
        /**
         * Generates the right title in the modal containing the SamplePersonForm
         *
         * @return {string} The right title
         */
        formTitle (): string {
            return !this.person
                ? this.$tr("CSamplePerson-title-add")
                : `${this.person.firstName} ${this.person.lastName}`
        }
    },
    watch: {
        dialog (val) {
            val || this.close()
        }
    },
    async created () {
        const url = new OxUrlBuilder(this.links?.personsList)
            .withRelations(["nationality"])
            .withFieldsets(["default", "extra"])
            .withLimit("20")
            .withPermissions()
        this.personsCollection = await getCollectionFromJsonApiRequest(SamplePerson, url.toString())
        await prepareForm("sample_person", ["default", "extra"])
        this.pageReady = true
    },
    methods: {
        /**
         * Returns the right color for the sex icon depending on the sex value
         *
         * @param {string} sex - The sex value
         * @return {string} The sex icon color
         */
        getSexIconColor (sex: string): string {
            return sex === "m" ? OxThemeCore.blueText : OxThemeCore.pinkText
        },

        /**
         * Clones the SamplePerson object clicked in the Datagrid
         *   - in order to hydrate the SamplePersonForm fields with his data
         *
         * Changes the dialog value to open the modal containing the SamplePersonForm
         *
         * @param {SamplePerson} item - The SamplePerson object clicked
         */
        updateItem (item: SamplePerson): void {
            this.person = item
            this.dialog = true
        },

        /**
         * Resets the current person value.
         * Resets the dialog value to close the modal containing the SamplePersonForm
         */
        close () {
            this.person = null
            this.dialog = false
        },

        /**
         * Catches the event emitted by SamplePersonForm after the successful create/update request.
         * Closes the modal containing the SamplePersonForm.
         * Refreshes the Datagrid to show the modification
         */
        async dataSaved () {
            this.close()
            await (this.$refs.datagrid as InstanceType<typeof OxDatagrid>).refresh()
        },

        /**
         * Clones the SamplePerson object clicked in the Datagrid
         *   - in order to hydrate the confirmation alert with his data
         *
         * Changes the showDeleteModal value to open the confirmation alert
         *
         * @param {SamplePerson} item - The SamplePerson object clicked
         */
        askDeleteItem (item: SamplePerson) {
            this.showDeleteModal = true
            this.person = item
        },

        /**
         * Clones the SamplePerson objects selected in the Datagrid
         * Changes the showMassDeleteModal value to open the confirmation alert
         *
         * @param {SamplePerson[]} selectedItems - The SamplePerson objects selected
         */
        askMassDeleteItem (selectedItems: SamplePerson[]) {
            this.showMassDeleteModal = true
            this.selectedPersons = selectedItems
        },

        /**
         * Calls the API to DELETE a SamplePerson.
         * Refreshes the Datagrid to show the modification
         */
        async deleteItem () {
            try {
                if (this.person) {
                    await deleteJsonApiObject(this.person)
                    addInfo(this.$tr("CSamplePerson-msg-delete"))

                    if (this.dialog) {
                        this.close()
                    }

                    this.person = null

                    await (this.$refs.datagrid as InstanceType<typeof OxDatagrid>).refresh()
                }
            }
            catch (e) {
                console.error(e)
            }
        },

        /**
         * Calls the API to successively DELETE all the SamplePerson items selected in the Datagrid.
         * Refreshes the Datagrid to show the modification
         */
        async massDeleteAction () {
            if (this.selectedPersons.length > 0) {
                await Promise.all(
                    this.selectedPersons.map(async (selectedItem) => {
                        await deleteJsonApiObject(selectedItem)
                    })
                )

                addInfo(this.$tr("CSamplePerson-msg-delete", null, true))
                await (this.$refs.datagrid as InstanceType<typeof OxDatagrid>).refresh()
            }
        },

        /**
         * Calls the API to successively UPDATE all the SamplePerson items selected in the Datagrid
         *   - updates the is_director attribute
         *
         * Refreshes the Datagrid to show the modification
         *
         * @param {SamplePerson[]} selectedItems - The selected SamplePerson items
         */
        async massMarkAsDirectorAction (selectedItems: SamplePerson[]) {
            if (selectedItems.length > 0) {
                await Promise.all(
                    selectedItems.map(async (selectedItem) => {
                        await updateJsonApiObjectFields(selectedItem, { is_director: true })
                    })
                )

                addInfo(this.$tr("CSamplePerson-msg-modify", null, true))
                await (this.$refs.datagrid as InstanceType<typeof OxDatagrid>).refresh()
            }
        },

        /**
         * Calls the API to UPDATE a SamplePerson
         *   - updates the is_director attribute
         *
         * Refreshes the Datagrid to show the modification
         *
         * @param {SamplePerson} item - The SamplePerson object
         */
        async markAsDirector (item: SamplePerson) {
            await updateJsonApiObjectFields(item, { is_director: true })

            addInfo(this.$tr("CSamplePerson-msg-modify"))
            await (this.$refs.datagrid as InstanceType<typeof OxDatagrid>).refresh()
        },

        /**
         * Refreshes the Datagrid by adding new "filters" query parameters in the URL called (based on selected filters)
         *   - filters by is_director = 1
         *   - filters by activity_start > 2000
         */
        async filterItems (selected: string[]) {
            const url = new OxUrlBuilder(this.personsCollection.self)
            const filters: OxUrlFilter[] = []

            if (selected) {
                if (selected.includes("directors")) {
                    filters.push({ key: "is_director", operator: "equal", value: "1" })
                }

                if (selected.includes("activity_start_2000")) {
                    filters.push({ key: "activity_start", operator: "greaterOrEqual", value: "2001-01-01" })
                }
            }

            url.withFilters(...filters)

            await (this.$refs.datagrid as InstanceType<typeof OxDatagrid>).refresh(url)
        },

        customSort: function (items, sortBy, isDesc) {
            items.sort((a, b) => {
                // How "sort" method works =>
                //   if return > 0 : index A > index B
                //   if return < 0 : index A < index B
                //   if return = 0 : index A & index B unchanged
                if (sortBy.includes("birthdate") || sortBy.includes("activityStart")) {
                    // We use "Infinity" global property to manage "null" values
                    // If Date < 1970-01-01 : timestamp value < 0
                    //   => So we have to assign "null" value to "-"Infinity to avoid wrong results when
                    //   comparing for example "1888-01-01" with "null" value
                    // @see MDN https://developer.mozilla.org/fr/docs/Web/JavaScript/Reference/Global_Objects/Infinity
                    if (!isDesc[0]) {
                        return (a.birthdateData ? new Date(a.birthdateData) : -Infinity) as number -
                            ((b.birthdateData ? new Date(b.birthdateData) : -Infinity) as number)
                    }
                    else {
                        return (b.birthdateData ? new Date(b.birthdateData) : -Infinity) as number -
                            ((a.birthdateData ? new Date(a.birthdateData) : -Infinity) as number)
                    }
                }
                else if (typeof a[sortBy] !== "undefined") {
                    if (!isDesc[0]) {
                        return a[sortBy].toLowerCase().localeCompare(b[sortBy].toLowerCase())
                    }
                    else {
                        return b[sortBy].toLowerCase().localeCompare(a[sortBy].toLowerCase())
                    }
                }

                return 0
            })

            return items
        }
    }
})
</script>
<template>
  <div class="SampleMovieSettings">
    <header class="SampleMovieSettings-header">
      <h5 class="SampleMovieSettings-title">{{ $tr("CSamplePerson",  null, true) }}</h5>
      <div class="SampleMovieSettings-action">
        <ox-dialog
          v-model="dialog"
          separated
          :title="formTitle"
          width="622"
        >
          <template
            v-if="links.personsCreate"
            #activator="{ on, attrs }"
          >
            <ox-button
              button-style="primary"
              icon="add"
              :label="$tr('CSamplePerson-title-add')"
              :title="$tr('CSamplePerson-title-add')"
              v-bind="attrs"
              v-on="on"
            />
          </template>
          <sample-person-form
            v-if="dialog"
            :create-link="links.personsCreate"
            :nationalities-link="links.nationalities"
            :person="person"
            @dataSaved="dataSaved"
            @deletePerson="askDeleteItem"
          />
        </ox-dialog>
      </div>
      <ox-alert
        v-if="person"
        v-model="showDeleteModal"
        :label-accept="$tr('Delete')"
        :label-cancel="$tr('Cancel')"
        show-cancel
        :title="$tr('CSamplePerson-title-askDelete')"
        @accept="deleteItem"
      >
        <template #default>
          {{ $tr('sample_person-confirm-Delete this object?')}}
          <span class="SampleMovieSettings-alertFullName">{{ person.fullName }} </span> ?
          {{ $tr('sample_person-confirm-Irreversible action')}}
        </template>
      </ox-alert>
      <ox-alert
        v-if="selectedPersons.length > 0"
        v-model="showMassDeleteModal"
        :label-accept="$tr('Delete')"
        :label-cancel="$tr('Cancel')"
        show-cancel
        :title="$tr('CSamplePerson-title-askDelete', null, true)"
        @accept="massDeleteAction"
      >
        <template #default>
          {{ $tr('sample_person-confirm-Delete this object?', null, true)}}
          {{ $tr('sample_person-confirm-Irreversible action')}}
        </template>
      </ox-alert>
    </header>
    <div
      v-if="pageReady"
      class="SampleMovieSettings-datagrid"
    >
      <ox-datagrid
        v-model="personsCollection"
        :columns="columns"
        :filters="filterChoices"
        fixed-headers
        multi-sort
        :no-data-text="$tr('CSamplePerson.none')"
        :ox-object="samplePersonClass"
        ref="datagrid"
        searchable
        :search-label="$tr('CSamplePerson-msg-search')"
        show-actions
        show-select
        stripped
        @deleteItem="askDeleteItem"
        @filterItems="filterItems"
        @updateItem="updateItem"
      >
        <template #item.fullName="{ item, value }">
          <sample-fullname-cell
            :src="item.profilePicture"
            :text="value"
          />
        </template>
        <template #item.sex="{ item, value }">
          <ox-icon
            v-if="item.sexIcon"
            :color="getSexIconColor(value)"
            :icon="item.sexIcon"
          />
        </template>
        <template #item.actions="{ item, buttonEditAttrs }">
          <ox-button
            v-bind="buttonEditAttrs"
            @click="updateItem(item)"
          />
          <ox-dropdown-button
            :alt-actions="altActions"
            button-style="tertiary-dark"
            @actiondelete="askDeleteItem(item)"
            @actionmarkasdirector="markAsDirector(item)"
          />
        </template>
        <template #mass-actions="{ selectedItems }">
          <div class="SampleMovieSettings-massAction">
            <ox-button
              button-style="tertiary-dark"
              icon="delete"
              :title="$tr('Delete')"
              @click="askMassDeleteItem(selectedItems)"
            />
          </div>
          <div class="SampleMovieSettings-massAction">
            <ox-dropdown-button
              :alt-actions="altMassActions"
              button-style="tertiary-dark"
              @massactionmarkasdirector="massMarkAsDirectorAction(selectedItems)"
            />
          </div>
        </template>
      </ox-datagrid>
    </div>
    <div
      v-else
      class="SampleMovieSettings-datagrid"
    >
      <v-skeleton-loader
        height="48"
        type="text"
      />
      <v-skeleton-loader
        type="table-row-divider@10, table-tfoot"
      />
    </div>
  </div>
</template>

<style src="./SampleMovieSettings.scss" lang="scss"></style>
