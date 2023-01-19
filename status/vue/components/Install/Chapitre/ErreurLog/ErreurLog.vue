<!--
 @author  SAS OpenXtrem <dev@openxtrem.com>
 @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
-->

<template>
  <div
    class="ErreurLog Chapitre-scrollable"
    @scroll="erreurLogScroll">
    <div class="ErreurLog-content">
      <INTabs
        :current-tab="currentTab"
        :tabs="tabs"
        @selecttab="selectTab">
        <div v-show="currentTab === 'Logs'">
          <div class="Chapitre-title">
            {{ tr('Logs') }}
          </div>
          <div
            class="Chapitre-ambiant Chapitre-ambiantLowEmphasis"
            v-html="tr('ambiant-Logs')">
          </div>
          <INTable
            :data="logs"
            :columns="logsColumns"
            header-tr-prefix="Logs"
            :can-auto-sort="false"
            :can-filter="false"/>
          <INLoading v-if="!moreLogLoaded"/>
        </div>
        <div v-show="currentTab === 'Errors'">
          <div class="Chapitre-title">
            {{ tr('Errors') }}
          </div>
          <div
            class="Chapitre-ambiant Chapitre-ambiantLowEmphasis"
            v-html="tr('ambiant-Erreurs')">
          </div>
          <div
            v-if="errorsBufferFilesCount > 0"
            class="Chapitre-ambiant Chapitre-ambiantInfo">
            <div
              class="Chapitre-ambiantIcon"
              :title="errorsBufferPath">
              <INValue :field="false" />
            </div>
            {{ tr('ErrorsBuffer-At least one file', errorsBufferFilesCount, datetime(errorsBufferLastUpdate)) }}
          </div>
          <INTable
            :data="errors"
            :columns="errorsColumns"
            header-tr-prefix="Errors"
            :can-auto-sort="false"
            :can-external-sort="true"
            @sortby="sortError"
            :can-filter="false"
            :use-pagination="true"
            :can-previous-page="errorPagination.hasPrevious()"
            @previouspage="previousErrorPage"
            :can-last-page="errorPagination.hasLast()"
            @lastpage="lastErrorPage"
            :can-next-page="errorPagination.hasNext()"
            @nextpage="nextErrorPage"
            :can-first-page="errorPagination.hasFirst()"
            @firstpage="firstErrorPage"
            :current-page="errorPagination.currentPage" />
        </div>
      </INTabs>
    </div>
  </div>
</template>

<script src="./ErreurLog.ts" lang="ts"></script>
<style src="./ErreurLog.scss" lang="scss">
</style>
