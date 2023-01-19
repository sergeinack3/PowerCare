{{*
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $total_sessions != 0}}
  {{mb_include module=system template=inc_pagination total=$total_sessions current=$page change_page='DicomSession.changePage' step=20}}
{{/if}}

<table class="tbl">
  <thead>
  <tr>
    <th></th>
    <th>{{tr}}Actions{{/tr}}</th>
    <th>{{tr}}Details{{/tr}}</th>
    <th>{{mb_label object=$session field="begin_date"}}</th>
    <th>{{mb_label object=$session field="end_date"}}</th>
    <th>{{mb_label object=$session field="_duration"}}</th>
    <th>{{mb_label object=$session field="sender"}}</th>
    <th>{{mb_label object=$session field="receiver"}}</th>
    <th>{{mb_label object=$session field="status"}}</th>
  </tr>
  </thead>
  <tbody>
    {{foreach from=$sessions item=_session}}
      {{mb_include template=inc_session object=$_session}}
    {{foreachelse}}
      <tr>
        <td colspan="9" class="empty">
          {{tr}}CDicomSession.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </tbody>
</table>