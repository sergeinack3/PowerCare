{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm('retourEtablissement');

    new Url("hospi", "ajax_lit_autocomplete")
      .addParam('group_id', '{{$affectation->_ref_sejour->group_id}}')
      .autoComplete(form.keywords, null, {
        minChars:           2,
        method:             "get",
        select:             "view",
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          var value = selected.id.split('-')[2];
          $V(form.lit_id, value);
        },
        callback:           function (input, queryString) {
          var service_id = $V(form.service_id);
          queryString += "&service_id=" + service_id;
          return queryString;
        }
      }
      );
  });
</script>

<form name="retourEtablissement" method="post"
      onsubmit="return onSubmitFormAjax(this
      {{if $from_placement}}, function() {
        Control.Modal.close();
        if (window.refreshMouvements) {
          refreshMouvements(null, '{{$affectation->lit_id}}');
        }
        if (window.reloadTableau) {
          reloadTableau();
        }
      }
      {{/if}});">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="dosql" value="do_retour_etablissement" />
  <input type="hidden" name="affectation_id" value="{{$affectation->_id}}" />
  <input type="hidden" name="affectation_perm_id" value="{{$affectation_perm_id}}" />
  {{if !$from_placement}}
  <input type="hidden" name="callback" value="document.location.reload" />
  {{/if}}

  <table class="form">
    <tr>
      <th class="title" colspan="2">
        {{tr var1=$affectation->_ref_sejour->_ref_patient->_view}}CSejour-Choose bed back in etablissement{{/tr}}
      </th>
    </tr>
    <tr>
      <th class="halfPane">
        {{mb_label object=$affectation field=lit_id}}
      </th>
      <td>
        {{mb_field object=$affectation field=lit_id hidden=true}}
        <input type="text" name="keywords" value="{{$affectation->_ref_lit->_view}}" />
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$affectation field=service_id}}
      </th>
      <td>
        {{mb_field object=$affectation field=service_id hidden=true}}

        <select name="service_id" onchange="$V(this.form.lit_id, ''); $V(this.form.keywords, '');">
          {{foreach from=$services item=_service}}
            <option value="{{$_service->_id}}" {{if $_service->_id === $affectation->service_id}}selected{{/if}}>
              {{$_service->_view}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="button" class="tick me-primary" onclick="this.form.onsubmit();">{{tr}}Validate{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>