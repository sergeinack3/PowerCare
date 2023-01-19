{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  savePref = function(form) {
    var formPref = getForm('editPrefSejour');
    var sejours_ids_admissions_elt = formPref.elements['pref[sejours_ids_admissions]'];

    var sejours_ids_admissions = $V(sejours_ids_admissions_elt).evalJSON();

    sejours_ids_admissions.g{{$group_id}} = $V(form.select("input[class=sejour]:checked"));

    if (sejours_ids_admissions.g{{$group_id}} == null) {
      sejours_ids_admissions.g{{$group_id}} = "";
    }
    else {
      sejours_ids_admissions.g{{$group_id}} = sejours_ids_admissions.g{{$group_id}}.join("|");
    }

    $V(sejours_ids_admissions_elt, Object.toJSON(sejours_ids_admissions));
    return onSubmitFormAjax(formPref, function() {
      form.submit();
      Control.Modal.close();
    });
  };

  checked_checkbox = false;
  toggleCheckedSejour = function() {
    var form = getForm("selectSejours");
    form.select("input[type=checkbox]").each(function(elt) {
      elt.checked = checked_checkbox;
    });
    checked_checkbox = !checked_checkbox;
  };

  Main.add(function() {
    Control.Modal.stack.last().position();
  });
</script>

<style>
  div#select_type_admission td.section {
    text-align: center;
    font-size: 0.9em;
    font-weight: normal;
    line-height: 100%;
    background-color: #BDCEFA;
    text-transform: uppercase;
  }

  div#select_type_admission {
    padding-top: -20px;
  }
</style>

<!-- Formulaire de sauvegarde des séjours en préférence utilisateur -->
<form name="editPrefSejour" method="post">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_preference_aed" />
  <input type="hidden" name="user_id" value="{{$app->user_id}}" />
  <input type="hidden" name="pref[sejours_ids_admissions]" value="{{$sejours_ids_admissions}}" />
</form>

<div id="select_type_admission" style="overflow-x: auto;">
  <form name="selectSejours" method="get">
    <input type="hidden" name="m" value="admissions" />
    {{if $view == "admissions"}}
      <input type="hidden" name="tab" value="vw_idx_admission" />
    {{elseif $view == "sorties"}}
      <input type="hidden" name="tab" value="vw_idx_sortie" />
    {{elseif $view == "preadmissions"}}
      <input type="hidden" name="tab" value="vw_idx_preadmission" />
    {{elseif $view == "presents"}}
      <input type="hidden" name="tab" value="vw_idx_present" />
    {{elseif $view == "accueil"}}
      <input type="hidden" name="tab" value="vw_accueil_patient" />
    {{elseif $view == "projet"}}
      <input type="hidden" name="tab" value="vw_projet_sortie" />
    {{/if}}
    <input type="hidden" name="sejours_ids[]" value="" />
    <table class="tbl me-no-box-shadow">
      <tr>
        <th colspan="2">
          <button type="button" style="float: left;" class="tick notext" onclick="toggleCheckedSejour()" title="{{tr}}common-msg-Check / uncheck all{{/tr}}"></button>
          {{tr}}admissions-Choice of type of admission{{/tr}}
        </th>
      </tr>
      {{foreach from=$list_type_admission key=key item=_admission}}
        <tr>
          <td style="vertical-align: top;" class="{{if $_admission == "comp" || $_admission == "ambu"}}secteur_ambucomp secteur_ambucompssr{{elseif $_admission == "ssr"}}secteur_ambucompssr{{elseif $_admission == "ambucomp" || $_admission == "ambucompssr"}}section{{/if}}">
            <label>
              <input type="checkbox" name="sejours_ids[{{$key}}]" value="{{$key}}" data-sejour_type_id="{{$key}}"
                     {{if $sejours_ids && in_array($key, $sejours_ids)}}checked{{/if}}
                     {{if $_admission == "ambucomp" || $_admission == "ambucompssr"}}onclick="$$('.secteur_{{$_admission}}').each(function(elt) { elt.down('input').checked=this.checked ? 'checked' : ''; }.bind(this));"{{/if}}
                     class="sejour" />
              <strong>{{tr}}CSejour._type_admission.{{$_admission}}{{/tr}}</strong>
            </label>
          </td>
        </tr>
      {{/foreach}}
      <tr>
        <td class="button" colspan="2">
            <button class="tick">{{tr}}Validate{{/tr}}</button>

            <button type="button" class="save" onclick="savePref(form);">
              {{tr}}Validate{{/tr}} {{tr}}and{{/tr}} {{tr}}Save{{/tr}}
            </button>

          <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>