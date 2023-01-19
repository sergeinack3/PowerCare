{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=demandes_radio_bio value="dPurgences Display demandes_radio_bio"|gconf}}
{{assign var=see_type_radio value="dPurgences Display see_type_radio"|gconf}}
{{assign var=imagerie_etendue value="dPurgences CRPU imagerie_etendue"|gconf}}

{{assign var=types_attentes value="bio,specialiste"}}

{{if !$imagerie_etendue}}
  {{assign var=types_attentes value="radio,$types_attentes"}}
{{/if}}

{{assign var=types_attentes value=","|explode:$types_attentes}}

<div id="attente">
  <script>
    Horodatage = {
      onSubmit:  function(form) {
        if ($V(form.type_attente) == "radio" && !$V(form.attente_id) && !$V(form.demande) && !$V(form.depart)) {
          return;
        }
        return onSubmitFormAjax(form, Horodatage.reload.curry(form));
      },
      reload: function(form) {
        new Url("urgences", "ajax_vw_attente")
          .addParam("rpu_id", $V(form.rpu_id))
          .requestUpdate('attente');
      }
    }
  </script>
  <table class="main me-no-align me-no-box-shadow">
    {{foreach from=$types_attentes item=type_attente}}
      {{assign var=colspan value=2}}
      {{if $demandes_radio_bio && in_array($type_attente, array("radio", "bio"))}}
        {{assign var=colspan value=3}}
      {{/if}}
      <td style="width:{{if !$imagerie_etendue}}32{{else}}49{{/if}}%; vertical-align: top;">
        <table class="tbl me-margin-top-2">
          <tr>
            <th class="section" colspan="{{$colspan}}">
              <form name="newAttente{{$type_attente}}" method="post" onsubmit="return Horodatage.onSubmit(this);">
                {{mb_class object=$rpu->_ref_attente_empty}}
                {{mb_key object=$rpu->_ref_attente_empty}}
                {{mb_field object=$rpu->_ref_attente_empty field=rpu_id value=$rpu->_id hidden=true}}
                {{mb_field object=$rpu->_ref_attente_empty field=type_attente value=$type_attente hidden=true}}

                {{assign var=field_new_demande value=depart}}
                {{if $demandes_radio_bio && in_array($type_attente, array("radio", "bio"))}}
                  {{assign var=field_new_demande value=demande}}
                {{/if}}

                {{mb_field object=$rpu->_ref_attente_empty field=$field_new_demande hidden=1}}

                <button type="button" class="new notext compact me-tertiary me-margin-right-4" style="float: left;"
                        onclick="$V(this.form.{{$field_new_demande}}, 'current'); this.form.onsubmit();">{{tr}}CRPUAttente.new{{/tr}}</button>

              </form>

              {{tr}}CRPUAttente.type_attente.{{$type_attente}}{{/tr}}
            </th>
          </tr>
          <tr>
            {{if in_array($type_attente, array("radio", "bio"))}}
              {{if $demandes_radio_bio}}
                <th style="width: 33%;">
                  Demande
                </th>
              {{/if}}
              <th style="width: {{if $demandes_radio_bio}}33{{else}}50{{/if}}%;">
                Départ
              </th>
              <th>
                Retour
              </th>
            {{else}}
              <th class="halfPane">
                Attente
              </th>
              <th>
                Arrivée
              </th>
            {{/if}}
          </tr>
          {{foreach from=$rpu->_ref_attentes_by_type.$type_attente item=attente}}
            <tr>
              {{assign var=show_type_radio value=1}}
              {{if $demandes_radio_bio && in_array($type_attente, array("radio", "bio"))}}
              <td class="button">
                {{mb_include module=urgences template=inc_form_attente field=demande}}
              </td>
                {{assign var=show_type_radio value=0}}
              {{/if}}
              <td class="button">
                {{mb_include module=urgences template=inc_form_attente field=depart}}
              </td>
              <td class="button">
                {{mb_include module=urgences template=inc_form_attente field=retour}}
              </td>
            </tr>
          {{foreachelse}}
          <tr>
            <td colspan="{{$colspan}}" class="empty">
              {{tr}}CRPUAttente.none{{/tr}}
            </td>
          </tr>
          {{/foreach}}
        </table>
      </td>
    {{/foreach}}
  </table>
</div>