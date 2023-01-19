{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="urgences" script="CExtractPassages"}}

<script>
  Main.add(function() {
    getForm('listFilter').onsubmit();
  });
</script>

<form name="listFilter" action="?" method="get" onsubmit="return CExtractPassages.refreshExtractPassages(this)">
  <input type="hidden" name="m" value="dPurgences" />
  <input type="hidden" name="page" value="{{$page}}" onchange="this.form.onsubmit()"/>

  <table class="main layout">
    <tr>
      <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>

      <td>
        <table class="main form">
          <tr>
            <th class="title" colspan="2">{{tr}}CExtractPassages{{/tr}}</th>
          </tr>

          <tr>
            <th style="width: 15%">{{tr}}CExtractPassages-date_extract{{/tr}}</th>
            <td class="text">
              {{mb_field class=CExtractPassages field=_date_min register=true form="listFilter"
                prop=dateTime onchange="\$V(this.form.elements.start, 0)" value="$date_min"}}
              <b>&raquo;</b>
              {{mb_field class=CExtractPassages field=_date_max register=true form="listFilter"
                prop=dateTime onchange="\$V(this.form.elements.start, 0)" value="$date_max"}}
            </td>
          </tr>

          <tr>
            <th style="width: 15%">{{tr}}CExtractPassages-_selection{{/tr}}</th>
            <td class="text">
              {{mb_field class=CExtractPassages field=debut_selection register=true form="listFilter"
              prop=dateTime onchange="\$V(this.form.elements.start, 0)" value=""}}
              <b>&raquo;</b>
              {{mb_field class=CExtractPassages field=fin_selection register=true form="listFilter"
              prop=dateTime onchange="\$V(this.form.elements.start, 0)" value=""}}
            </td>
          </tr>

          <tr>
            <th>{{mb_label class=CExtractPassages field="type"}}</th>
            {{assign var=prop_extractPassages value='Ox\Mediboard\Urgences\CExtractPassages::getTypesActive'|static_call:true}}
            {{$type}}
            <td>
                {{mb_field class=CExtractPassages field="type" onchange="this.form.onsubmit()" value="$type"
                 prop="enum list|$prop_extractPassages default|rpu" typeEnum="radio"}}
            </td>
          </tr>

          <tr>
            <th>{{mb_label class=CExtractPassages field="date_echange"}}</th>
            <td>
              <label>{{tr}}All{{/tr}} <input name="has_send_datetime" value="" type="radio" checked="checked"/></label>
              <label>{{tr}}Sent{{/tr}} <input name="has_send_datetime" value="1" type="radio"/></label>
              <label>{{tr}}CExtractPassages-msg-not sent{{/tr}} <input name="has_send_datetime" value="0" type="radio"/></label>
            </td>
          </tr>

          <tr>
            <td colspan="2">
              <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
            </td>
          </tr>
        </table>
      </td>
    </tr>
   </table>
</form>

<div id="extractPassages" class="me-padding-0">
</div>
