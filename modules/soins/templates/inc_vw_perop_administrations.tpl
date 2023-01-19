{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=salleOp           script=salleOp            ajax=1}}
{{mb_script module=monitoringPatient script=surveillance_perop ajax=1}}

<table class="tbl">
  <tr>
    <td colspan="6">
      <button type="button" class="print" onclick="SalleOp.printFicheBloc($V('operation_select'));">
          {{tr}}COperation-action-Block sheet{{/tr}}
      </button>
      <button class="print not-printable me-tertiary me-dark"
              onclick="SurveillancePerop.printSurveillance($V('operation_select'));">
          {{tr}}CSupervisionGraphToPack-action-Print monitoring{{/tr}}
      </button>
      <select id="operation_select" {{if 1 >= $operations|@count}}class="me-no-display"{{/if}}
              onchange="PlanSoins.showPeropAdministrationsOperation('{{$prescription_id}}',
                '{{$operation->sejour_id}}',$V(this), this.checked ? 1 : 0);">
        {{foreach from=$operations name=sejour_operations item=_operation}}
          <option {{if $operation->_id === $_operation->_id}}selected{{/if}} value="{{$_operation->_id}}">
            {{$_operation}} - {{$_operation->libelle}}
          </option>
        {{/foreach}}
      </select>
    </td>
  </tr>
  <tr>
    <th colspan="7" class="title">
      <div class="me-float-right">
        <form name="editPrefGeste" method="post">
          <label>
            <input type="checkbox" {{if $show_administrations}}checked{{/if}}
                   onclick="PlanSoins.showPeropAdministrationsOperation('{{$prescription_id}}',
                     '{{$operation->sejour_id}}',$V('operation_select'), this.checked ? 1 : 0);"/>
            <span>{{tr}}COperation-action-Show the administrations{{/tr}}</span>
          </label>
        </form>
      </div>

        {{tr}}COperation-Administration and operative event|pl{{/tr}} ({{$count_administrations_gestes}})
    </th>
  </tr>
  <tr>
    <th class="narrow">{{tr}}CAdministration-_date{{/tr}}</th>
    <th class="narrow">{{tr}}CAdministration-_time{{/tr}}</th>
    <th>{{tr}}CPrescriptionLineMedicament-_ucd_view{{/tr}} / {{tr}}CAnesthPerop-libelle{{/tr}}</th>
    <th>{{tr}}CPrisePosologie-quantite{{/tr}}</th>
    <th>{{tr}}CAdministration-administrateur_id{{/tr}}</th>
    <th class="narrow">{{tr}}CAdministration-_perop_section{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
  {{foreach from=$perops key=datetime item=_perops_by_datetime}}
    {{foreach from=$_perops_by_datetime item=_perop}}
      {{assign var=quantite       value=""}}
      {{assign var=unite          value=""}}
      {{assign var=administrateur value=""}}

      {{if is_object($_perop)}}
        {{if $_perop|instanceof:'Ox\Mediboard\SalleOp\CAnesthPerop'}}
          {{assign var=perop_user value=$_perop->_ref_user}}
          {{if $perop_user && $perop_user->_id}}
            {{assign var=administrateur value=$perop_user}}
          {{/if}}
        {{elseif $_perop|instanceof:'Ox\Mediboard\PlanSoins\CAdministration'}}
          {{assign var=administrateur value=$_perop->_ref_administrateur}}
          {{assign var=quantite value=$_perop->quantite}}

          {{if $_perop->_ref_object|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMedicament' ||
               $_perop->_ref_object|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMixItem'}}

            {{mb_ternary var=unite test=$_perop->_ref_object->_unite_livret value=$_perop->_ref_object->_unite_livret other=$_perop->_ref_object->_unite_reference_libelle}}
            {{mb_ternary var=quantite test=$_perop->_ref_object->_qte_livret value=$_perop->_ref_object->_qte_livret other=$_perop->quantite}}
          {{else}}
            {{assign var=unite value=$_perop->_ref_object->_unite_prise}}
          {{/if}}

        {{elseif $_perop|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMix'}}
          {{assign var=administrateur value=$_perop->_ref_praticien}}
        {{/if}}
      {{/if}}

      <tr
        {{if $_perop|instanceof:'Ox\Mediboard\PlanSoins\CAdministration'
        && $_perop->_ref_object|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMedicament'
        && $_perop->_ref_object->atc|strstr:"N02"}}
          class="perop"
        {{/if}}>
        <td style="text-align: center;">{{mb_ditto name=date value=$datetime|date_format:$conf.date}}</td>
        <td style="text-align: center;">{{mb_ditto name=time value=$datetime|date_format:$conf.time}}</td>
        {{if is_object($_perop)}}
          {{if $_perop|instanceof:'Ox\Mediboard\SalleOp\CAnesthPerop'}}
            <td class="text">
              <strong style="display: inline-block;" onmouseover="ObjectTooltip.createEx(this, '{{$_perop->_guid}}');">
                {{if $_perop->incident}}
                  <i class="fas fa-exclamation-triangle" style="color: red"></i> {{tr}}CAnesthPerop-incident{{/tr}} :
                {{/if}}

                {{$_perop->_view_completed}}
              </strong>

              {{if $_perop->commentaire}}
                : {{$_perop->commentaire}}
              {{/if}}
            </td>
          {{elseif $_perop|instanceof:'Ox\Mediboard\PlanSoins\CAdministration'}}
            <td class="greedyPane">
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_perop->_guid}}');">
                {{if $_perop->_ref_object|instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement'}}
                  {{$_perop->_ref_object->_view}}
                {{else}}
                  {{$_perop->_ref_object->_ucd_view}}
                {{/if}}
              </span>
            </td>
          {{elseif $_perop|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMix'}}
            <td>
              {{if $datetime == $_perop->_pose}}
                {{tr}}pose_perfusion{{/tr}} -
              {{else}}
                {{tr}}retrait_perfusion{{/tr}} -
              {{/if}}
              {{$_perop->_short_view}}
            </td>
          {{/if}}
          <td>
            <strong>{{$quantite}} {{$unite}}</strong>
          </td>
          <td>
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$administrateur}}
          </td>
          <td>
            {{tr}}CAdministration-section.{{$_perop->_perop_section}}{{/tr}}
          </td>
          <td>
            {{mb_include module=system template=inc_object_history object=$_perop}}
          </td>
        {{/if}}
      </tr>
    {{/foreach}}
  {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">
        {{tr}}COperation-Administration and operative event.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
