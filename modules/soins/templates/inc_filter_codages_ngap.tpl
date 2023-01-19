{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    var form = getForm('filterActs-{{$sejour->_guid}}');
    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('edit', '1')
      .addParam('prof_sante', '1')
      .addParam('input_field', '_executant_id_view')
      .autoComplete(form.elements['_executant_id_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var form = getForm('filterActs-{{$sejour->_guid}}');
        $V(form.elements['_executant_id_view'], selected.down('.view').innerHTML);
        $V(form.elements['executant_id'], selected.getAttribute('id').split('-')[2]);
      }
    });

    new Url('mediusers', 'ajax_functions_autocomplete')
      .addParam('edit', '1')
      .addParam('type', 'cabinet')
      .addParam('input_field', '_function_id_view')
      .autoComplete(form.elements['_function_id_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var form = getForm('filterActs-{{$sejour->_guid}}');
        $V(form.elements['_function_id_view'], selected.down('.view').innerHTML);
        $V(form.elements['function_id'], selected.getAttribute('id').split('-')[2]);
      }
    });
  });

  onSetFilterNGAP = function(input) {
    if (input.name == 'executant_id' && $V(input) != '') {
      emptyFilterNGAP(input.form.elements['function_id']);
    }
    else if (input.name == '_executant_id_view' && $V(input) == '') {
      emptyFilterNGAP(input.form.elements['executant_id']);
    }
    else if (input.name == 'function_id' && $V(input) != '') {
      emptyFilterNGAP(input.form.elements['executant_id']);
    }
    else if (input.name == '_function_id_view' && $V(input) == '') {
      emptyFilterNGAP(input.form.elements['function_id']);
    }
  };

  emptyFilterNGAP = function(input) {
    $V(input, '', false);
    $V(input.form.elements['_' + input.name + '_view'], '', false);
  };
</script>

{{assign var=user value='Ox\Mediboard\Mediusers\CMediusers::get'|static_call:null}}
{{assign var=executant value=''}}
{{assign var=function value=''}}
{{if $user->isProfessionnelDeSante()}}
  {{if $app->user_prefs.preselected_filters_ngap_sejours == 'CMediusers'}}
    {{assign var=executant value=$user}}
  {{elseif $app->user_prefs.preselected_filters_ngap_sejours == 'CFunctions'}}
    {{assign var=function value=$user->loadRefFunction()}}
  {{/if}}
{{/if}}

<form name="filterActs-{{$sejour->_guid}}" method="post" action="?" onsubmit="">
  <input type="hidden" name="subject_guid" value="{{$subject->_guid}}">
  <fieldset>
    <legend>Filtres sur les actes</legend>
    <table class="form me-no-box-shadow">
      <tr>
        <th>
          <label for="filterActs-{{$sejour->_guid}}__executant_view">{{tr}}CActeNGAP-executant_id{{/tr}}</label>
        </th>
        <td>
          <input type="hidden" name="executant_id" id="filterActs-{{$sejour->_guid}}_executant_id"
                 onchange="onSetFilterNGAP(this);" value="{{if $executant|instanceof:'Ox\Mediboard\Mediusers\CMediusers'}}{{$executant->_id}}{{/if}}">
          <input type="text" name="_executant_id_view" id="filterActs-{{$sejour->_guid}}__executant_id_view" class="autocomplete" value="{{$executant}}" onchange="onSetFilterNGAP(this);"/>
          <button type="button" class="cancel notext" onclick="emptyFilterNGAP(this.form.elements['executant_id']);">{{tr}}Empty{{/tr}}</button>
        </td>
        <th>
          <label for="filterActs-{{$sejour->_guid}}__function_view">{{tr}}CFunctions{{/tr}}</label>
        </th>
        <td>
          <input type="hidden" name="function_id" id="filterActs-{{$sejour->_guid}}_function_id"
                 onchange="onSetFilterNGAP(this);" value="{{if $function|instanceof:'Ox\Mediboard\Mediusers\CFunctions'}}{{$function->_id}}{{/if}}">
          <input type="text" name="_function_id_view" id="filterActs-{{$sejour->_guid}}__function_id_view" class="autocomplete" value="{{$function}}" onchange="onSetFilterNGAP(this);"/>
          <button type="button" class="cancel notext" onclick="emptyFilterNGAP(this.form.elements['function_id']);">{{tr}}Empty{{/tr}}</button>
        </td>
        <td>
          <button type="button" class="fa fa-filter me-primary" onclick="ActesNGAP.refreshList(null, null, null, '0');">{{tr}}Filter{{/tr}}</button>
        </td>
      </tr>
    </table>
  </fieldset>
</form>