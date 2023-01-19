{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="addRDVExterne" action="?" method="post"
      onsubmit="return onSubmitFormAjax(this, function(){ Soins.showRDVExternal('{{$sejour_id}}');
        Control.Modal.close(); });">
  {{mb_class object=$rdv_externe}}
  {{mb_key object=$rdv_externe}}
  <input type="hidden" name="sejour_id" value="{{$sejour_id}}" />
  <input type="hidden" name="statut" value="{{$rdv_externe->statut}}" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$rdv_externe}}

    <tr>
      <th>{{mb_label object=$rdv_externe field="libelle"}}</th>
      <td>{{mb_field object=$rdv_externe field="libelle"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$rdv_externe field="description"}}</th>
      <td>{{mb_field object=$rdv_externe field="description"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$rdv_externe field="date_debut"}}</th>
      <td>{{mb_field object=$rdv_externe field="date_debut" form="addRDVExterne" register=true}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$rdv_externe field="duree"}}</th>
      <td>{{mb_field object=$rdv_externe field="duree" form="addRDVExterne" increment=true}} {{tr}}common-minute|pl{{/tr}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$rdv_externe field="commentaire"}}</th>
      <td>{{mb_field object=$rdv_externe field="commentaire"}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $rdv_externe->statut != "annule"}}
          {{if $rdv_externe->statut == "realise"}}
            <button class="cancel" onclick="$V(this.form.statut, 'encours');">{{tr}}CRDVExterne-action-Cancel the realization{{/tr}}</button>
          {{else}}
            <button class="tick" onclick="$V(this.form.statut, 'realise');">{{tr}}common-action-Realize{{/tr}}</button>
          {{/if}}
        {{/if}}
        {{if !$rdv_externe->_id}}
          <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
        {{else}}
          <button type="submit" class="edit">{{tr}}Edit{{/tr}}</button>
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form, {
                    ajax: true,
                    objName:'{{$rdv_externe->libelle|smarty:nodefaults|JSAttribute}}'
                    },
                    function() {
                    Soins.showRDVExternal('{{$sejour_id}}');
                    Control.Modal.close();
                    });">{{tr}}Delete{{/tr}}</button>
          {{if $rdv_externe->statut != "realise"}}
            {{if $rdv_externe->statut == "annule"}}
              <button class="tick" onclick="$V(this.form.statut, 'encours');">{{tr}}common-action-Validate{{/tr}}</button>
            {{else}}
              <button class="cancel" onclick="$V(this.form.statut, 'annule');">{{tr}}Cancel{{/tr}}</button>
            {{/if}}
          {{/if}}
        {{/if}}
      </td>
    </tr>
  </table>
</form>
