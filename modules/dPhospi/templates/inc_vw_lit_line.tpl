{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $_lit && $_lit->_id}}
  <!--Enregistrement automatique du formulaire lors de la saisie -->
  <tr id="line_lit-{{$_lit->_guid}}">
    <td class="narrow" style="width: 5%">
      <form name="editLitRankFilter{{$_lit->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Infrastructure.reloadLitLine('{{$_lit->_id}}', '{{$chambre->_id}}')}})">
        {{mb_key object=$_lit}}
        {{mb_class object=$_lit}}
        {{mb_field object=$_lit field=chambre_id  value=$chambre->_id hidden=true}}
        {{mb_field object=$_lit field=code  value=$_lit->nom hidden=true}}
        {{mb_field object=$_lit field=rank onchange="this.form.onsubmit()" increment=true form="editLitRankFilter`$_lit->_guid`"}}
      </form>
    </td>
    <td class="text" style="width: 10%">
      <form name="editLitNom{{$_lit->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Infrastructure.reloadLitLine('{{$_lit->_id}}', '{{$chambre->_id}}')}})">
        {{mb_key object=$_lit}}
        {{mb_class object=$_lit}}
        <input type="hidden" name="chambre_id" value="{{$chambre->_id}}" />
        {{mb_field object=$_lit field=nom  onchange="this.form.onsubmit()" size=10}}
      </form>
    </td>
    <td class="text" style="width: 10%">
      <form name="editLitNom_complet{{$_lit->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Infrastructure.reloadLitLine('{{$_lit->_id}}', '{{$chambre->_id}}')}})">
        {{mb_key object=$_lit}}
        {{mb_class object=$_lit}}
        <input type="hidden" name="chambre_id" value="{{$chambre->_id}}" />
        {{mb_field object=$_lit field=nom_complet  onchange="this.form.onsubmit()"}}
      </form>
    </td>
    <td {{if $_lit->annule}}class="cancelled"{{/if}}>
      <form name="editLitAnnule{{$_lit->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Infrastructure.reloadLitLine('{{$_lit->_id}}', '{{$chambre->_id}}')}})">
        {{mb_key object=$_lit}}
        {{mb_class object=$_lit}}
        <input type="hidden" name="chambre_id" value="{{$chambre->_id}}" />
        {{mb_field object=$_lit field=annule  onchange="this.form.onsubmit()"}}
      </form>
    </td>

    {{if "atih"|module_active }}
      <td>
        <form name="editLitIdentifie{{$_lit->_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Infrastructure.reloadLitLine('{{$_lit->_id}}', '{{$chambre->_id}}')}})">
          {{mb_key object=$_lit}}
          {{mb_class object=$_lit}}
          <input type="hidden" name="chambre_id" value="{{$chambre->_id}}" />
          {{mb_field object=$_lit field=identifie  onchange="this.form.onsubmit()"}}
        </form>
      </td>
    {{/if}}

    <td class="text" id="edit_liaisons_items-{{$_lit->_id}}" style="width: 30%">
      {{if $_lit->_id}}
        <script type="text/javascript">
          Main.add(function () {
            Infrastructure.editLitLiaisonItem('{{$_lit->_id}}');
          });
        </script>
      {{/if}}
    </td>
    <td>
      {{mb_include module=system template=inc_object_notes      object=$_lit}}
      {{mb_include module=system template=inc_object_idsante400 object=$_lit}}
      {{mb_include module=system template=inc_object_history    object=$_lit}}
      {{mb_include module=system template=inc_object_uf         object=$_lit }}
      {{mb_include module=system template=inc_object_idex       object=$_lit tag=$tag_lit}}
    </td>

    <td class="button">
      <form name="editLit{{$_lit->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Infrastructure.reloadLitLine('{{$_lit->_id}}', '{{$chambre->_id}}')}})">
        {{mb_key object=$_lit}}
        {{mb_class object=$_lit}}
        <input type="hidden" name="chambre_id" value="{{$chambre->_id}}" />
        <input type="hidden" name="nom" value="{{$_lit->nom}}" />
        <input type="hidden" name="del" value />
        <button class="trash notext" type="button" onclick="Infrastructure.confirmDeletionLit(this.form)"></button>
      </form>
    </td>
  </tr>
{{else}}
  <!--Enregistrement manuel du formulaire -->
  <tr id="line_lit-{{$_lit->_guid}}">
    <td class="narrow" style="width: 5%">
      <form name="saveRankLit{{$_lit->_guid}}" method="post" onsubmit="getForm('editLit{{$_lit->_guid}}').onsubmit(); return false;">

        <label><input id="input_rank" type="number" size="3" onchange="Infrastructure.setValueForm('editLit', 'rank', this.value);"
                      style="width: 30px" /></label>
        <script>
          Main.add(function () {
            $('input_rank').addSpinner({min: 0});
          });
        </script>
        <button type="submit" style="display:none;"></button>
      </form>
    </td>
    <td class="text" style="width: 10%">
      <form name="saveNomLit{{$_lit->_guid}}" method="post" onsubmit="getForm('editLit{{$_lit->_guid}}').onsubmit(); return false;">
        <label><input type="text" size="10" id="nom"
                      onchange="Infrastructure.setValueForm('editLit{{$_lit->_guid}}', 'nom', this.value); Infrastructure.setValueForm('editLit{{$_lit->_guid}}', 'code', this.value);" /></label>
        <button type="submit" style="display:none;"></button>
      </form>
    </td>
    <td class="text" style="width: 10%">
      <form name="saveNomCompletLit{{$_lit->_guid}}" method="post"
            onsubmit="getForm('editLit{{$_lit->_guid}}').onsubmit(); return false;">
        <label><input type="text" size="25"
                      onchange="Infrastructure.setValueForm('editLit{{$_lit->_guid}}', 'nom_complet', this.value);" /></label>
        <button type="submit" style="display:none;"></button>
      </form>
    </td>
    <td>
      <form name="saveAnnuleLit{{$_lit->_guid}}" method="post" onsubmit="getForm('editLit{{$_lit->_guid}}').onsubmit(); return false;">
        <label><input type="radio" name="__annule" value="1"
                      onclick="Infrastructure.setValueForm('editLit{{$_lit->_guid}}', 'annule', this.value);" /> Oui </label>
        <label><input type="radio" name="__annule" value="0"
                      onclick="Infrastructure.setValueForm('editLit{{$_lit->_guid}}', 'annule', this.value);" checked /> Non </label>
        <button type="submit" style="display:none;"></button>
      </form>
    </td>

    {{if "atih"|module_active }}
      <td>
        <form name="saveIdentifieLit{{$_lit->_guid}}" method="post"
              onsubmit="getForm('editLit{{$_lit->_guid}}').onsubmit(); return false;">
          {{mb_key object=$_lit}}
          {{mb_class object=$_lit}}
          <label><input type="radio" name="__identifie" value="1"
                        onclick="Infrastructure.setValueForm('editLit{{$_lit->_guid}}', 'identifie', this.value);" /> Oui </label>
          <label><input type="radio" name="__identifie" value="0"
                        onclick="Infrastructure.setValueForm('editLit{{$_lit->_guid}}', 'identifie', this.value);" checked /> Non
          </label>
          <button type="submit" style="display:none;"></button>
        </form>
      </td>
    {{/if}}
    <td></td>
    <td></td>

    <td class="button">
      <form name="editLit{{$_lit->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Infrastructure.reloadLitLine('{{$_lit->_id}}', '{{$chambre->_id}}')}})">
        {{mb_key object=$_lit}}
        {{mb_class object=$_lit}}
        <input type="hidden" name="chambre_id" value="{{$chambre->_id}}" />
        <input type="hidden" id="rank" name="rank" value="{{$_lit->rank}}" />
        <input type="hidden" id="nom" name="nom" value="{{$_lit->nom}}" />
        <input type="hidden" id="code" name="code" value="{{$_lit->nom}}" />
        <input type="hidden" id="nom_complet" name="nom_complet" value="{{$_lit->nom_complet}}" />
        <input type="hidden" id="annule" name="annule" value="{{$_lit->annule}}" />
        {{if "atih"|module_active }}
          <input type="hidden" id="identifie" name="identifie" value="{{$_lit->identifie}}" />
        {{/if}}
        <button class="save notext" type="submit"></button>
      </form>
    </td>
  </tr>
{{/if}}
