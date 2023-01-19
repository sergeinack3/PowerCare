<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { defineComponent, PropType } from "@vue/composition-api"
import OxCollection from "@/core/models/OxCollection"
import OxObject from "@/core/models/OxObject"
import { OxUrlBuilder, getUrlParams } from "@/core/utils/OxUrlTools"
import { getCollectionFromJsonApiRequest } from "@/core/utils/OxApiManager"
import { DataOptions, DataTableHeader } from "vuetify"
import { OxDatagridColumn } from "@/core/types/OxDatagridTypes"
import { OxUrlSorter } from "@/core/types/OxUrlTypes"
import { OxButton, OxChipGroup, OxChipGroupChoiceModel, OxIcon, OxTextField, OxThemeCore } from "oxify"
import { getSchema } from "@/core/utils/OxStorage"
import { isEmpty } from "lodash"

export default defineComponent({
    name: "OxDatagrid",
    components: {
        OxButton,
        OxChipGroup,
        OxIcon,
        OxTextField
    },
    props: {
        columns: {
            type: [] as PropType<OxDatagridColumn[]>,
            required: true
        },
        customSort: Function,
        groupBy: String,
        filters: [] as PropType<OxChipGroupChoiceModel[]>,
        fixedHeaders: {
            type: Boolean,
            default: false
        },
        hideHeader: {
            type: Boolean,
            default: false
        },
        hideFooter: {
            type: Boolean,
            default: false
        },
        multiSort: {
            type: Boolean,
            default: false
        },
        noDataText: String,
        oxObject: Function as PropType<new() => OxObject>,
        searchable: {
            type: Boolean,
            default: false
        },
        searchLabel: String,
        showActions: {
            type: Boolean,
            default: false
        },
        showSelect: {
            type: Boolean,
            default: false
        },
        stripped: {
            type: Boolean,
            default: false
        },
        value: {
            type: Object as PropType<OxCollection<OxObject>>,
            required: true
        }
    },
    data () {
        return {
            defaultLimitValue: 20,
            displaySearchQuery: false,
            itemsData: [] as OxObject[],
            footerProps: {
                "items-per-page-text": this.$tr("common-label-Number items per page|pl")
            },
            loading: true,
            objectClass: Function as unknown as new() => OxObject,
            options: {} as DataOptions,
            resourceType: "",
            search: "",
            searchQuery: "",
            selectedFilters: [] as string[],
            selectedItems: [] as OxObject[],
            showMassActions: false,
            totalItems: 0
        }
    },
    computed: {
        /**
         * Returns the CSS classes for datagrid table
         *
         * @return {string} The CSS classes
         */
        datagridClasses (): string {
            return this.showMassActions ? "massActionsEnabled OxDatagrid-table" : "OxDatagrid-table"
        },

        /**
         * Returns the input search label text
         *
         * @return {string} The input search label
         */
        searchLabelText (): string {
            return this.searchLabel ? this.searchLabel : this.$tr("common-search")
        },

        /**
         * Transforms the given prop "columns" in an array interpreted by the v-data-table component from Vuetify
         *   - adds default CSS classes
         *   - removes the "filterValues" property
         *   - defines a default value from the schema store (for columns without "text" property)
         *   - automatically adds the "actions" column if the "showActions" prop = true
         *
         * @return {DataTableHeader[]} An array containing the Datagrid's columns description
         */
        headers (): DataTableHeader[] {
            const headers = this.columns.map(
                column => {
                    const rColumn = { ...column }
                    rColumn.class = "OxDatagrid-tableHeader"
                    rColumn.cellClass = "OxDatagrid-tableCell"
                    rColumn.groupable = false
                    delete rColumn.filterValues

                    if (!rColumn.text && !isEmpty(this.resourceType)) {
                        // Attempting to retrieve the schema from the store for the given resource & field name
                        //   For example :
                        //   - resourceType = "sample_person"
                        //   - rColumn.value = "birthdate"
                        //
                        // We use the replace function to be able to match the store value (the true field name) with
                        // the camel case value set in the "column" prop description
                        //   For example :
                        //   - rColumn.value = "activityStart" (=> SampleMovieSettings.vue)
                        const attrSchema = getSchema(
                            this.resourceType,
                            rColumn.value.replace(
                                /[A-Z]/g,
                                letter => `_${letter.toLowerCase()}`
                            )
                        )

                        if (attrSchema) {
                            rColumn.text = attrSchema.libelle
                        }
                    }

                    return rColumn
                }
            ) as DataTableHeader[]

            if (this.showActions) {
                headers.push(
                    {
                        cellClass: "OxDatagrid-tableCell OxDatagrid-tableActions",
                        text: "",
                        value: "actions",
                        sortable: false,
                        groupable: false
                    }
                )
            }

            return headers
        },

        /**
         * Returns the total length of Datagrid items.
         * If the OxObject collection has no prev/next links, we return "-1" to disable the server-side management and
         * use the native v-data-table client-side management (specially for search request)
         *
         * @return {number} The total length of Datagrid items
         */
        serverItemsLength (): number {
            return this.hasPrevNextLinks ? this.totalItems : -1
        },

        /**
         * Checks if the OxObject collection has prev/next links
         *
         * @return {boolean}
         */
        hasPrevNextLinks (): boolean {
            return !!this.value.next || !!this.value.prev
        },

        /**
         * Checks if the "groupBy" prop has a defined and not empty value
         *
         * @return {boolean}
         */
        showGroupBy (): boolean {
            return !!this.groupBy
        },

        /**
         * Gets from the "headers" the matching value with the given "groupBy" prop,
         * and returns the associated text
         *
         * @return {string} The title value for the "groupBy" section
         */
        groupByTitle (): string {
            const header = this.headers.find(
                headerParam => headerParam.value === this.groupBy
            )

            return header?.text ?? ""
        },

        /**
         * Returns the checkboxes color
         *
         * @return {string} The "primary" color
         */
        primaryColor (): string {
            return OxThemeCore.primary
        },

        /**
         * Returns the right translation for "selection", by adding plural if number of selected items > 1
         *
         * @return {string} The "selection" translation text
         */
        selectionText (): string {
            return this.selectedItems.length > 1
                ? this.$tr("common-selection", null, true)
                : this.$tr("common-selection")
        },

        /**
         * Returns the right icon name displayed in the search field
         *
         * @return {string} The icon name displayed in the search field
         */
        searchFieldIcon (): string {
            return this.search ? "cancel" : "search"
        },

        /**
         * Returns the right text displayed when the Datagrid is empty
         *
         * @return {string} The text displayed when the Datagrid is empty
         */
        customableNoDataText (): string {
            return this.noDataText ?? this.$tr("common-No data")
        }
    },
    watch: {
        options: {
            async handler (newVal: DataOptions[], oldVal: DataOptions[]) {
                // Avoiding a second unnecessary API call when we loading the page for the first time
                //   => cause the v-data-table component rebuilds his options after initialization
                if (!isEmpty(oldVal)) {
                    this.loading = true
                    const url = new OxUrlBuilder(this.value.self)

                    // Options are rebuilt after every Datagrid action (pagination click, sort click...)
                    //   => we have to recalculate the URL params to keep the Datagrid up to date
                    //   in server-side behaviour
                    const { sortBy, sortDesc, page, itemsPerPage, groupBy, groupDesc } = this.options
                    const offset = (page - 1) * itemsPerPage
                    const sorters = [] as OxUrlSorter[]

                    url.withLimit(itemsPerPage.toString()).withOffset(offset.toString())

                    if (groupBy.length > 0) {
                        this.generateSorters(groupBy, groupDesc, sorters)
                    }

                    if (sortBy.length > 0) {
                        this.generateSorters(sortBy, sortDesc, sorters)
                    }

                    sorters ? url.withSort(...sorters) : url.withSort()

                    await this.refresh(url)
                }
            },
            deep: true
        }
    },
    created () {
        this.itemsData = this.value.objects
        this.totalItems = this.value.total
        this.loading = false

        // We initialize the "items per page" selectbox in the v-data-table footer
        // The current limit is extracted from the API request, and used to calculate the values to display
        if (this.value.self) {
            const { limit } = getUrlParams(this.value.self)

            const limitValue = limit || this.defaultLimitValue

            this.footerProps["items-per-page-options"] = [
                Math.ceil(limitValue / 2),
                limitValue,
                Math.ceil(limitValue + limitValue / 2)
            ]
        }

        // If we have a non-empty OxCollection defined in the OxDatagrid v-model
        if (this.value &&
            this.itemsData.length > 0 &&
            typeof this.value === "object"
        ) {
            // We can deduce the object class and type from the first item
            this.objectClass = this.itemsData[0].constructor as new() => OxObject
            this.resourceType = this.itemsData[0].type
        }
        else if (this.oxObject) {
            // Else the "oxObject" prop has to be defined (to be able to get the object class and type)
            this.objectClass = this.oxObject
            /* eslint-disable-next-line new-cap */
            this.resourceType = new this.objectClass().type
        }
        else {
            throw new Error("Impossible type inference")
        }
    },
    methods: {
        /**
         * Converts all sort criteria in OxUrlSorter objects and adds them in specific "sorters" array
         *
         * @param {string[]} orderBy - Sort criteria selected
         * @param {boolean[]} orderDesc - Is DESC indication for every sort criteria
         * @param {OxUrlSorter[]} sorters - Array of all sorters to add in the API request URL
         */
        generateSorters (orderBy: string[], orderDesc: boolean[], sorters: OxUrlSorter[]) {
            orderBy.forEach(
                (orderParam, index) => {
                    // Gets the Datagrid column matching the sort criteria
                    const column = this.columns.find(
                        columnParam => columnParam.value === orderParam
                    ) as OxDatagridColumn

                    // Have to add one sorter for each "filterValues" defined in the matching column
                    //   => "filterValues" contains all "true" attribute values used to make the API request
                    //   => For example : filterValues = is_director
                    if (Array.isArray(column.filterValues)) {
                        column.filterValues.forEach(
                            attr => orderDesc[index]
                                ? sorters.push({ sort: "DESC", choice: attr })
                                : sorters.push({ sort: "ASC", choice: attr })
                        )
                    }
                    else {
                        if (!column.filterValues) {
                            // Automatically deduce the "true" attribute value from the matching column value
                            //   => by converting camelCase in snake_case
                            //   => For example : activityStart becomes activity_start
                            column.filterValues = column.value.replace(
                                /[A-Z]/g,
                                letter => `_${letter.toLowerCase()}`
                            )
                        }

                        orderDesc[index]
                            ? sorters.push({ sort: "DESC", choice: column.filterValues as string })
                            : sorters.push({ sort: "ASC", choice: column.filterValues as string })
                    }
                }
            )
        },

        /**
         * Returns the right icon value depending on whether the group is opened or not
         *
         * @param {boolean} isOpen
         * @return {string} The icon value
         */
        groupByIcon (isOpen: boolean): string {
            return isOpen ? "chevronUp" : "chevronDown"
        },

        /**
         * Returns the "groupBy" value to display in each group.
         * Returns "N/A" as a default value for nullable values
         *
         * @param {string} value - The value requested to be displayed in each group
         * @return {string} The true value to display in each group
         */
        groupByValue (value: string): string {
            return value || "N/A"
        },

        /**
         * Returns the CSS classes for table rows
         *
         * @return {string} The CSS classes
         */
        rowItemClasses (): string {
            return this.stripped ? "stripped OxDatagrid-tableRow" : "OxDatagrid-tableRow"
        },

        /**
         * Returns the custom page text displayed when the Datagrid is not empty
         *
         * @param props - The "page-text" slot available data
         * @return {string} The text displayed when the Datagrid is not empty
         */
        customPageText (props: {
            pageStart: number,
            pageStop: number,
            itemsLength: number
        }): string {
            return props.pageStart + "-" + props.pageStop + " " + this.$tr("common-of") + " " + props.itemsLength
        },

        /**
         * Refreshes the Datagrid by doing a new API request with the new given URL
         *
         * @param {OxUrlBuilder} url
         */
        async refresh (url?: OxUrlBuilder): Promise<void> {
            const newUrl = url || new OxUrlBuilder(this.value.self)
            this.loading = true
            const data = await getCollectionFromJsonApiRequest(
                this.objectClass,
                newUrl.toString()
            )

            this.itemsData = data.objects
            this.totalItems = data.total
            this.selectedItems = []
            this.showMassActions = false
            this.loading = false

            this.$emit("input", data)
        },

        /**
         * Adds the "search" query parameter and removes the "offset" query parameter in the API request.
         * Makes a new API request with the new params by refreshing the Datagrid
         */
        async searchItems () {
            const url = new OxUrlBuilder(this.value.self)

            url.withSearch(this.search).withOffset(null)

            await this.refresh(url)
            this.searchQuery = this.search
            this.displaySearchQuery = !!this.searchQuery
        },

        /**
         * Removes the "search" query parameter in the API request.
         * Makes a new API request with the new params by refreshing the Datagrid
         */
        async resetSearch () {
            const url = new OxUrlBuilder(this.value.self)

            url.withSearch(null)
            this.search = ""

            await this.refresh(url)
            this.displaySearchQuery = false
        },

        /**
         * Update item event propagation
         *
         * @param {OxObject} item - The updated item
         */
        updateItem (item: OxObject) {
            this.$emit("updateItem", item)
        },

        /**
         * Delete item event propagation
         *
         * @param {OxObject} item - The deleted item
         */
        deleteItem (item: OxObject) {
            this.$emit("deleteItem", item)
        },

        /**
         * Filter items event propagation
         *
         * @param {string[]} selectedFilters - The selected filters
         */
        filterItems (selectedFilters: string[]) {
            this.$emit("filterItems", selectedFilters)
        },

        /**
         * Input event propagation
         *
         * @param {OxObject[]} selectedItems - The selected items
         */
        input (selectedItems: OxObject[]) {
            this.selectedItems = selectedItems
            this.showMassActions = this.selectedItems.length > 0
        }
    }
})
</script>
<template>
  <div class="OxDatagrid">
    <v-data-table
      v-model="selectedItems"
      :checkbox-color="primaryColor"
      :class="datagridClasses"
      :custom-sort="customSort"
      :fixed-header="fixedHeaders"
      :footer-props="footerProps"
      :group-by="groupBy"
      :headers="headers"
      height="100%"
      :hide-default-footer="hideFooter"
      :hide-default-header="hideHeader"
      :item-class="rowItemClasses"
      :items="itemsData"
      :items-per-page="itemsData.length"
      :loading="loading"
      :loading-text="$tr('Loading in progress')"
      :multi-sort="multiSort"
      :no-data-text="customableNoDataText"
      :no-results-text="$tr('common-error-Search-no-results')"
      :options.sync="options"
      :search="search"
      :server-items-length="serverItemsLength"
      :show-group-by="showGroupBy"
      :show-select="showSelect"
      @input="input"
    >
      <template #top>
        <div
          v-if="searchable"
          class="OxDatagrid-search"
        >
          <ox-text-field
            hide-details
            :icon="searchFieldIcon"
            :label="searchLabelText"
            rounded
            single-line
            :value="search"
            @change="value => search = value"
            @click:append="resetSearch"
            @keydown.enter="searchItems"
          />
          <p
            v-if="displaySearchQuery"
            class="OxDatagrid-searchQuery"
          >
            {{ $tr("common-label-Search results", searchQuery) }}
          </p>
        </div>
        <div v-if="filters" class="OxDatagrid-filters">
          <div class="OxDatagrid-filterIcon">
            <ox-icon icon="filter" />
          </div>
          <ox-chip-group
            v-model="selectedFilters"
            :choices="filters"
            :multiple="true"
            @change="filterItems(selectedFilters)"
          />
        </div>
      </template>
      <template
        v-if="showMassActions"
        #header="{ props, on }"
      >
        <thead class="OxDatagrid-customHeader">
          <tr>
            <th
              class="text-start"
              style="width: 1px; min-width: 1px;"
            >
              <v-simple-checkbox
                v-model="props.everyItem"
                v-ripple
                :color="primaryColor"
                :indeterminate="props.someItems && !props.everyItem"
                @input="on['toggle-select-all']"
              />
            </th>
            <th :colspan="props.headers.length - 1">
              <div class="OxDatagrid-tableHeaderActions">
                <div class="OxDatagrid-nbSelected">
                  {{ selectedItems.length }} {{ selectionText }}
                </div>
                <div class="OxDatagrid-massActions">
                  <slot
                    name="mass-actions"
                    :selected-items="selectedItems"
                  />
                </div>
              </div>
            </th>
          </tr>
        </thead>
      </template>
      <template
        v-for="column in headers"
        #[`item.${column.value}`]="{ item, value }"
      >
        <slot
          :item="item"
          :name="`item.${column.value}`"
          :value="value"
        >
          {{ value }}
        </slot>
      </template>
      <!-- eslint-disable-next-line -->
      <template v-if="showActions" #item.actions="{ item, value }">
        <div class="OxDatagrid-tableActionsButtons">
          <slot
            :button-delete-attrs="{buttonStyle: 'tertiary-dark', icon: 'delete', title: $tr('Delete')}"
            :button-edit-attrs="{buttonStyle: 'tertiary-dark', icon: 'edit', title: $tr('Edit')}"
            :item="item"
            name="item.actions"
            :value="value"
          >
            <ox-button
              button-style="tertiary-dark"
              icon="edit"
              :title="$tr('Edit')"
              @click="updateItem(item)"
            />
            <ox-button
              button-style="tertiary-dark"
              icon="delete"
              :title="$tr('Delete')"
              @click="deleteItem(item)"
            />
          </slot>
        </div>
      </template>
      <!-- eslint-disable-next-line -->
      <template #group.header="slotProps">
        <td
          class="OxDatagrid-groupByCell"
          :colspan="slotProps.headers.length"
        >
          <ox-button
            button-style="tertiary-dark"
            :icon="groupByIcon(slotProps.isOpen)"
            @click="slotProps.toggle"
          />
          <slot
            :name="`group-${slotProps.groupBy}-${slotProps.group}`"
            v-bind="slotProps"
          >
            {{ groupByTitle }} : <span class="OxDatagrid-groupByValue">{{ groupByValue(slotProps.group) }}</span>
          </slot>
        </td>
      </template>
      <!-- eslint-disable-next-line -->
      <template #footer.page-text="pageTextProps">
        <span v-if="pageTextProps.itemsLength > 0">
          {{ customPageText(pageTextProps) }}
        </span>
      </template>
    </v-data-table>
  </div>
</template>

<style lang="scss" src="./OxDatagrid.scss" />
