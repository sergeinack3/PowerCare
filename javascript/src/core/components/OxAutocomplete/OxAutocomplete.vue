<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { defineComponent, PropType } from "@vue/composition-api"
import { getCollectionFromJsonApiRequest } from "@/core/utils/OxApiManager"
import { OxIcon, OxThemeCore } from "oxify"
import OxObject from "@/core/models/OxObject"
import { debounce } from "lodash"
import { OxUrlBuilder } from "@/core/utils/OxUrlTools"
import { VAutocomplete } from "vuetify/lib/components/VAutocomplete"
import { VCombobox } from "vuetify/lib/components/VCombobox"

export default defineComponent({
    name: "OxAutocomplete",
    components: {
        OxIcon,
        VCombobox,
        VAutocomplete
    },
    props: {
        autoSelectFirst: {
            type: Boolean,
            default: false
        },
        label: {
            type: String,
            default: ""
        },
        placeholder: {
            type: String,
            default: ""
        },
        url: {
            type: String,
            required: true
        },
        icon: {
            type: String,
            default: "search"
        },
        items: {
            type: Array as PropType<OxObject[]>,
            default: () => []
        },
        itemText: {
            type: String,
            required: false,
            default: "text"
        },
        itemValue: {
            type: String,
            default: "id"
        },
        minCharSearch: {
            type: Number,
            default: 3
        },
        maxResult: {
            type: Number,
            default: 5
        },
        multiple: {
            type: Boolean,
            default: false
        },
        noFilter: {
            type: Boolean,
            default: true
        },
        notNull: {
            type: Boolean,
            default: false
        },
        oxObject: {
            type: Function as PropType<new () => OxObject>
        },
        rounded: {
            type: Boolean,
            default: false
        },
        value: {
            type: [Object, Array] as PropType<OxObject | OxObject[]>,
            required: false
        },
        iconColor: {
            type: String,
            default: OxThemeCore.onBackgroundMediumEmphasis
        },
        searchField: {
            type: String,
            default: ""
        },
        clearable: {
            type: Boolean,
            default: false
        },
        searchable: {
            type: Boolean,
            default: false
        }
    },
    data () {
        return {
            itemsData: [] as OxObject[],
            loading: false,
            search: "",
            objectClass: Function as unknown as new () => OxObject,
            noDataResponse: false,
            selfClearable: false,
            hideResults: false
        }
    },
    computed: {
        hasItem (): boolean {
            return !!this.$scopedSlots.item
        },
        hasSelection (): boolean {
            return !!this.$scopedSlots.selection
        },
        hideNoData (): boolean {
            return !this.noDataResponse
        },
        isClearable (): boolean {
            return this.selfClearable && this.clearable
        },
        autocompleteComponent (): string {
            return this.searchable ? "v-combobox" : "v-autocomplete"
        },
        autocompleteClasses (): string {
            return this.hideResults ? "hideMenu" : ""
        },
        rules (): Array<Function> {
            const rules: Array<Function> = []
            if (this.notNull) {
                rules.push(v => (!!v || this.$tr("Missing-field")))
            }
            return rules
        },
        decoratedLabel (): string {
            return this.label + (this.notNull ? " *" : "")
        }
    },
    watch: {
        items: {
            handler (newItems) {
                this.itemsData = newItems
            },
            immediate: true
        },
        search (value) {
            if (value === null) {
                this.selfClearable = false
            }
            if (!this.value || (typeof this.value === "object" && value !== this.value[this.itemText])) {
                this.loadItems(value, false)
            }
            this.hideResults = false
        }
    },
    created () {
        // Add defaults selected items in choices
        if (this.value) {
            if (!Array.isArray(this.value) &&
                typeof this.value === "object" &&
                this.value.id &&
                this.items?.length === 0
            ) {
                this.itemsData = [this.value]
            }
            else if (Array.isArray(this.value) && this.items?.length === 0) {
                this.itemsData = this.value
            }
        }

        // Object type inference
        if (this.oxObject) {
            this.objectClass = this.oxObject
        }
        else if (this.value &&
            !Array.isArray(this.value) &&
            typeof this.value === "object"
        ) {
            this.objectClass = this.value.constructor as new() => OxObject
        }
        else {
            throw new Error("Impossible type inference")
        }
    },
    methods: {
        /* eslint-disable  @typescript-eslint/no-explicit-any */
        loadItems: debounce(
            async function (this: any, value, force) {
                await this.searchItems(value, force)
            },
            400
        ),
        async searchItems (value, force: boolean) {
            if ((!value || value.length < this.minCharSearch) && !force) {
                return
            }

            this.loading = true
            const url = new OxUrlBuilder(this.url).withLimit(this.maxResult.toString())
            if (this.searchField === "") {
                url.withSearch(value)
            }
            else {
                url.withFilters({ key: this.searchField, operator: "contains", value: value })
            }

            const objects = await getCollectionFromJsonApiRequest(this.objectClass as new() => OxObject, url.toString())
            this.noDataResponse = objects.objects.length === 0

            if (this.value && Array.isArray(this.value) && this.multiple) {
                // Merge results with selected values on multiple autocomplete
                this.itemsData = [...objects.objects, ...this.value]
            }
            else {
                this.itemsData = objects.objects
            }

            this.loading = false
        },
        input (value) {
            // Reset search text after change when multiple
            if (this.multiple) {
                this.search = ""
            }
            if (!this.searchable) {
                this.$emit("input", value)
            }
            else if (this.searchable && value?.constructor?.name === this.objectClass.name) {
                this.$emit("input", value)
            }
            this.selfClearable = true
        },
        enterDown () {
            // Hide results when component is searchable and a search is made
            this.hideResults = true
            this.$emit("enter", this.search)
        },
        onBlur () {
            this.noDataResponse = false
            // Keep selected value in items in order to display his itemText in autocomplete
            if (this.value && !this.searchable && !Array.isArray(this.value) && this.value[this.itemText] && this.value.id) {
                this.itemsData = [this.value]
            }
        },
        makeLoad () {
            if (this.minCharSearch === 0 && (!this.itemsData || this.itemsData.length === 0)) {
                this.loadItems("", true)
            }
        }
    }
})
</script>
<template>
  <div
    class="OxAutocomplete"
    :class="autocompleteClasses"
  >
    <div
      v-if="label"
      class="OxAutocomplete-label"
    >
      {{ decoratedLabel }}
    </div>
    <component
      :append-icon="icon"
      :attach="true"
      :auto-select-first="autoSelectFirst"
      :clearable="isClearable"
      filled
      hide-details="auto"
      :hide-no-data="hideNoData"
      :is="autocompleteComponent"
      :item-text="itemText"
      :item-value="itemValue"
      :items="itemsData"
      :loading="loading"
      :multiple="multiple"
      :no-filter="noFilter"
      :placeholder="placeholder"
      return-object
      :rounded="rounded"
      :rules="rules"
      :search-input.sync="search"
      :value="value"
      @blur="onBlur"
      @click="makeLoad"
      @input="input"
      @keydown.enter="enterDown"
    >
      <template
        v-if="hasSelection"
        #selection="data"
      >
        <slot
          :item="data.item"
          name="selection"
        ></slot>
      </template>
      <template
        v-if="hasItem"
        #item="data"
      >
        <div class="OxAutocomplete-item">
          <slot
            :item="data.item"
            name="item"
          />
        </div>
      </template>
      <template #append>
        <OxIcon
          :color="iconColor"
          :icon="icon"
        />
      </template>
      <template #no-data>
          <div
            v-if="noDataResponse"
            class="OxAutocomplete-emptyMessage"
          >
            {{ $tr("OxAutocomplete-noData") }}
          </div>
      </template>
    </component>
  </div>
</template>

<style lang="scss" src="./OxAutocomplete.scss" />
