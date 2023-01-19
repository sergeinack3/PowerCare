<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { OxSorter, OxSorterChoiceModel } from "oxify"
import { defineComponent, PropType } from "@vue/composition-api"
import { getSchema } from "@/core/utils/OxStorage"
import { getUrlParams, OxUrlBuilder } from "@/core/utils/OxUrlTools"
import OxObject from "@/core/models/OxObject"
import OxCollection from "@/core/models/OxCollection"
import { getCollectionFromJsonApiRequest } from "@/core/utils/OxApiManager"
import { OxUrlSorter } from "@/core/types/OxUrlTypes"

export default defineComponent({
    components: {
        OxSorter
    },
    props: {
        resourceName: {
            type: String,
            required: true
        },
        choices: {
            type: Array,
            default: () => []
        },
        value: {
            type: Object as PropType<OxCollection<OxObject>>,
            required: true
        }
    },
    data () {
        return {
            sorterChoices: [] as OxSorterChoiceModel[],
            objectClass: Function as unknown as new() => OxObject,
            defaultChoice: null as string | null,
            defaultSort: null as "ASC" | "DESC" | null
        }
    },
    computed: {
        hasObjectClass (): boolean {
            return this.objectClass.prototype instanceof OxObject
        }
    },
    methods: {
        async changeSort (value: OxUrlSorter) {
            if (!this.value.self) {
                this.$emit("update", value)
                return
            }

            let sorters = [] as OxUrlSorter[]

            if (value.choice !== "") {
                sorters = [value]
            }

            const url = new OxUrlBuilder(this.value.self).withSort(...sorters).withOffset(null)

            const items = await getCollectionFromJsonApiRequest(this.objectClass as new() => OxObject, url.toString())

            this.$emit("input", items)
        }
    },
    created () {
        (this.choices as string[]).forEach((choice) => {
            const schema = getSchema(this.resourceName, choice)
            if (schema) {
                this.sorterChoices.push({ value: schema.field, label: schema.label })
            }
        })

        if (this.value &&
            this.value.objects.length > 0 &&
            typeof this.value === "object"
        ) {
            this.objectClass = this.value.objects[0].constructor as new() => OxObject
        }
        else {
            console.warn("Impossible type inference")
        }

        if (this.value && this.value.self) {
            // Sort auto-detection from URL
            const { sort } = getUrlParams(this.value.self)
            if (sort.length > 0) {
                this.defaultChoice = sort[0].choice
                this.defaultSort = sort[0].sort
            }
        }
    }
})
</script>

<template>
  <div
    v-if="hasObjectClass"
    class="OxSort"
  >
    <ox-sorter
      :choices="sorterChoices"
      :defaultChoice="defaultChoice"
      :defaultSort="defaultSort"
      @update="changeSort"
    />
  </div>
</template>

<style lang="scss" src="./OxSort.scss" />
