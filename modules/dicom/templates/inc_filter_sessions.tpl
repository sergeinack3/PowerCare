{{*
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <th class="title">
      {{tr}}sessions-dicom{{/tr}}
    </th>
  </tr>
  <tr>
    <td style="text-align: center;">
      <form action="?" name="sessionsFilters" method="post" onsubmit="return DicomSession.refreshSessionsList(this);">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="page" value="{{$page}}" onchange="this.form.onsubmit();"/>
        <input type="hidden" name="order_col" value="begin_date" />
        <input type="hidden" name="order_way" value="DESC" />
        
        <table class="form">
          <tr>
            <th class="category" colspan="4">
              {{tr}}session-date-choice{{/tr}}
            </th>
          </tr>
          <tr>
            <th class="narrow">{{mb_label object=$session field="_date_min"}}</th>
            <td class="narrow">{{mb_field object=$session field="_date_min" form="sessionsFilters" register=true}}</td>
            <th class="narrow">{{mb_label object=$session field="_date_max"}}</th>
            <td class="narrow">{{mb_field object=$session field="_date_max" form="sessionsFilters" register=true}}</td>
          </tr>
          <tr>
            <th class="category" colspan="4">{{tr}}filter-criteria{{/tr}}</th>
          </tr>
          <tr>
            <th colspan="2">{{mb_label object=$session field="group_id"}}</th>
            <td colspan="2" class="me-text-align-left">
              {{mb_field object=$session field="group_id" canNull=true form="filterExchange" autocomplete="true,1,50,true,true"}}
            </td>
          </tr>
          <tr>
            <th colspan="2">{{mb_label object=$session field="sender_id"}}</th>
            <td colspan="2" class="me-text-align-left">
              {{mb_field object=$session field="sender_id" canNull=true form="filterExchange" autocomplete="true,1,50,true,true"}}
            </td>
          </tr>
          {{*<tr>
            <th colspan="2">{{mb_label object=$session field="receiver_id"}}</th>
            <td colspan="2">{{mb_field object=$session field="receiver_id" canNull=true form="filterExchange" autocomplete="true,1,50,true,true"}}</td>
          </tr>*}}
          <tr>
            <th colspan="2">{{mb_label object=$session field="status"}}</th>
            <td colspan="2" class="me-text-align-left">
              {{mb_field object=$session field="status" emptyLabel="tous"}}
            </td>
          </tr>
          <tr>
            <td colspan="4" style="text-align: center;">
              <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>
