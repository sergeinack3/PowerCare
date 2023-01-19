{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=info_examen ajax=$ajax}}

<script>
  Main.add(function() {
    InfoExamen.form = getForm("editExamen");
    InfoExamen.type_examen = '{{$type_examen}}';
    InfoExamen.group_id = '{{$op->_ref_sejour->group_id}}';
    InfoExamen.init();
    {{if $type_examen == "rayons_x"}}
      InfoExamen.form.dose_recue_graphie.addSpinner();
      InfoExamen.form.dose_recue_scopie.addSpinner();
    {{/if}}
  });
</script>

<form name="editExamen" method="post">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_planning_aed" />
  {{mb_key object=$op}}
  <input type="hidden" name="del" value="0" />

  <table class="form">
    {{if $type_examen == "anapath"}}
      <tr>
        <th>{{mb_label object=$op field=flacons_anapath}}</th>
        <td>{{mb_field object=$op field=flacons_anapath form=editExamen prop="num min|0" size=2 increment=true}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$op field=labo_anapath_id}}</th>
        <td>
          {{mb_field object=$op field=labo_anapath_id hidden=true}}
          <input type="text" name="_labo_anapath_id_view" value="{{$op->_ref_labo_anapath->_view}}" />
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$op field=description_anapath}}</th>
        <td>{{mb_field object=$op field=description_anapath form=editExamen aidesaisie="validateOnBlur: 0, width: '100%'"}}</td>
      </tr>
    {{elseif $type_examen == "labo"}}
      <tr>
        <th>{{mb_label object=$op field=flacons_bacterio}}</th>
        <td>{{mb_field object=$op field=flacons_bacterio form=editExamen prop="num min|0" size=2 increment=true}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$op field=labo_bacterio_id}}</th>
        <td>
          {{mb_field object=$op field=labo_bacterio_id hidden=true}}
          <input type="text" name="_labo_bacterio_id_view" value="{{$op->_ref_labo_bacterio->_view}}" />
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$op field=description_bacterio}}</th>
        <td>{{mb_field object=$op field=description_bacterio form=editExamen aidesaisie="validateOnBlur: 0, width: '100%'"}}</td>
      </tr>
    {{elseif $type_examen == "rayons_x"}}
      <tr>
        <th>{{mb_label object=$op field=ampli_id}}</th>
        <td>
          {{mb_field object=$op field=ampli_id hidden=true}}
          <input type="text" name="_ampli_id_view" value="{{$op->_ref_ampli->_view}}" />
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$op field=temps_rayons_x}}</th>
        <td>
          {{mb_field object=$op field=temps_rayons_x hidden=true}}
          <label>
            <input name="_hour_rayons_x" type="number" value="{{$op->temps_rayons_x|date_format:'%H'}}" size="2"
                   onchange="InfoExamen.constructTimeRayonX()" />
            {{tr}}common-Hour|pl{{/tr}}
          <label>
            <input name="_minute_rayons_x" type="number" value="{{$op->temps_rayons_x|date_format:'%M'}}" size="2"
                   onchange="InfoExamen.constructTimeRayonX()" />
            {{tr}}common-Minute|pl{{/tr}}
          </label>
          <label>
            <input name="_seconde_rayons_x" type="number" value="{{$op->temps_rayons_x|date_format:'%S'}}" size="2"
                   onchange="InfoExamen.constructTimeRayonX()" />
            {{tr}}common-Second|pl{{/tr}}
          </label>
        </td>
      </tr>
      {{if $op->dose_rayons_x}}
        <tr>
          <th>{{mb_label object=$op field=dose_rayons_x}}</th>
          <td>{{mb_field object=$op field=dose_rayons_x form=editExamen increment=true}} {{mb_field object=$op field=unite_rayons_x}}</td>
        </tr>
      {{else}}
        <tr>
          <th>{{mb_label object=$op field=unite_rayons_x}}</th>
          <td>{{mb_field object=$op field=unite_rayons_x}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$op field=dose_recue_graphie}}</th>
          <td>
            <input name="dose_recue_graphie" value="{{$op->dose_recue_graphie}}" type="number" size="4">
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$op field=nombre_graphie}}</th>
          <td>{{mb_field object=$op field=nombre_graphie form=editExamen increment=true}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$op field=dose_recue_scopie}}</th>
          <td>
              {{mb_field object=$op field=dose_recue_scopie form=editExamen}}
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$op field=pds}}</th>
          <td>{{mb_field object=$op field=pds form=editExamen increment=true}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$op field=unite_pds}}</th>
          <td>{{mb_field object=$op field=unite_pds}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$op field=kerma}}</th>
          <td>{{mb_field object=$op field=kerma form=editExamen increment=true}}</td>
        </tr>
      {{/if}}
      <tr>
        <th>{{mb_label object=$op field=description_rayons_x}}</th>
        <td>{{mb_field object=$op field=description_rayons_x form=editExamen aidesaisie="validateOnBlur: 0, width: '100%'"}}</td>
      </tr>
    {{/if}}
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="save" onclick="onSubmitFormAjax(this.form, Control.Modal.close)">
          {{tr}}Save{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
