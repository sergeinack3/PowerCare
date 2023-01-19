<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div class="INTable">
    <table class="INTable-grid">
      <tr v-if="usePagination">
        <th
          class="INTable-tableHeader"
          :colspan="columns.length + (hasAction ? 1 : 0)">
          <div class="INTablePagination">
            <INButton
              v-if="canFirstPage"
              icon="angle-double-left"
              @click="firstPage"
              button-class="tertiary" />
            <INButton
              v-if="canPreviousPage"
              icon="chevron-left"
              @click="previousPage"
              button-class="tertiary" />
            <div class="INTablePagination-page">
              <INValue :field="currentPage" />
            </div>
            <INButton
              v-if="canNextPage"
              icon="chevron-right"
              @click="nextPage"
              button-class="tertiary" />
            <INButton
              v-if="canLastPage"
              icon="angle-double-right"
              @click="lastPage"
              button-class="tertiary" />
          </div>
        </th>
      </tr>
      <tr v-if="canFilter">
        <th
          class="INTable-tableHeader"
          :colspan="columns.length + (hasAction ? 1 : 0)">
          <INField
            :placeholder="tr('ToFilter')"
            @input="filter"
            :can-reset="true" />
        </th>
      </tr>
      <tr>
        <th
          class="INTable-tableHeader"
          v-for="(key, keyIndex) in columns"
          :key="keyIndex"
          @click="sortBy(key)"
          :class="{isClickable: canAutoSort || canExternalSort}">
          <div
            class="INTable-label"
            :class="{isClickable: canAutoSort || canExternalSort}">
            {{ headTr(key) }}
            <span
              class="INTable-label-sortIcon"
              :class="columnIconClassName(key)">
                <INIcon :icon="columnIcon(key)" />
              </span>
          </div>
        </th>
        <th
          class="INTable-tableHeader"
          v-if="hasAction">&nbsp;
        </th>
      </tr>
      <tr
        class="INTable-row"
        v-for="(line, lineIndex) in sortedData"
        :key="lineIndex">
        <td
          class="INTable-tableCell"
          v-for="(key, keyIndex) in columns"
          :key="lineIndex + '-' + keyIndex">
          <div v-if="typeof(key) === 'object'">
            <INValue
              :field="line[key.field]"
              :length="key.length" />
          </div>
          <div v-else>
            <INValue :field="line[key]" />
          </div>
        </td>
        <td
          class="INTable-tableCell"
          v-if="hasAction">
          <div>
            <INButton
              :icon="iconAction"
              @click="clickAction(line[fieldAction])"
              button-class="tertiary" />
          </div>
        </td>
      </tr>
      <tr v-if="usePagination">
        <th
          class="INTable-tableHeader"
          :colspan="columns.length + (hasAction ? 1 : 0)">
          <div class="INTablePagination">
            <INButton
              v-if="canFirstPage"
              icon="angle-double-left"
              @click="firstPage"
              button-class="tertiary" />
            <INButton
              v-if="canPreviousPage"
              icon="chevron-left"
              @click="previousPage"
              button-class="tertiary" />
            <div class="INTablePagination-page">
              <INValue :field="currentPage" />
            </div>
            <INButton
              v-if="canNextPage"
              icon="chevron-right"
              @click="nextPage"
              button-class="tertiary" />
            <INButton
              v-if="canLastPage"
              icon="angle-double-right"
              @click="lastPage"
              button-class="tertiary" />
          </div>
        </th>
      </tr>
    </table>
  </div>
</template>

<script src="./INTable.ts" lang="ts"></script>

<style src="./INTable.scss" lang="scss" scoped>
</style>
