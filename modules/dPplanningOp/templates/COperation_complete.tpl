{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="operation" value=$object}}
{{assign var=limit_prise_rdv value=$app->user_prefs.limit_prise_rdv}}

<script type="text/javascript">
  Main.add(function() {
    var url = new Url('salleOp', 'httpreq_vw_timing');
    url.addParam('operation_id', '{{$operation->_id}}');
    url.addParam('submitTiming', 'submitTiming');
    url.addParam('readonly', 1);
    url.requestUpdate('timings');
  });
</script>

<table class="form">
  <tr>
    <th class="title" colspan="2">
      {{mb_include module=system template=inc_object_idsante400}}
      {{mb_include module=system template=inc_object_history}}
      {{mb_include module=system template=inc_object_notes}}
      {{$operation}}
    </th>
  </tr>
  
  {{if $operation->annulee == 1}}
  <tr>
    <th class="category cancelled" colspan="4">
    {{tr}}COperation-annulee{{/tr}}
    </th>
  </tr>
  {{/if}}

  <tr>
    <td>
      <strong>{{tr}}COperation-chir_id-court{{/tr}} :</strong>
      {{$operation->_ref_chir->_view}}
    </td>
    <td>
      <strong>{{tr}}COperation-anesth_id-court{{/tr}} :</strong>
      {{$operation->_ref_anesth->_view}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>{{tr}}COperation-date-court{{/tr}} :</strong>
      {{$operation->_datetime|date_format:"%d %B %Y"}}
    </td>
    <td>
    </td>
  </tr>

  {{if !$limit_prise_rdv}}
    <tr>
      <td>
        <strong>{{tr}}COperation-libelle{{/tr}} :</strong>
        {{$operation->libelle}}
      </td>
      <td>
        <strong>{{tr}}COperation-cote{{/tr}} :</strong>
        {{tr}}{{$operation->cote}}{{/tr}}
      </td>
    </tr>

    <tr>
      <td>
        <strong>{{tr}}COperation-_lu_type_anesth{{/tr}} :</strong>
        {{$operation->_lu_type_anesth}}
      </td>
    </tr>

    {{if $operation->materiel}}
      <tr>
        <td class="text" colspan="2">
          <strong>{{tr}}COperation-materiel-court{{/tr}} :</strong>
          {{$operation->materiel|nl2br}}
        </td>
      </tr>
    {{/if}}

    {{if $operation->exam_per_op}}
      <tr>
        <td class="text" colspan="2">
          <strong>{{tr}}COperation-exam_per_op-court{{/tr}} :</strong>
          {{$operation->exam_per_op|nl2br}}
        </td>
      </tr>
    {{/if}}

    {{if $operation->rques}}
      <tr>
        <td class="text" colspan="2">
          <strong>{{tr}}COperation-rques-court{{/tr}} :</strong>
          {{$operation->rques|nl2br}}
        </td>
      </tr>
    {{/if}}

    {{if $operation->examen}}
      <tr>
        <td class="text" colspan="2">
          <strong>{{tr}}COperation-examen{{/tr}} :</strong>
          {{$operation->examen|nl2br}}
        </td>
      </tr>
    {{/if}}
  {{/if}}
</table>

{{if !$limit_prise_rdv}}
  <table class="tbl">
    {{mb_include module=cabinet template=inc_list_actes_ccam subject=$object vue=complete}}
  </table>

  <div id="timings"></div>
{{/if}}