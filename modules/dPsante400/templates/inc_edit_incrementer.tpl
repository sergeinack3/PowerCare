{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var oForm = getForm("incrementer{{$incrementer->_guid}}");

    oForm.range_min.addSpinner({min: 0});
    oForm.range_max.addSpinner({min: 0});
  });
</script>

<form name="incrementer{{$incrementer->_guid}}" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="{{$m}}"/>
  <input type="hidden" name="dosql" value="do_incrementer_aed"/>
  <input type="hidden" name="incrementer_id" value="{{$incrementer->_id}}"/>
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="last_update" value="now"/>
  <input type="hidden" name="_reset" value="0"/>

  <input type="hidden" name="callback" value="Domain.bindIncrementerDomain.curry({{$domain_id}})"/>

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$incrementer}}

    <tr>
      <th>{{mb_label object=$incrementer field="_object_class"}}</th>
      <td>{{mb_field object=$incrementer field="_object_class" readonly=true typeEnum="radio"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$incrementer field="pattern"}}
      <td>{{mb_field object=$incrementer field="pattern"}}
        <i class="me-icon help me-primary"
           onmouseover="ObjectTooltip.createDOM(this, 'pattern-incrementer-legend', {duration: 0});"></i>
      {{mb_include module=dPsante400 template=inc_vw_pattern_incrementer_legend}}</th></td>
    </tr>

    <tr>
      <th></th>
      <td>{{mb_include module=dPsante400 template=inc_object_vars}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$incrementer field="_view"}}</th>
      <td>{{$incrementer->_view}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$incrementer field="value"}}</th>
      <td>
        {{if !$incrementer->_id}}
          {{mb_field object=$incrementer field="value" value="1"}}
        {{else}}
          {{mb_value object=$incrementer field="value"}}
        {{/if}}
      </td>
    </tr>

    <tr>
      <th colspan="2" class="section">Plage de numérotation pour les systèmes tiers</th>
    </tr>

    <tr>
      <td colspan="2">
        <div class="small-warning">
          Il est nécessaire de définir une borne min et max pour gérer une plage.
        </div>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$incrementer field="manage_range"}}</th>
      <td>{{mb_field object=$incrementer field="manage_range"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$incrementer field="range_min"}}</th>
      <td>{{mb_field object=$incrementer field="range_min" size="10px"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$incrementer field="range_max"}}</th>
      <td>{{mb_field object=$incrementer field="range_max" size="10px"}}</td>
    </tr>

    <tr>
      <th colspan="2" class="section">Réinitialisation</th>
    </tr>
    <tr>
      <td colspan="2">
        <div class="small-info">
          La <em>valeur variable</em> doit être modifiée pour remettre à zéro le compteur
        </div>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$incrementer field="extra_data"}}</th>
      <td>{{mb_field object=$incrementer field="extra_data"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$incrementer field="reset_value"}}</th>
      <td>
        {{mb_field object=$incrementer field="reset_value" size="10"}}

        {{if $incrementer->_id}}
          <button class="erase" type="button" onclick="$V(this.form._reset, 1); this.form.onsubmit(); $V(this.form._reset, 0); "
                  {{if $incrementer->reset_value == ""}}disabled{{/if}}>{{tr}}Reset{{/tr}}</button>
        {{/if}}
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $incrementer->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
