{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=chungs value=$object->loadRefsChungScore()}}
{{assign var=igs value=$object->loadRefsExamsIGS()}}

{{if $chungs|@count == 0 && $igs|@count == 0}}
  <span class="empty">Aucune fiche</span>
{{/if}}

{{if $chungs|@count}}
  <table class="tbl">
    <tr>
      <th class="title" colspan="8">{{tr}}CChungScore{{/tr}}</th>
    </tr>
    <tr>
      <th class="category">{{mb_title class=CChungScore field="total"}}</th>
      {{foreach from='Ox\Mediboard\Soins\CChungScore'|static:'fields' item=_field}}
        <th class="category text ">{{mb_title class=CChungScore field=$_field}}</th>
      {{/foreach}}
    </tr>
    {{foreach from=$chungs item=_chung_score}}
      <tr>
        <td
          style="font-weight: bold; font-size: 1.3em; text-align: center;">{{mb_value object=$_chung_score field="total"}}</td>
        {{foreach from='Ox\Mediboard\Soins\CChungScore'|static:'fields' item=_field}}
          <td class="text" style="text-align: center;">{{mb_value object=$_chung_score field=$_field}}</td>
        {{/foreach}}
      </tr>
    {{/foreach}}
  </table>
{{/if}}

{{if $igs|@count}}
  <table class="tbl">
    <tr>
      <th class="title" colspan="19">{{tr}}CExamIgs{{/tr}}</th>
    </tr>
    <tr>
      <th style="font-weight: bold; text-align: center;"
          class="text">{{mb_label class="CExamIgs" field="scoreIGS"}}</th>
      <th style="text-align: center;" class="text">{{mb_label class="CExamIgs" field=simplified_igs}}</th>
      <th>Date</th>
      {{foreach from='Ox\Mediboard\Cabinet\CExamIgs'|static:'fields' item=_field}}
        <th class="text">{{mb_label class="Ox\Mediboard\Cabinet\CExamIgs" field=$_field}}</th>
      {{/foreach}}
    </tr>
    {{foreach from=$igs item=_igs}}
      <tr>
        <td style="font-weight: bold; font-size: 1.3em; text-align: center;">
          {{mb_value object=$_igs field="scoreIGS"}}
        </td>
        <td style="font-size: 1.2em; text-align: center;">
          {{mb_value object=$_igs field=simplified_igs}}
        </td>
        <td class="text" style="text-align: center;">
          {{mb_value object=$_igs field=date}}
        </td>
        {{foreach from='Ox\Mediboard\Cabinet\CExamIgs'|static:'fields' item=_field}}
          <td class="text {{if $_igs->$_field == ''}}empty{{/if}}"
              style="text-align: center;">{{mb_value object=$_igs field=$_field}}</td>
        {{/foreach}}
      </tr>
    {{/foreach}}
  </table>
{{/if}}
