{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=form value=hospitaliserPatiente}}

{{assign var=required_atnc value="dPplanningOp CSejour required_atnc"|gconf}}
{{assign var=required_uf_soins value="dPplanningOp CSejour required_uf_soins"|gconf}}

<tr>
  {{me_form_field nb_cells=2 mb_object=$sejour mb_field=praticien_id}}
    {{mb_field object=$sejour field=praticien_id hidden=true}}
    <input type="text" name="praticien_id_view" value="{{$sejour->_ref_praticien->_view}}" class="autocomplete" />

    <script>
      Main.add(function() {
        var form = getForm('{{$form}}');

        new Url('mediusers', 'ajax_users_autocomplete')
          .addParam('praticiens', '1')
          .addParam('input_field', 'praticien_id_view')
          .autoComplete(
            form.praticien_id_view, null,
            {
              minChars: 0,
              method: "get",
              select: "view",
              dropdown: true,
              afterUpdateElement: function(field, selected) {
                var id = selected.getAttribute("id").split("-")[2];
                $V(form.praticien_id, id);
              }
            }
          );
      });
    </script>
  {{/me_form_field}}
</tr>

<tr>
  {{me_form_field nb_cells=2 mb_object=$sejour mb_field=uf_soins_id}}
    <select name="uf_soins_id" class="ref {{if $required_uf_soins === "obl"}}notNull{{/if}}" style="width: 15em;">
      <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
      {{foreach from=$ufs.soins item=_uf}}
        <option value="{{$_uf->_id}}" {{if $sejour->uf_soins_id === $_uf->_id}}selected{{/if}}>
          {{mb_value object=$_uf field=libelle}}
        </option>
      {{/foreach}}
    </select>
  {{/me_form_field}}
</tr>

<tr>
  {{me_form_field nb_cells=2 mb_object=$sejour mb_field=mode_entree}}
    {{if $modes_entree|@count}}
      {{mb_field object=$sejour field=mode_entree hidden=true}}
      <select name="mode_entree_id" class="{{$sejour->_props.mode_entree_id}}" style="width: 15em;" onchange="updateModeEntree(this)">
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
        {{foreach from=$modes_entree item=_mode}}
          <option value="{{$_mode->_id}}" data-mode="{{$_mode->mode}}" data-provenance="{{$_mode->provenance}}"
                  {{if $sejour->mode_entree_id == $_mode->_id}}selected{{/if}}>
            {{$_mode}}
          </option>
        {{/foreach}}
      </select>
    {{else}}
      {{mb_field object=$sejour field=mode_entree emptyLabel="Choose"}}
    {{/if}}
  {{/me_form_field}}
</tr>

{{if $required_atnc}}
  <tr>
    {{me_form_field nb_cells=2 mb_object=$sejour mb_field=ATNC class="notNull"}}
      {{mb_field object=$sejour field="ATNC" class="notNull" typeEnum="select" emptyLabel="Non renseigné"}}
    {{/me_form_field}}
  </tr>
{{/if}}
