{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  jsonSejours = {};
  validationSejours = function() {
    var reply = confirm('Attention vous êtes en train de mettre une sortie réelle à la totatité des séjours sélectionnés.');
    if (reply) {
      var form = getForm('selectedSejours');
      $V(form.sejours, Object.toJSON(jsonSejours));
      return onSubmitFormAjax(form);
    }
  };
</script>

<table class="tbl main" id="tbl_sejours_masse">
  <tr>
    <th colspan="8">
      <form name="selectedSejours" method="post" action="?">
        <input type="hidden" name="m" value="planningOp" />
        <input type="hidden" name="dosql" value="do_change_sortie_sejours_aed" />
        <input type="hidden" name="sejours" value="" />
        <input type="hidden" name="callback" value="seeSejourMasse" />

        {{assign var=field_mode_sortie value="mode_sortie"}}
        {{assign var=mode_sortie_hidden value=""}}

        {{if $modes_sorties|@count}}
          {{assign var=field_mode_sortie value="mode_sortie_id"}}
          {{assign var=mode_sortie_hidden value=true}}
        {{/if}}

        {{me_form_field mb_object=$filter mb_field=$field_mode_sortie style_css="display: inline;"}}
          {{if $modes_sorties|@count}}
            <select name="mode_sortie_id">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$modes_sorties item=_mode_sortie}}
                <option value="{{$_mode_sortie->_id}}">
                  {{$_mode_sortie->_view}}
                </option>
              {{/foreach}}
            </select>
          {{else}}
            {{mb_field object=$filter field=mode_sortie hidden=$mode_sortie_hidden emptyLabel='Choose'}}
          {{/if}}

          <button type="button" class="tick me-primary" onclick="validationSejours();" id="btt_valide_sejours" disabled>{{tr}}Validate.sortie_reelle_masse{{/tr}}</button>
        {{/me_form_field}}
      </form>
    </th>
  </tr>
  <tr>
    <th class="narrow">
      <input type="checkbox" name="check_all" onchange="selectAllSejours($V(this));"/>
    </th>
    <th>{{mb_label class=CSejour field=patient_id}}</th>
    <th>{{mb_label class=CSejour field=praticien_id}}</th>
    <th>{{tr}}CAffectation{{/tr}}</th>
    <th>{{mb_label class=CSejour field=entree_reelle}}</th>
    <th>{{mb_label class=CSejour field=sortie_prevue}}</th>
    <th>{{mb_label class=CSejour field=type}}</th>
    <th>{{mb_label class=CSejour field=mode_sortie}}</th>
  </tr>
  {{foreach from=$sejours item=_sejour}}
    {{assign var=patient value=$_sejour->_ref_patient}}
    <tr style="text-align: center;" class="me-text-align-left">
      <td>
        <input type="checkbox" name="box-{{$_sejour->_guid}}" onchange="jsonSejours['{{$_sejour->_id}}']._checked = (this.checked ? 1 : 0);showCheckSejours();"/>
        <script>
          var jsonSejour = { _checked : 0 };
          jsonSejours["{{$_sejour->_id}}"] = jsonSejour;
        </script>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')" {{if !$_sejour->entree_reelle }}class="patient-not-arrived"{{/if}}>
          {{$patient->_view}}
        </span>
      </td>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}</td>
      <td>{{$_sejour->_ref_last_affectation->_view}}</td>
      <td>{{mb_value object=$_sejour field=entree_reelle}}</td>
      <td>{{mb_value object=$_sejour field=sortie_prevue}}</td>
      <td>{{mb_value object=$_sejour field=type}}</td>
      <td>
        {{if $conf.dPplanningOp.CSejour.use_custom_mode_sortie}}
          {{$_sejour->_ref_mode_sortie->_view}}
        {{elseif $_sejour->mode_sortie}}
          {{mb_value object=$_sejour field=mode_sortie}}
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="8">{{tr}}CSejour.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>