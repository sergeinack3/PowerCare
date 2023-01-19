{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  SiblingsChecker.running = false;
  SiblingsChecker.submit = 0;
  $V(SiblingsChecker.form._reason_state, "");
  $("submit-patient").disabled = false;
</script>

{{if !$similar}}
  <div class="small-warning">
    Le nom et/ou le prénom sont très différents de {{$old_patient->_view}}<br />
  </div>
{{/if}}

{{if $doubloon}}
  <div class="small-error">
    {{$doubloons|@count}}
    {{if $doubloons|@count > 1}}
      doublons ont été détectés :
    {{else}}
      doublon a été détecté :
    {{/if}}
    <form name="doubloonSelector">
      <ul>
        {{foreach from=$doubloons item=_doubloon}}
          <li>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_doubloon->_guid}}')">
                {{$_doubloon->nom}} {{if $_doubloon->nom_jeune_fille}}({{$_doubloon->nom_jeune_fille}}){{/if}} {{$_doubloon->prenom}}
              </span>
            né(e) le {{mb_value object=$_doubloon field="naissance"}}

            {{mb_include module=patients template=inc_vw_ipp ipp=$_doubloon->_IPP hide_empty=1}}
          </li>
        {{/foreach}}
      </ul>

      {{if $submit}}
        Voulez-vous tout de même sauvegarder ?
      {{/if}}
      <input type="hidden" name="_doubloon_ids" value="{{$doubloon}}">
    </form>
  </div>
{{/if}}

{{if $siblings}}
  <div class="small-warning">
    Risque de doublons :

    <form name="linkSelector">
      <ul>
        {{foreach from=$siblings item=_sibling}}
          <li>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sibling->_guid}}')">
            {{$_sibling->nom}} {{if $_sibling->nom_jeune_fille}}({{$_sibling->nom_jeune_fille}}){{/if}} {{$_sibling->prenom}}
          </span>
            né(e) le {{mb_value object=$_sibling field="naissance"}}

            {{if $submit}}
              <input type="radio" name="sibling_id" value="{{$_sibling->_id}}"{{if $siblings|@count == 1}} checked{{/if}}/>
            {{/if}}

            <br />
            {{if $_sibling->cp || $_sibling->ville || $_sibling->adresse}}
              <span class="compact" style="white-space: normal">
              <span style="white-space: nowrap">
                {{$_sibling->cp}} {{$_sibling->ville}}
              </span>
              <span style="white-space: nowrap">{{$_sibling->adresse|spancate:50}}</span>
            </span>
            {{/if}}
          </li>
        {{/foreach}}
      </ul>
    </form>
  </div>
{{/if}}

{{if $submit && $doubloon}}
  <label>Raison de la création du doublon :
    <textarea name="doubloon_reason" onchange="$V(SiblingsChecker.form._reason_state, this.value)"></textarea>
  </label>
{{/if}}

{{if $submit && ($doubloon || $siblings || !$similar)}}
  <div style="text-align: center">
    <button type="button" class="tick" onclick="SiblingsChecker.confirmCreate()">{{tr}}Confirm{{/tr}}</button>
    <button type="button" class="cancel" onclick="Control.Modal.close()">{{tr}}Cancel{{/tr}}</button>
    <button type="button" class="link" onclick="SiblingsChecker.link = 1; SiblingsChecker.confirmCreate();">
      {{tr}}Link{{/tr}}
    </button>
  </div>
{{/if}}
