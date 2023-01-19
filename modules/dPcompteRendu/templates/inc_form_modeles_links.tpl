{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $nb_modeles == 0}}
  <div class="small-info">{{tr}}modele-none{{/tr}}</div>
  {{mb_return}}
{{/if}}

<form name="Add-CPackToModele" method="post" onsubmit="return Pack.onSubmitModele(this);">
  {{mb_class object=$link}}
  {{mb_key   object=$link}}
  {{mb_field object=$link field=pack_id hidden=1}}

  <table class="form me-no-box-shadow">
    <tr>
      {{me_form_field nb_cells=2 mb_object=$link mb_field=modele_id}}
        <select name="modele_id" class="notNull ref" onchange="this.form.onsubmit()" style="width: 20em;">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{if $modeles.prat|@count}}
            <optgroup label="{{tr}}CCompteRendu-owned-by-user{{/tr}}">
              {{foreach from=$modeles.prat item=_modele}}
              <option value="{{$_modele->_id}}">{{$_modele->nom}}</option>
              {{/foreach}}
            </optgroup>
          {{/if}}
          {{if $access_function}}
          {{if $modeles.func|@count}}
            <optgroup label="{{tr}}CCompteRendu-owned-by-function{{/tr}}">
              {{foreach from=$modeles.func item=_modele}}
              <option value="{{$_modele->_id}}">{{$_modele->nom}}</option>
              {{/foreach}}
            </optgroup>
          {{/if}}
          {{/if}}
          {{if $access_group}}
          {{if $modeles.etab|@count}}
            <optgroup label="{{tr}}CCompteRendu-owned-by-etablissment{{/tr}}">
              {{foreach from=$modeles.etab item=_modele}}
              <option value="{{$_modele->_id}}">{{$_modele->nom}}</option>
              {{/foreach}}
            </optgroup>
          {{/if}}
          {{/if}}
          {{if $modeles.instance|@count}}
            <optgroup label="{{tr}}CCompteRendu-owned-by-user{{/tr}}">
              {{foreach from=$modeles.instance item=_modele}}
                <option value="{{$_modele->_id}}">{{$_modele->nom}}</option>
              {{/foreach}}
            </optgroup>
          {{/if}}
        </select>
      {{/me_form_field}}
    </tr>
  </table>
</form>