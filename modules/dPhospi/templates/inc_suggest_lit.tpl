{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admissions script=admissions ajax=1}}

<table class="tbl" id="lits_dispos">
  <tr>
    <th class="title" colspan="6">
      <form name="searchLit" method="get" onsubmit="return onSubmitFormAjax(this, null, this.up('div'))">
        <input type="hidden" name="m" value="hospi" />
        <input type="hidden" name="a" value="ajax_suggest_lit" />
        <input type="hidden" name="_link_affectation" value="{{$_link_affectation}}" />
        <input type="hidden" name="affectation_id" value="{{$affectation_id}}" />
        <input type="hidden" name="datetime" value="{{$datetime}}" />
        <input type="hidden" name="services_ids_suggest" value="{{','|implode:$services_ids_suggest}}" />
        <button type="button" onclick="Placement.selectServices('cut', '{{','|implode:$services_ids_suggest}}');" class="search"
                style="float: left;">Services
        </button>
      </form>
    </th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th>
      Libre depuis
    </th>
    <th>{{tr}}CLit{{/tr}}</th>
    <th class="narrow">
      <input type="text" size="3" onkeyup="Admissions.filter(this, 'lits_dispos')" id="filter-patient-name" />
    </th>
    <th class="narrow">
      <label title="Sexe de l'autre patient dans la chambre">Sexe</label>
    </th>
    <th>Occupé après</th>
  </tr>

  {{foreach from=$lits item=_lit}}
    {{assign var=lit_id            value=$_lit->_id}}
    {{assign var=tmp_affectation   value=$_lit->_ref_last_dispo}}
    {{assign var=uf_hebergement_id value=$tmp_affectation->uf_hebergement_id}}
    {{assign var=uf_medicale_id    value=$tmp_affectation->uf_medicale_id}}
    {{assign var=uf_soins_id       value=$tmp_affectation->uf_soins_id}}

    {{if $_lit->_dispo_depuis == 0}}
      {{assign var=width_entree value=0}}
    {{else}}
      {{math equation="(x/y) * 100" x=$_lit->_dispo_depuis y=$max_entree assign=width_entree}}
    {{/if}}

    {{if $_lit->_occupe_dans == "libre"}}
      {{assign var=width_sortie value="100"}}
    {{else}}
      {{math equation="(x/y) * 100" x=$_lit->_occupe_dans y=$max_sortie assign=width_sortie}}
    {{/if}}
    <tbody class="hoverable">
    <tr>
      <td>
        <button type="button" class="tick notext"
                onclick="
                {{if $_link_affectation}}
                  submitLiaison('{{$lit_id}}', '{{$uf_hebergement_id}}', '{{$uf_medicale_id}}', '{{$uf_soins_id}}');
                {{else}}
                  moveAffectation('{{$affectation_id}}', '{{$lit_id}}');
                {{/if}}
                  Control.Modal.close();"></button>
      </td>
      <td style="width: 30%;">
        <div style="width: {{$width_entree}}%; background: #dcd;">
          {{if $_lit->_dispo_depuis_friendly}}
            {{$_lit->_dispo_depuis_friendly.locale}}
          {{else}}
            &mdash;
          {{/if}}
        </div>
      </td>
      <td colspan="2">
        <span class="CPatient-view">{{$_lit}}</span>
      </td>
      <td>
        {{if $_lit->_sexe_other_patient}}
          {{tr}}CPatient.sexe.{{$_lit->_sexe_other_patient}}{{/tr}}
        {{/if}}
      </td>
      <td style="width: 30%;">
        {{if $_lit->_occupe_dans_friendly}}
          {{$_lit->_occupe_dans_friendly.locale}}
        {{else}}
          &mdash;
        {{/if}}
      </td>
    </tr>
    </tbody>
  {{/foreach}}
</table>
