{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  selectAllVisites = function(valeur, type_visite){
    $('type_visite-'+type_visite).select('input[type=checkbox]').each(function(e){
      if (e.name.indexOf('visite-') >= 0 && !e.disabled) {
        $V(e, valeur);
      }
    });
  };

  showCheckVisites = function(type_visite) {
    var checked   = 0;
    var count     = 0;
    var tbody_visites_sejour = $('type_visite-'+type_visite);
    tbody_visites_sejour.select('input,checkbox').each(function(e){
      if (e.name.indexOf('visite-') >= 0) {
        count++;
        if ($V(e)) { checked ++; }
      }
    });

    var check_all = tbody_visites_sejour.down('input[name=check_all]');
    check_all.checked = '';
    check_all.style.opacity = '1';

    if (checked) {
      check_all.checked = '1';
      if (checked < count) {
        check_all.style.opacity = '0.5';
      }
    }
  };

  validationVisites = function() {
    var form = getForm("valideVisites");
    $V(form.sejours_ids, Object.toJSON(jsonVisites));
    $V(form.sejours_effectue_ids, Object.toJSON(jsonVisitesEffectues));

    return onSubmitFormAjax(form, function() {Control.Modal.close();});
  };

  jsonVisites = {};
  jsonVisitesEffectues = {};
</script>

<form name="valideVisites" action="?" method="post">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="dosql" value="do_visites_aed" />
  <input type="hidden" name="sejours_ids" value="" />
  <input type="hidden" name="sejours_effectue_ids" value="" />
</form>

<table class="form" id="tbody_visites_sejour">
  <tr>
    <th colspan="3" class="title">Validation des visites du {{$dnow|date_format:$conf.date}}</th>
  </tr>
  {{foreach from=$sejours_by_type item=sejours key=type_visite}}
    <tbody id="type_visite-{{$type_visite}}">
      <tr>
        <th class="category" colspan="3">{{if $type_visite == "a_visiter"}}A éffectuer{{else}}Déjà effectuée(s){{/if}} ({{$sejours|@count}})</th>
      </tr>
      <tr>
        <th class="section narrow">
          {{if $sejours|@count}}
            <input type="checkbox" name="check_all" onchange="selectAllVisites($V(this), '{{$type_visite}}');" {{if $type_visite == "a_visiter"}}checked="checked"{{/if}}/>
          {{/if}}
        </th>
        <th class="section">{{tr}}CPatient{{/tr}}</th>
        <th class="section">{{tr}}CSejour{{/tr}}</th>
      </tr>
      {{foreach from=$sejours item=_sejour}}
        <tr>
          <td>
            <script>
              Main.add(function() {
                {{if $type_visite == "a_visiter"}}
                  jsonVisites["{{$_sejour->_id}}"] = { _checked : "1" };
                {{else}}
                  jsonVisitesEffectues["{{$_sejour->_id}}"] = { _checked : "0" };
                {{/if}}
              });
            </script>
            <input type="checkbox" name="visite-{{$_sejour->_guid}}" value="{{if $type_visite == "a_visiter"}}1{{else}}0{{/if}}" {{if $type_visite == "a_visiter"}}checked="checked"{{/if}}
                   onchange="{{if $type_visite == "a_visiter"}}jsonVisites{{else}}jsonVisitesEffectues{{/if}}['{{$_sejour->_id}}']._checked = (this.checked ? 1 : 0);showCheckVisites('{{$type_visite}}');"/>
          </td>
          <td>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_ref_patient->_guid}}')">
              {{$_sejour->_ref_patient->_view}}
            </span>
          </td>
          <td>
            <span class="{{if !$_sejour->entree_reelle}}patient-not-arrived{{/if}} {{if $_sejour->septique}}septique{{/if}}" onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
              {{$_sejour->_shortview}}
            </span>
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td colspan="3" class="empty">Aucune visite {{if $type_visite == "a_visiter"}}à valider{{else}}déjà effectuée{{/if}}</td>
        </tr>
      {{/foreach}}
      <tr>
        <td class="button" colspan="3">
          {{if $sejours|@count}}
            <button type="button" class="tick" onclick="validationVisites();">{{if $type_visite == "a_visiter"}}Valider{{else}}Revalider{{/if}} les visites</button>
          {{/if}}
        </td>
      </tr>
    </tbody>
  {{/foreach}}
</table>