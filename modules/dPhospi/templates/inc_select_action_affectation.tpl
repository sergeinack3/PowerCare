{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("choixAction");

    Calendar.regField(form._date_cut);

    {{if !$chambre->_id}}
      var group_id = null;
      {{if $affectation->sejour_id}}
        group_id = {{$affectation->_ref_sejour->group_id}};
      {{/if}}
      new Url("hospi", "ajax_lit_autocomplete")
        .addNotNullParam('group_id', group_id)
        .autoComplete(form.keywords, null, {
          minChars:           2,
          method:             "get",
          select:             "view",
          dropdown:           true,
          afterUpdateElement: function (field, selected) {
            var value = selected.id.split('-')[2];
            $V(form.lit_id, value);
          }
        });
    {{/if}}
  });

  callbackRefresh = function (form) {
    window.affectation_selected = null;
    window.lit_selected = null;

    var lit_id = $V(form.lit_id);

    // Refresh du lit que l'on quitte
    if (window.refreshMouvements) {
      refreshMouvements(Control.Modal.close, '{{$affectation->lit_id}}');

      // Action vers un lit : on refresh la ligne concernée
      if (lit_id) {
        refreshMouvements(null, lit_id);
      }
      // Sinon c'est les non placés
      else {
        loadNonPlaces();
      }
    }

    {{if $chambre->_id}}
      if (window.refreshService) {
        Control.Modal.close();
        refreshService({{$chambre->service_id}});
      }
    {{/if}}
  }
</script>

<form name="choixAction" method="post"
      onsubmit="return onSubmitFormAjax(this, callbackRefresh.curry(this));">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="dosql" value="do_cut_affectation_aed" />
  <input type="hidden" name="callback_etab" value="1" />
  {{mb_key object=$affectation}}

  <table class="form">
    <tr>
      <th class="title" colspan="3">
        Affectation du {{$affectation->entree|date_format:$conf.datetime}} au {{$affectation->sortie|date_format:$conf.datetime}}
      </th>
    </tr>
    <tr>
      <td rowspan="2" style="vertical-align: middle;">
        Lit d'origine : {{$affectation->_ref_lit}}
      </td>
      <th>
        {{if !$chambre->_id}}
          <input type="hidden" name="lit_id" value="{{$lit->_id}}"
                 onchange="$V(this.form.service_id, '', false);" />
        {{/if}}
        Lit de destination :
      </th>
      <td>
        {{if $chambre->_id}}
          <select name="lit_id">
            {{foreach from=$chambre->_ref_lits item=_lit}}
              <option value="{{$_lit->_id}}" {{if $lit->_id == $_lit->_id}}selected{{/if}}>{{$_lit->_view}}</option>
            {{/foreach}}
          </select>
        {{else}}
          <input type="text" name="keywords" style="width: 12em" value="{{$lit}}" />
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>
        Service de destination :
      </th>
      <td>
        {{if $chambre->_id}}
          <input type="hidden" name="service_id" value="{{$chambre->service_id}}" />
          {{$chambre->_ref_service->_view}}
        {{else}}
        <select name="service_id"
                onchange="$V(this.form.lit_id, '', false); $V(this.form.keywords, '');">
          <option value="">&mdash; {{tr}}CService.select{{/tr}}</option>
          {{foreach from=$services item=_service}}
            <option value="{{$_service->_id}}">{{$_service}}</option>
          {{/foreach}}
        </select>
        {{/if}}
      </td>
    </tr>
    <tr>
      <td colspan="3">
        <fieldset>
          <legend>Type d'action</legend>
          <table class="form me-no-box-shadow">
            <tr>
              <td>
                <label>
                  <input type="radio" name="action" value="mouvement" checked
                         onclick="$V(this.form.dosql, 'do_cut_affectation_aed');" />
                  Mouvement le :
                  <input type="hidden" name="_date_cut" class="dateTime notNull" value="{{$dtnow}}" />
                </label>
              </td>
            </tr>
            <tr>
              <td>
                <label>
                  <input type="radio" name="action" value="deplacement" onclick="$V(this.form.dosql, 'do_affectation_aed');" />
                  Déplacement
                </label>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td colspan="3" class="button">
        <button type="button" class="tick singleclick me-primary" onclick="this.form.onsubmit();">{{tr}}Validate{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>