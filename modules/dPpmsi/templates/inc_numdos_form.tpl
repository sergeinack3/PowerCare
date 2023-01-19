{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $sejour->_ref_NDA}}
  <form name="editNumdos{{$sejour->_id}}" action="?" method="post"
        onsubmit="return onSubmitFormAjax(this); PMSI.loadActes({{$sejour->_id}});">
    <input type="hidden" name="dosql" value="do_idsante400_aed" />
    <input type="hidden" name="m" value="dPsante400" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="ajax" value="1" />
    <input type="hidden" name="id_sante400_id" value="{{$sejour->_ref_NDA->_id}}" />
    
    <table class="form" style="table-layout:fixed">
      <tr>
        <th class="category" colspan="4">
          Numéro de dossier
          <script>
            SejourHprimSelector.init{{$sejour->_id}} = function() {
              this.sForm      = "editNumdos{{$sejour->_id}}";
              this.sId        = "id400";
              this.sIPPForm   = "editIPP";
              this.sIPPId     = "id400";
              this.sIPP       = document.forms.editIPP.id400.value;
              this.sPatNom    = "{{$patient->nom}}";
              this.sPatPrenom = "{{$patient->prenom}}";
              this.pop();
            };
          </script>
        </th>
      </tr>
      <tr>
        <th>
          <label for="id400" title="Saisir le numéro de dossier">Numéro de dossier</label>
        </th>
        <td>
          <input type="text" class="notNull" name="id400" value="{{$sejour->_ref_NDA->id400}}" size="8" />
          <input type="hidden" class="notNull" name="tag" value="{{$sejour->_ref_NDA->tag}}" />
          <input type="hidden" class="notNull" name="object_id" value="{{$sejour->_id}}" />
          <input type="hidden" class="notNull" name="object_class" value="CSejour" />
          <input type="hidden" name="last_update" value="{{$sejour->_ref_NDA->last_update}}" />
          <em>(Suggestion : {{$sejour->_guess_NDA}}) </em>
        </td>
        <td class="button" rowspan="2">
          <button class="submit">{{tr}}Validate{{/tr}}</button>
        </td>
        <td class="button" rowspan="2">
          {{if $hprim21installed}}
            <button class="search" type="button" onclick="SejourHprimSelector.init{{$sejour->_id}}()">{{tr}}Search{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
    </table>
  </form>
{{else}}
  <div class="big-warning">
    Il est propable qu'aucun tag ne soit spécifié pour le numéro de dossier, il n'est donc pas possible de manipuler les numéros de dossiers.<br />
    Allez dans <a href="?m=planningOp&tab=configure">la configuration du module {{tr}}module-dPplanningOp-court{{/tr}}</a>.
  </div>
{{/if}}
