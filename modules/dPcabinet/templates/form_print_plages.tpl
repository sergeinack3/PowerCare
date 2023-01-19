{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=print_plages_consultation}}

<script>
  Main.add(function() {
    PrintPlagesConsultation.listeCategories = {{$categories|@json:true}};
    PrintPlagesConsultation.loadCategories();
  });
</script>

<form name="paramFrm" action="?m=dPcabinet" method="post" onsubmit="return PrintPlagesConsultation.checkFormPrint('{{'dPcabinet Planning show_print_order_mode'|gconf}}')">
  <table class="main me-align-auto">
    <tr class="me-row-valign me-same-width">
      <td>
        <table class="form">
          <tr>
            <th class="category" colspan="3">Choix de la période</th>
          </tr>

          <tr>
            <td>{{mb_label object=$filter field="_date_min"}}</td>
            <td>{{mb_field object=$filter field="_date_min" form="paramFrm" canNull="false" onchange="PrintPlagesConsultation.changeDateCal()" register=true}} </td>
            <td rowspan="2">
              <input type="radio" name="select_days" onclick="PrintPlagesConsultation.changeDate('{{$now}}','{{$now}}');" value="day" checked="checked" />
              <label for="select_days_day">{{tr}}Current-day{{/tr}}</label>
              <br /><input type="radio" name="select_days" onclick="PrintPlagesConsultation.changeDate('{{$tomorrow}}','{{$tomorrow}}');" value="tomorrow" />
              <label for="select_days_tomorrow">Lendemain</label>
              <br /><input type="radio" name="select_days" onclick="PrintPlagesConsultation.changeDate('{{$week_deb}}','{{$week_fin}}');" value="week" />
              <label for="select_days_week">{{tr}}Current-week{{/tr}}</label>
              <br /><input type="radio" name="select_days" onclick="PrintPlagesConsultation.changeDate('{{$month_deb}}','{{$month_fin}}');" value="month" />
              <label for="select_days_month">{{tr}}Current-month{{/tr}}</label>
            </td>
          </tr>

          <tr>
            <td>{{mb_label object=$filter field="_date_max"}}</td>
            <td>{{mb_field object=$filter field="_date_max" form="paramFrm" canNull="false" onchange="PrintPlagesConsultation.changeDateCal()" register=true}} </td>
          </tr>
        </table>
      </td>

      <td>
        <table class="main form">
          <tr>
            <th class="category" colspan="2">{{tr}}common-Filter settings{{/tr}}</th>
          </tr>

          <tr>
            <th><label for="chir" title="Praticien">{{tr}}common-Practitioner{{/tr}}</label></th>
            <td>
              <select name="chir">
                <option value="0">&mdash; {{tr}}All{{/tr}}</option>
                {{mb_include module=mediusers template=inc_options_mediuser list=$listChir}}
              </select>
            </td>
          </tr>
          <tr>
            <th><label for="function_id" title="{{tr}}Function{{/tr}}">{{tr}}Function{{/tr}}</label></th>
            <td>
              <select name="function_id" onchange="PrintPlagesConsultation.loadCategories();">
                <option value="0">&mdash; {{tr}}All{{/tr}}</option>
                {{mb_include module=mediusers template=inc_options_function list=$listFnc}}
              </select>
            </td>
          </tr>

          <tr>
            <th><label for="category_id" title="{{tr}}CConsultation-categorie_id-desc{{/tr}}">{{tr}}CConsultation-categorie_id-desc{{/tr}}</label></th>
            <td>
              <select name="category_id">
              </select>
            </td>
          </tr>

          {{if 'dPcabinet Planning show_print_order_mode'|gconf}}
            <tr>
              <th class="category" colspan="2">{{tr}}common-Sort settings{{/tr}}</th>
            </tr>

            <tr>
              <th>{{tr}}common-Sorting mode{{/tr}}</th>
              <td>
                <select name="sorting_mode">
                  <option value="chrono">{{tr}}common-Chronological order{{/tr}}</option>
                  <option value="day">{{tr}}common-Birth day{{/tr}}</option>
                  <option value="month">{{tr}}common-Birth month{{/tr}}</option>
                  <option value="year">{{tr}}common-Birth year{{/tr}}</option>
                </select>
              </td>
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}CConsultation|pl{{/tr}}</th>
            <td>
              <select name="canceled" id="annule">
                <option value="all">&mdash; {{tr}}All{{/tr}}</option>
                <option value="not_canceled" selected>{{tr}}CConsultation-Not canceled|pl{{/tr}}</option>
                <option value="canceled">{{tr}}CConsultation-annule-court|pl{{/tr}}</option>
              </select>
            </td>
          </tr>
        </table>
      </td>

      <td>
        <table class="main form">
          <tr>
            <th class="category" colspan="2">{{tr}}common-Display settings{{/tr}}</th>
          </tr>
          {{assign var="class" value="CConsultation"}}

          <tr class="not-full">
            <th>
              <label for="_print_ipp_1" title="Afficher ou cacher l'IPP">Afficher l'IPP</label>
            </th>

            <td>
              <label>
                <input type="radio" name="_print_ipp" value="1" {{if $filter->_print_ipp == "1"}}checked="checked"{{/if}} />
                {{tr}}common-Yes{{/tr}}
              </label>

              <label>
                <input type="radio" name="_print_ipp" value="0" {{if $filter->_print_ipp == "0"}}checked="checked"{{/if}} />
                {{tr}}common-No{{/tr}}
              </label>
            </td>
          </tr>

          <tr>
            <th>{{mb_label object=$filter field="_telephone"}}</th>
            <td>{{mb_field object=$filter field="_telephone"}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$filter field="_coordonnees"}}</th>
            <td>{{mb_field object=$filter field="_coordonnees"}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$filter field="_plages_vides"}}</th>
            <td>{{mb_field object=$filter field="_plages_vides"}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$filter field="_non_pourvues"}}</th>
            <td>{{mb_field object=$filter field="_non_pourvues"}}</td>
          </tr>
        </table>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="3">
        <button type="button" class="print me-primary" onclick="PrintPlagesConsultation.checkFormPrint('{{'dPcabinet Planning show_print_order_mode'|gconf}}')">Afficher</button>
      </td>
    </tr>
  </table>
</form>