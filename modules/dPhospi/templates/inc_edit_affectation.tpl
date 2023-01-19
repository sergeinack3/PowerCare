{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm('editAffect');
    var div_content = form.up('div.content');
    {{if !$IS_MEDIBOARD_EXT_DARK}}
     div_content.setStyle({overflow: 'visible'});
    {{/if}}
    div_content.up('div.modal').setStyle({overflow: 'visible'});

    {{if $affectation->_id && $affectation->sejour_id}}
    var url = new Url("hospi", "ajax_lit_autocomplete");
    url.addParam('group_id', '{{$affectation->_ref_sejour->group_id}}');
    url.autoComplete(form.keywords, null, {
      minChars: 2,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function (field, selected) {
        var value = selected.id.split('-')[2];
        $V(form.lit_id, value);
      }
    });
    {{/if}}
  });
</script>

<form name="editAffect" method="post"
      onsubmit="return onSubmitFormAjax(this, (function() {
        Control.Modal.close();
        if (window.refreshMouvements) {
        if ((this._lock_all_lits && this._lock_all_lits.checked)) {
        refreshMouvements(window.loadNonPlaces);
        }
        else {
        var lit_id = $V(this.lit_id);
        if (lit_id && lit_id != '{{$affectation->lit_id}}') {
        refreshMouvements(null, lit_id);
        }
        refreshMouvements(window.loadNonPlaces, '{{$affectation->lit_id}}');
        }
        }
        }).bind(this));">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="dosql" value="do_affectation_aed" />
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$affectation}}
  {{mb_field object=$affectation field=lit_id      hidden=true}}
  {{mb_field object=$affectation field=function_id hidden=true}}

  <table class="form">
    <tr>
      {{if $affectation->_id}}
        <th class="title" colspan="4">
          {{if $affectation->sejour_id}}
            {{$affectation->_ref_sejour->_ref_patient}}
          {{else}}
            Lit bloqué
          {{/if}}
        </th>
      {{/if}}
    </tr>
    {{if !$affectation->_id}}
      {{if !$urgence || !$mod_urgence}}
        <tr>
          <th>
            <input type="checkbox" name="_lock_all_lits" value="1" onchange="{{if $urgence}}$V(this.form._lock_all_lits_urgences, this.checked ? 1 : 0);{{/if}}"/>
            <input type="hidden" name="_lock_all_lits_urgences" value="0" />
          </th>
          <td colspan="3">Bloquer tous les lits du service {{$lit->_ref_chambre->_ref_service->nom}}</td>
        </tr>
      {{/if}}
    {{/if}}
    <tr>
      <th>
        {{mb_label object=$affectation field=entree}}
      </th>
      <td>
        {{mb_field object=$affectation field=entree form=editAffect register=true}}
      </td>
      <th>
        {{mb_label object=$affectation field=sortie}}
      </th>
      <td>
        {{mb_field object=$affectation field=sortie form=editAffect register=true}}
      </td>
    </tr>

    {{if $affectation->_id && $affectation->sejour_id}}
      <tr>
        <th>
          {{mb_label object=$affectation field=lit_id}}
        </th>
        <td colspan="3">
          <input type="text" name="keywords" value="{{$lit}}" />
        </td>
      </tr>
    {{/if}}

    {{if !$affectation->sejour_id}}
      <tr>
        <th>
          {{mb_label object=$affectation field=rques}}
        </th>
        <td colspan="3">
          {{mb_field object=$affectation field=rques form=editAffect}}
        </td>
      </tr>
    {{/if}}

    <tr>
      <td colspan="4" class="button">
        <button type="button" class="save me-primary" onclick="this.form.onsubmit();">
          {{if $affectation->_id}}
            {{tr}}Save{{/tr}}
          {{else}}
            {{tr}}Create{{/tr}}
          {{/if}}
          {{if $affectation->_id}}
            <button type="button" class="cancel" onclick="$V(this.form.del, 1); this.form.onsubmit();">{{tr}}Delete{{/tr}}</button>
          {{/if}}
      </td>
    </tr>
  </table>
</form>
{{if $affectation->_id && $affectation->sejour_id}}
  {{mb_include module=hospi template=inc_other_actions}}
{{/if}}
