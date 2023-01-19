{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planification ajax=true}}
{{assign var=readonly value=false}}
{{if $plage->_id && $plage->_ref_sejours_affectes|@count}}
  {{assign var=readonly value=true}}
{{/if}}

<script>
  Main.add(function () {
    {{if !$readonly}}
      TrameCollective.autocompleteElementPrescription(getForm('Edit-{{$plage->_guid}}'));
    {{/if}}
  });
</script>

{{if $readonly}}
  <div class="small-warning">{{tr}}CPlageSeanceCollective-readonly{{/tr}}</div>
{{/if}}

<form name="Edit-{{$plage->_guid}}" action="?m={{$m}}" method="post" onsubmit="return TrameCollective.onsubmit(this);">
  {{if !$plage->_id}}
    <input type="hidden" name="m" value="{{$m}}"/>
    <input type="hidden" name="dosql" value="do_create_multi_plage_aed"/>
    <input type="hidden" name="days_week" value=""/>
  {{/if}}
  <input type="hidden" name="_readonly" value="{{$readonly}}" />
  <input type="hidden" name="del" value="0" />
  {{mb_key   object=$plage}}
  {{mb_class object=$plage}}
  {{mb_field object=$plage field=element_prescription_id hidden=true}}
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$plage}}
    <tr>
      <th>{{mb_label object=$plage field=trame_id}}</th>
      <td>{{mb_field object=$plage field=trame_id options=$trames emptyLabel="CTrameSeanceCollective.select"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage field=nom}}</th>
      <td>{{mb_field object=$plage field=nom}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage field=user_id}}</th>
      <td>
        {{if $kines_cdarr|@count}}
          {{mb_field object=$plage field=user_id options=$kines_cdarr emptyLabel="CMediusers.select"}}
        {{else}}
          <select name="user_id">
            <option value="">&mdash; {{tr}}CMediusers.select{{/tr}}</option>
          </select>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage field=therapeute2_id}}</th>
      <td>
        {{if $kines|@count}}
          {{mb_field object=$plage field=therapeute2_id options=$kines emptyLabel="CMediusers.select"}}
        {{else}}
          <select name="therapeute2_id">
            <option value="">&mdash; {{tr}}CMediusers.select{{/tr}}</option>
          </select>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage field=therapeute3_id}}</th>
      <td>
        {{if $kines|@count}}
          {{mb_field object=$plage field=therapeute3_id options=$kines emptyLabel="CMediusers.select"}}
        {{else}}
          <select name="therapeute3_id">
            <option value="">&mdash; {{tr}}CMediusers.select{{/tr}}</option>
          </select>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage field=niveau}}</th>
      <td>
        {{if $readonly}}
          {{mb_value object=$plage field=niveau}}
        {{else}}
          {{mb_field object=$plage field=niveau}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage field=element_prescription_id}}</th>
      <td>
        {{if $readonly}}
          {{$plage->_ref_element_prescription->_view}}
        {{else}}
          <input type="text" name="libelle" placeholder="&mdash; {{tr}}CPrescription.select_element{{/tr}}" class="autocomplete"
                 value="{{if $plage->element_prescription_id}}{{$plage->_ref_element_prescription}}{{/if}}"/>
          <div style="display:none;" class="autocomplete" id="element_prescription_id_autocomplete"></div>
        {{/if}}
      </td>
    </tr>
    <tbody id="actes_plage">
      {{if !$readonly && $plage->_id}}
        {{mb_include module=ssr template=vw_codage_actes_plage}}
      {{else}}
        <!-- Affichage des actes paramétrés -->
        {{assign var=categories_actes value='Ox\Mediboard\Ssr\CPlageSeanceCollective'|static:categories_actes}}
        {{foreach from=$categories_actes key=name_cat item=_type_acte}}
          {{if $plage->$_type_acte|@count}}
            <tr>
              <th>{{tr}}{{$name_cat}}{{/tr}}</th>
              <td>
                <ul>
                  {{foreach from=$plage->$_type_acte item=_acte}}
                    <li>{{$_acte->code}} (x {{$_acte->quantite}}) : {{$_acte->_view}}</li>
                  {{/foreach}}
                </ul>
              </td>
            </tr>
          {{/if}}
        {{/foreach}}
      {{/if}}
    </tbody>
    <tr>
      <th>{{mb_label object=$plage field=equipement_id}}</th>
      <td>
        {{if !$readonly}}
          <select name="equipement_id">
            <option value="">&mdash; {{tr}}CEquipement.select{{/tr}}</option>
            {{foreach from=$plateaux item=_plateau}}
              <optgroup label="{{$_plateau->_view}}">
                {{foreach from=$_plateau->_ref_equipements item=_equipement}}
                  <option value="{{$_equipement->_id}}" {{if $plage->equipement_id == $_equipement->_id}}selected="selected"{{/if}}>
                    {{$_equipement->_view}}
                  </option>
                {{/foreach}}
              </optgroup>
            {{/foreach}}
          </select>
        {{elseif $plage->equipement_id}}
          {{$plage->_ref_equipement->_view}}
        {{else}}
          &ndash;
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage field=day_week}}</th>
      <td>
        {{if $readonly}}
          {{mb_value object=$plage field=day_week}}
        {{elseif $plage->_id}}
          {{mb_field object=$plage field=day_week}}
        {{else}}
          <select name="_days_week" class="str notNull" multiple="1" size="7" onchange="TrameCollective.selectMultiDays(this.form);">
            <option value="monday">{{tr}}Monday{{/tr}}</option>
            <option value="tuesday">{{tr}}Tuesday{{/tr}}</option>
            <option value="wednesday">{{tr}}Wednesday{{/tr}}</option>
            <option value="thursday">{{tr}}Thursday{{/tr}}</option>
            <option value="friday">{{tr}}Friday{{/tr}}</option>
            <option value="saturday">{{tr}}Saturday{{/tr}}</option>
            <option value="sunday">{{tr}}Sunday{{/tr}}</option>
          </select>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage field=debut}}</th>
      <td>
        {{if $readonly}}
          {{mb_value object=$plage field=debut}}
        {{else}}
          {{mb_field object=$plage field=debut form="Edit-`$plage->_guid`"}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage field=duree}}</th>
      <td>
        {{if $readonly}}
          {{mb_value object=$plage field=duree}}
        {{else}}
          {{mb_field object=$plage field=duree increment=1 size=2 step=10 form="Edit-`$plage->_guid`"}}
        {{/if}}
        {{tr}}common-minute|pl{{/tr}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage field=commentaire}}</th>
      <td>{{mb_field object=$plage field=commentaire}}</td>
    </tr>
    {{if $plage->_id}}
      <tr>
        <th>{{mb_label object=$plage field=active}}</th>
        <td>
          {{mb_value object=$plage field=active}}
          {{if $plage->_inactivable}}
            {{mb_field object=$plage field=active hidden=true}}
          {{/if}}
        </td>
      </tr>
    {{/if}}
    <tr>
      <td class="button" colspan="2">
        {{if $plage->_id}}
          <button class="modify me-primary" type="button" onclick="TrameCollective.confirmChangePlage(this.form);">
            {{tr}}Save{{/tr}}
          </button>
          <button class="trash" type="button" onclick="TrameCollective.confirmDeletion(this.form);">
            {{tr}}Delete{{/tr}}
          </button>
          <button class="{{if $plage->active}}cancel{{else}}tick{{/if}}" type="button"
              {{if $plage->_inactivable && $plage->active}}
                onclick="TrameCollective.confirmInactivation(this.form);"
              {{elseif !$plage->active}}
                onclick="TrameCollective.confirmActivation(this.form);"
              {{else}}
                disabled="disabled" title="{{tr}}CPlageCollective.It cant be disabled{{/tr}}"
              {{/if}}>
            {{tr}}{{if $plage->active}}Disable{{else}}Enable{{/if}}{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
