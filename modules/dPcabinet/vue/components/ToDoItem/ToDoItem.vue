<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { OxButton, OxCheckbox, OxDate } from "oxify"
import { defineComponent, PropType } from "@vue/composition-api"
import ToDoListItem from "@modules/dPcabinet/vue/models/ToDoListItem"
import { cloneObject } from "@/core/utils/OxObjectTools"

export default defineComponent({
    components: { OxCheckbox, OxButton },
    name: "ToDoItem",
    props: {
        item: Object as PropType<ToDoListItem>
    },
    data () {
        return {
            mutatedItem: {} as ToDoListItem
        }
    },
    computed: {
        isHandled (): boolean {
            return !!this.mutatedItem.handledDate
        }
    },
    created () {
        this.mutatedItem = this.item ? cloneObject(this.item) : new ToDoListItem()
    },
    methods: {
        checkItem (value) {
            this.mutatedItem.handledDate = value ? OxDate.getYMD(new Date()) : ""
            this.$emit("checkItem", this.mutatedItem)
        },
        deleteItem () {
            this.$emit("deleteItem", this.mutatedItem)
        }
    },
    watch: {
        item: {
            handler (item: ToDoListItem) {
                this.mutatedItem = item
            }
        }
    }
})
</script>

<template>
  <div class="ToDoItem">
    <ox-checkbox
      :label="mutatedItem.libelle"
      :value="isHandled"
      @change="checkItem"
    />
    <div class="ToDoItem-hoverActions">
      <ox-button
        button-style="tertiary-dark"
        icon="delete"
        small
        @click="deleteItem"
      />
    </div>
  </div>
</template>

<style src="./ToDoItem.scss" lang="scss"></style>
