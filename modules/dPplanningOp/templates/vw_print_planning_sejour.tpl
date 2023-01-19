{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=planning ajax=true}}

<script>
  Main.add(function () {
    window.print();
  });
</script>

<div id="planning-{{$sejour->_guid}}">
  <table class="main tbl">
    <tr>
      <th class="title" colspan="4">
        {{assign var=patient value=$sejour->_ref_patient}}

        <button class="print not-printable" style="float: right;" onclick="window.print();">{{tr}}Print{{/tr}}</button>

          {{tr}}CSejour-Planning stay of{{/tr}} <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">{{$patient->_view}}</span> -
        ({{tr var1=$sejour->entree|date_format:$conf.longdate var2=$sejour->sortie|date_format:$conf.longdate}}common-From %s to %s{{/tr}})
      </th>
    </tr>
    {{*Consultations*}}
    {{if $consultations|@count}}
      <tr>
        <th class="section" colspan="4">{{tr}}CConsultation{{/tr}} ({{$consultations|@count}})</th>
      </tr>
      <tr>
        <th>{{tr}}common-Label{{/tr}}</th>
        <th>{{tr}}common-Practitioner{{/tr}}</th>
        <th class="narrow">{{tr}}common-datetime_spec-desc{{/tr}}</th>
        <th class="narrow">{{tr}}common-Duration{{/tr}}</th>
      </tr>
      {{foreach from=$consultations item=_consultation}}
          {{assign var=praticien value=$_consultation->_ref_plageconsult->_ref_chir}}
        <tr>
          <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_consultation->_guid}}')">
          {{$_consultation->_view}}
        </span>
          </td>
          <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$praticien->_guid}}')">
          {{$praticien->_view}}
        </span>
          </td>
          <td class="narrow">{{$_consultation->_datetime|date_format:$conf.datetime}}</td>
          <td class="narrow">{{'Ox\Core\CMbDT::minutesRelative'|static_call:$_consultation->_datetime:$_consultation->_date_fin}}</td>
        </tr>
      {{/foreach}}
    {{/if}}
    {{*Interventions*}}
    {{if $operations|@count}}
      <tr>
        <th class="section" colspan="4">{{tr}}COperation{{/tr}} ({{$operations|@count}})</th>
      </tr>
      <tr>
        <th>{{tr}}common-Label{{/tr}}</th>
        <th>{{tr}}common-Practitioner{{/tr}}</th>
        <th>{{tr}}common-datetime_spec-desc{{/tr}}</th>
        <th class="narrow">{{tr}}common-Duration{{/tr}}</th>
      </tr>
      {{foreach from=$operations item=_operation}}
          {{assign var=praticien value=$_operation->_ref_chir}}
        <tr>
          <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')">
          {{$_operation->libelle}}
        </span>
          </td>
          <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$praticien->_guid}}')">
          {{$praticien->_view}}
        </span>
          </td>
          <td>{{$_operation->_datetime|date_format:$conf.datetime}}</td>
          <td>{{'Ox\Core\CMbDT::minutesRelative'|static_call:$_operation->_datetime:$_operation->_acte_execution}}</td>
        </tr>
      {{/foreach}}
    {{/if}}
    {{*Lignes d'éléments*}}
    {{if $lines_counter}}
      <tr>
        <th class="section" colspan="4">{{tr}}CPrescriptionLineElement{{/tr}} ({{$lines_counter}})</th>
      </tr>
      <tr>
        <th>{{tr}}common-Label{{/tr}}</th>
        <th>{{tr}}common-Practitioner{{/tr}}</th>
        <th>{{tr}}common-datetime_spec-desc{{/tr}}</th>
        <th class="narrow">{{tr}}common-Duration{{/tr}}</th>
      </tr>
      {{foreach from=$lines item=_category}}
        {{foreach from=$_category item=_line}}
          <tr>
            <td>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_line.object->_guid}}')">
                {{$_line.label}}
              </span>
            </td>
            <td>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_line.object->_ref_praticien->_guid}}')">
                {{$_line.object->_ref_praticien->_view}}
              </span>
            </td>
            <td>{{$_line.datetime|date_format:$conf.datetime}}</td>
            <td>{{$_line.duration}}</td>
          </tr>
        {{/foreach}}
      {{/foreach}}
    {{/if}}
    {{*RDV externes*}}
    {{if $rdv_externes|@count}}
      <tr>
        <th class="section" colspan="4">{{tr}}CRDVExterne{{/tr}} ({{$rdv_externes|@count}})</th>
      </tr>
      <tr>
        <th>{{tr}}common-Label{{/tr}}</th>
        <th>{{tr}}CRDVExterne-statut{{/tr}}</th>
        <th>{{tr}}common-datetime_spec-desc{{/tr}}</th>
        <th class="narrow">{{tr}}common-Duration{{/tr}}</th>
      </tr>
        {{foreach from=$rdv_externes item=_rdv}}
          <tr>
            <td>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_rdv->_guid}}')">
                {{$_rdv->libelle}}
              </span>
            </td>
            <td>
                {{$_rdv->statut}}
            </td>
            <td>{{$_rdv->date_debut|date_format:$conf.datetime}}</td>
            <td>{{$_rdv->duree}}</td>
          </tr>
        {{/foreach}}
    {{/if}}

    {{if !$consultations|@count && !$operations|@count && !$lines_counter && !$rdv_externes|@count}}
      <tr>
        <td class="empty">{{tr}}CSejour-No planning stay for this patient{{/tr}}</td>
      </tr>
    {{/if}}
  </table>
</div>
