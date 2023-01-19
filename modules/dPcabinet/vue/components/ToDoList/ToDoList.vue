<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { OxBadge, OxButton, OxTextField, OxForm, OxDate } from "oxify"
import { defineComponent, PropType } from "@vue/composition-api"
import { OxEntryPointLinks } from "@/core/types/OxEntryPointTypes"
import OxCollection from "@/core/models/OxCollection"
import ToDoListItem from "@modules/dPcabinet/vue/models/ToDoListItem"
import {
    getCollectionFromJsonApiRequest,
    updateJsonApiObjectFields,
    deleteJsonApiObject, createJsonApiObjects
} from "@/core/utils/OxApiManager"
import { OxUrlBuilder } from "@/core/utils/OxUrlTools"
import ToDoItem from "@modules/dPcabinet/vue/components/ToDoItem/ToDoItem.vue"
import { isEqual } from "lodash"

export default defineComponent({
    components: { ToDoItem, OxButton, OxBadge, OxTextField, OxForm },
    name: "ToDoList",
    props: {
        links: Object as PropType<OxEntryPointLinks>,
        state: {
            type: String,
            default: "closed"
        }
    },
    data () {
        return {
            toDoListItems: {} as OxCollection<ToDoListItem>,
            showNewItem: false,
            newItem: {} as ToDoListItem
        }
    },
    computed: {
        isOpen (): boolean {
            return this.state === "open"
        },
        countItems (): string {
            if (!this.toDoListItems.objects) {
                return "0"
            }

            return this.toDoListItems.objects.filter(item => !item.handledDate).length.toString()
        },
        itemRules (): Function[] {
            return [
                (v) => v.toString().length >= 1 || "Ne peut pas etre vide"
            ]
        }
    },
    async created () {
        await this.updateTodoList()
        this.newItem = new ToDoListItem()
    },
    methods: {
        open () {
            this.$emit("open")
        },
        close () {
            this.$emit("close")
            this.showNewItem = false
        },
        /**
         * Request the todolist items not checked or checked today
         */
        async updateTodoList () {
            this.toDoListItems = await getCollectionFromJsonApiRequest(
                ToDoListItem,
                new OxUrlBuilder(this.links?.todolistitems)
                    .addParameter("handled_date", OxDate.getYMD(new Date()))
                    .toString())
        },
        /**
         * Triggered when todolist item is checked
         */
        async patchTodoListItem (item: ToDoListItem) {
            const object = this.toDoListItems.objects.find(obj => obj.id === item.id)
            if (object) {
                const newItem = await updateJsonApiObjectFields(object, { handled_date: item.handledDate })
                // If the object is not correctly updated, refresh
                if ((!item.handledDate && newItem.handledDate) || (item.handledDate && newItem.handledDate && !isEqual(item.handledDate, newItem.handledDate))) {
                    await this.updateTodoList()
                }
                // Count without request a refresh
                else {
                    object.handledDate = newItem.handledDate
                }
            }
        },
        /**
         * Delete the given todolist item
         * @param item
         */
        async deleteTodoListItem (item: ToDoListItem) {
            const object = this.toDoListItems.objects.find(obj => obj.id === item.id)
            if (object) {
                const response = await deleteJsonApiObject(object)
                if (response) {
                    await this.updateTodoList()
                }
            }
        },
        /**
         * Create new a new todolist item
         */
        async addTodoListItem () {
            // Todolist item text must not be empty
            const validate = (this.$refs.toDoListForm as OxForm).validate()

            if (!validate) {
                return
            }

            if (this.links?.createtodolistitem) {
                const response = await createJsonApiObjects(this.newItem, this.links.createtodolistitem)
                // Reset the input and refresh
                if (response) {
                    this.newItem.libelle = ""
                    this.showNewItem = false
                    await this.updateTodoList()
                }
            }
        },
        // Show the input
        prepareNewTodoListItem () {
            this.showNewItem = true
        }
    }
})
</script>

<template>
  <div class="ToDoList">
    <div
      v-show="!isOpen"
      class="ToDoList-closedContent">
      <ox-badge
        color="secondary"
        :content="countItems"
      >
        <ox-button
          button-style="tertiary"
          @click="open"
          icon="check"
        />
      </ox-badge>
    </div>
    <div
      v-show="isOpen"
      class="ToDoList-header"
    >
      <span class="ToDoList-title">Todolist</span>
      <ox-button
        button-style="tertiary-dark"
        @click="close"
        icon="cancel"
        small
      />
    </div>
    <div
      v-show="isOpen"
      class="ToDoList-list"
    >
      <to-do-item
        v-for="item in toDoListItems.objects"
        :key="item.id"
        :item="item"
        @checkItem="patchTodoListItem"
        @deleteItem="deleteTodoListItem"
      />
      <div class="ToDoList-add">
        <div v-show="showNewItem">
          <ox-form ref="toDoListForm">
            <ox-text-field
              :rules="itemRules"
              v-model="newItem.libelle"
              v-on:keydown.prevent.enter="addTodoListItem"
            />
          </ox-form>
        </div>
        <ox-button
          button-style="tertiary"
          icon="add"
          label="Ajouter une tache"
          small
          @click="prepareNewTodoListItem"
        />
      </div>
    </div>
  </div>
</template>

<style src="./ToDoList.scss" lang="scss"></style>
