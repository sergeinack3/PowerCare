{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Calendar.regField(getForm("selCabinet").date, null, {noView: true});
  });
</script>

<table class="main">
  <tr>
    <td>
      <form name="selCabinet" method="get">
      <input type="hidden" name="m" value="{{$m}}" />
      <input type="hidden" name="tab" value="{{$tab}}" />
      <table class="form">
        <tr>
          <th class="title">
            Consultations d'anesthésie - 
            {{$date|date_format:$conf.longdate}}
            <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
          </th>
        </tr>
       </table> 
       </form>
    </td>
  </tr>
  <tr>
    <td>
      <table class="tbl">
        <tr>
        {{foreach from=$anesthesistes item=curr_anesthesiste}}
          <th class="title">
            Dr {{$curr_anesthesiste->_view}}
          </th>
        {{/foreach}}
        </tr>
   
        <!-- Affichage de la liste des consultations -->
        <tr>
        {{foreach from=$listPlages item=curr_day key=print_content_id}}
          <td style="width: 200px; vertical-align: top;">
          {{assign var="listPlage" value=$curr_day.plages}}
          {{assign var="date" value=$date}}
          {{assign var="hour" value=$hour}}
          {{assign var="boardItem" value=$boardItem}}
          {{assign var="board" value=$board}}
          {{assign var="tab" value=""}}
          {{assign var="vue" value="0"}}
          {{assign var="userSel" value=$curr_day.anesthesiste}}
          {{assign var="consult" value=$consult}}
          {{assign var="current_m" value="dPcabinet"}}
          {{assign var="print_content_class" value="CMediusers"}}
          {{assign var=mode_urgence value=false}}
          {{mb_include module=cabinet template=inc_list_consult}}
          </td>
        {{/foreach}}
        </tr>
      </table>
    </td>
  </tr>
</table>