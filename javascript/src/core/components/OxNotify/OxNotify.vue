<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { defineComponent } from "@vue/composition-api"
import {
    getErrors,
    getInfos,
    localStorageKey, markInfoAsRead,
    removeNotify, removeObsoleteNotifications
} from "@/core/utils/OxNotifyManager"
import { OxNotify } from "@/core/types/OxNotifyTypes"
import { OxSnackbar } from "oxify"

export default defineComponent({
    components: {
        OxSnackbar
    },
    data () {
        return {
            localStorageKey: localStorageKey,
            errors: [] as OxNotify[],
            infos: [] as OxNotify[],
            showNotification: true
        }
    },
    methods: {
        updateNotifications () {
            this.errors = getErrors()
            this.infos = getInfos()
        },
        closeNotify (id: string) {
            if (this.errors.length <= 1) {
                this.showNotification = false
                setTimeout(() => {
                    this.showNotification = true
                }, 100)
            }
            removeNotify(id)
        }
    },
    computed: {
        showErrors (): boolean {
            return this.errors.length > 0
        },
        infoShown (): OxNotify | null {
            if (this.infos.length > 0 && !this.showErrors) {
                return this.infos[0]
            }
            else {
                return null
            }
        }
    },
    watch: {
        infoShown: {
            handler (newInfo, oldInfo) {
                if (newInfo && !newInfo.read && newInfo?.id !== oldInfo?.id) {
                    // Display new info
                    setTimeout(() => {
                        markInfoAsRead(newInfo.id)
                    }, newInfo.minTime)
                }
            }
        }
    },
    created () {
        removeObsoleteNotifications()
        this.updateNotifications()
        window.addEventListener("notify", this.updateNotifications)
    },
    destroyed () {
        window.removeEventListener("notify", this.updateNotifications)
    }
})
</script>

<template>
  <div
    class="OxNotify"
  >
    <div
      v-if="showNotification"
      class="OxNotify-content"
    >
      <template v-if="showErrors">
        <ox-snackbar
          v-for="error in errors"
          :key="'notify-' + error.id"
          :text="error.message"
          :error="true"
          @click:close="closeNotify(error.id)"
        />
      </template>
      <template v-if="infoShown">
        <ox-snackbar
          :closable="infoShown.closable"
          :timeout="infoShown.maxTime"
          :text="infoShown.message"
          @click:close="closeNotify(infoShown.id)"
        />
      </template>
    </div>
  </div>
</template>

<style src="./OxNotify.scss" lang="scss"></style>
