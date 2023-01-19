<!--
  @author  SAS OpenXtrem <dev@openxtrem.com>
  @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<script lang="ts">
import { defineComponent, PropType } from "@vue/composition-api"
import {
    OxEntryPointConfigs,
    OxEntryPointLinks, OxEntryPointLocales,
    OxEntryPointMeta,
    OxEntryPointPrefs
} from "@/core/types/OxEntryPointTypes"

export default defineComponent({
    name: "OxEntryPoint",
    props: {
        links: Object as PropType<OxEntryPointLinks>,
        configs: Object as PropType<OxEntryPointConfigs>,
        prefs: Object as PropType<OxEntryPointPrefs>,
        meta: Object as PropType<OxEntryPointMeta>,
        locales: Object as PropType<OxEntryPointLocales>
    }
})
</script>
