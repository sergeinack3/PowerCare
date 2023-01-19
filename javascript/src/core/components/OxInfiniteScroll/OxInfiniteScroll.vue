<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { defineComponent, PropType } from "@vue/composition-api"
import OxCollection from "@/core/models/OxCollection"
import OxObject from "@/core/models/OxObject"
import { getCollectionFromJsonApiRequest } from "@/core/utils/OxApiManager"
import { OxUrlBuilder } from "@/core/utils/OxUrlTools"

export default defineComponent({
    props: {
        value: {
            type: Object as PropType<OxCollection<OxObject>>,
            required: true
        },
        bottomPxTrigger: {
            type: Number,
            default: 0
        }
    },
    data () {
        return {
            containerElement: {} as Element,
            loading: false,
            objectClass: Function as Function
        }
    },
    computed: {
        hasObjectClass (): boolean {
            return this.objectClass.prototype instanceof OxObject
        }
    },
    watch: {
        value: {
            handler () {
                this.$nextTick(async () => {
                    let endCollection = false
                    while (!this.isScrollable() && !endCollection) {
                        endCollection = await this.loadNextCollection()
                    }
                })
            },
            immediate: true
        }
    },
    methods: {
        async scroll () {
            if (this.containerElement.scrollHeight - this.containerElement.scrollTop <=
                this.containerElement.clientHeight + this.bottomPxTrigger) {
                await this.loadNextCollection()
            }
        },
        isScrollable (): boolean {
            return this.containerElement.scrollHeight > this.containerElement.clientHeight
        },
        async loadNextCollection (): Promise<boolean> {
            if (!this.value.next) {
                return true
            }
            if (this.loading) {
                return false
            }

            this.loading = true
            const nextCollection = await getCollectionFromJsonApiRequest(
                this.objectClass as new() => OxObject,
                new OxUrlBuilder(this.value.next).toString()
            )

            nextCollection.objects = [...this.value.objects, ...nextCollection.objects]
            nextCollection.count = (this.value.count ?? 0) + (nextCollection.count ?? 0)

            this.$emit("input", nextCollection)
            this.loading = false
            return false
        }
    },
    mounted () {
        if (this.value &&
            this.value.objects.length > 0 &&
            typeof this.value === "object"
        ) {
            this.objectClass = this.value.objects[0].constructor as new() => OxObject
            this.$nextTick(() => {
                this.containerElement = this.$refs.scrollContainer as Element
                this.containerElement.addEventListener("scroll", this.scroll)
            })
        }
        else {
            console.warn("Impossible type inference")
        }
    },
    destroyed () {
        this.containerElement.removeEventListener("scroll", this.scroll)
    }
})
</script>

<template>
  <div
    v-if="hasObjectClass"
    class="OxInfiniteScroll"
    ref="scrollContainer"
  >
    <slot></slot>
    <slot
      v-if="loading"
      name="loading"
    >
      {{ $tr("Loading in progress") }}...
    </slot>
  </div>
</template>

<style src="./OxInfiniteScroll.scss" lang="scss"></style>
