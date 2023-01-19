{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(Tdb.views.filterByText.curry('accouchements_tab'));
</script>

<table class="tbl me-no-align" id="accouchements_tab">
    <tbody id="tbody_accouchements_tab">
    {{foreach from=$ops item=_op}}
        {{assign var=_grossesse value=$_op->_ref_sejour->_ref_grossesse}}
        <tr {{if $_grossesse->datetime_accouchement}}class="opacity-50"{{/if}}>
            <td class="text">
          <span class="CPatient-view"
                onmouseover="ObjectTooltip.createEx(this, '{{$_grossesse->_ref_parturiente->_guid}}');">
            {{$_grossesse->_ref_parturiente}}
          </span>

                {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_grossesse->_ref_parturiente}}

                <br/>

                <span class="compact"
                      onmouseover="ObjectTooltip.createEx(this, '{{$_op->_ref_sejour->_ref_curr_affectation->_guid}}');">{{$_op->_ref_sejour->_ref_curr_affectation}}</span>
            </td>
            <td class="text">
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_op->_ref_chir}}<br/>
                <select name="anesth_id" onchange="Tdb.changeAnesthFor('{{$_op->_id}}', $V(this));" style="width:12em;">
                    <option value="">&mdash;</option>
                    {{foreach from=$anesths item=_anesth}}
                        <option value="{{$_anesth->_id}}"
                                {{if $_op->anesth_id == $_anesth->_id}}selected="selected" {{/if}}>{{$_anesth}}</option>
                    {{/foreach}}
                </select>
            </td>
            <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_guid}}');">
            {{if $_op->date != $date}}{{mb_value object=$_op field=date}}<br/>{{/if}}
              {{$_op->_datetime|date_format:$conf.time}}
          </span>
                {{if $_grossesse->_ref_dossier_perinat->niveau_alerte_cesar}}
                    <div
                            {{if $_grossesse->_ref_dossier_perinat->niveau_alerte_cesar == 1}}
                                class="small-info" style="background-color: lightgreen"
                            {{elseif $_grossesse->_ref_dossier_perinat->niveau_alerte_cesar == 2}}
                                class="small-warning"
                            {{elseif $_grossesse->_ref_dossier_perinat->niveau_alerte_cesar == 3}}
                                class="small-error"
                            {{/if}}
                    ></div>
                {{/if}}
            </td>
            <td class="text">
                {{if $_op->_ref_salle->_id && !$_op->_ref_salle->_id|@in_array:$salles }}
                    <span
                      onmouseover="ObjectTooltip.createEx(this, '{{$_op->_ref_salle->_guid}}');">{{$_op->_ref_salle}}</span>
                {{else}}
                    <select name="salle_id" style="width:13em;"
                            onchange="Tdb.changeSalleFor('{{$_op->_id}}', $V(this));">
                        <option value="">&mdash;</option>
                        {{foreach from=$blocs item=_bloc}}
                            <optgroup label="{{$_bloc}}">
                                {{foreach from=$_bloc->_ref_salles item=_salle}}
                                    <option value="{{$_salle->_id}}"
                                            {{if $_op->_ref_salle->_id == $_salle->_id}}selected="selected" {{/if}}>{{$_salle->nom}}</option>
                                {{/foreach}}
                            </optgroup>
                        {{/foreach}}
                    </select>
                {{/if}}
            </td>
            <td>
                {{if $_grossesse->datetime_debut_travail}}
                    Deb. {{mb_value object=$_grossesse field=datetime_debut_travail}}
                {{/if}}
                {{if $_grossesse->datetime_accouchement}}
                    <br/>
                    Fin {{mb_value object=$_grossesse field=datetime_accouchement}}
                {{/if}}
            </td>
            <td class="text compact">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_guid}}');">
          {{if $_op->libelle}}
              <em>[{{$_op->libelle}}]</em>
              <br/>
          {{/if}}
              {{foreach from=$_op->_ext_codes_ccam item=_code}}
                  <strong>{{$_code->code}}</strong>

: {{$_code->libelleLong}}

                  <br/>
              {{/foreach}}
          </span>
            </td>
            <td>
                <button class="edit notext" onclick="Tdb.editAccouchement('{{$_op->_id}}');"></button>
                <button onclick="Tdb.dossierAccouchement('{{$_op->_id}}');">Acc</button>
            </td>
        </tr>
        {{foreachelse}}
        <tr>
            <td colspan="8" class="empty">{{tr}}COperation.none{{/tr}}</td>
        </tr>
    {{/foreach}}
    </tbody>
    <thead>
    <tr>
        <th class="title me-text-align-center" colspan="10">
            <button type="button" class="accouchement_create notext" onclick="Tdb.editAccouchement(null);"
                    style="float: left;">
                {{tr}}CNaissance-action-Create delivery{{/tr}}
            </button>
            <label style="float: left;">
                <input type="checkbox" name="see_finished" value="1" {{if $see_finished}}checked{{/if}}
                       onchange="Tdb.views.toggleFinished();"/>
                {{tr}}CNaissance-action-Show finished{{/tr}}
            </label>
            <button type="button" class="change notext me-tertiary" onclick="Tdb.views.listAccouchements();"
                    style="float: right;">
                {{tr}}Refresh{{/tr}}
            </button>
            <button type="button" class="edit me-tertiary" onclick="Tdb.checklistsOpenSalle('{{$date}}');"
                    style="float: right;"
                    title="{{tr}}Checklist-mater-ouverture_salle-long{{/tr}}">
                {{tr}}Checklist-mater-ouverture_salle{{/tr}}
            </button>
            <a onclick="zoomViewport(this);">
                {{tr var1=$ops|@count var2=$date|date_format:$conf.date}}CNaissance-%s deliveries in progress on %s{{/tr}}
            </a>
        </th>
    </tr>
    <tr>
        <th>{{tr}}CPatient{{/tr}}e</th>
        <th>{{tr}}CSejour-title-praticien_anesth{{/tr}}</th>
        <th class="narrow">{{mb_title class=COperation field=_datetime}}</th>
        <th class="narrow">{{tr}}CSalle{{/tr}}</th>
        <th>{{tr}}CDossierPerinat-Travail{{/tr}}</th>
        <th>{{tr}}COperation-libelle{{/tr}}</th>
        <th class="narrow"></th>
    </tr>
    </thead>
</table>
