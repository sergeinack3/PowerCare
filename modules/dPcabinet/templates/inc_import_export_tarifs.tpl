{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<script type="text/javascript">
  onSelectObject = function(field) {
    if ($V(field) != '') {
      switch (field.name) {
        case 'chir_id':
          emptyField(field.form.elements['function_id']);
          emptyField(field.form.elements['group_id']);
          break;
        case 'function_id':
          emptyField(field.form.elements['chir_id']);
          emptyField(field.form.elements['group_id']);
          break;
        case 'group_id':
          emptyField(field.form.elements['function_id']);
          emptyField(field.form.elements['chir_id']);
          break;
      }
    }

    if ($V(field.form.elements['chir_id']) != '' || $V(field.form.elements['function_id']) != '' || $V(field.form.elements['group_id']) != '') {
      $('btn_{{$action}}_tarif').enable();
    }
    else {
      $('btn_{{$action}}_tarif').disable();
    }
  };

  emptyField = function(field) {
    $V(field, '');
    if (field.name == 'chir_id') {
      $V(field.form.elements['chir_id'], '', false);
      $V(field.form.elements['_chir_view'], '');
    }
    else if (field.name == 'function_id') {
      $V(field.form.elements['function_id'], '', false);
      $V(field.form.elements['_function_view'], '');
    }

    if ($V(field.form.elements['chir_id']) == '' && $V(field.form.elements['function_id']) == '' && $V(field.form.elements['group_id']) == '') {
      $('btn_{{$action}}_tarif').disable();
    }
  };

  {{if $action == 'import'}}
    importTarif = function(form) {
      var url = new Url('cabinet', 'dosql', 'do_import_tarifs');
      url.addParam('chir_id', $V(form.elements['chir_id']));
      url.addParam('function_id', $V(form.elements['function_id']));
      url.addParam('group_id', $V(form.elements['group_id']));
      url.requestUpdate('', {
        method: 'post',
        getParameters: {m: 'cabinet', dosql: 'do_import_tarif'}
      });
    };
  {{elseif $action == 'export'}}
    exportTarif = function(form) {
      var url = new Url('cabinet', 'dosql', 'do_import_tarifs');
      url.addParam('chir_id', $V(form.elements['chir_id']));
      url.addParam('function_id', $V(form.elements['function_id']));
      url.addParam('group_id', $V(form.elements['group_id']));
      url.requestUpdate('', {
        method: 'post',
        getParameters: {m: 'cabinet', dosql: 'do_export_tarif'}
      });
    };
  {{/if}}

  Main.add(function() {
    var form = getForm('filterTarifs');

    var url = new Url('mediusers', 'ajax_users_autocomplete');
    url.addParam('rdv', 1);
    url.addParam('input_field', '_chir_view');
    url.autoComplete(form.elements['_chir_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      width: '200px',
      afterUpdateElement: function(field, selected) {
        if ($V(field) == '') {
          $V(field, selected.down('.view').innerHTML);
        }

        $V(field.form.elements['chir_id'], selected.getAttribute('id').split('-')[2])
      }
    });

    url = new Url('mediusers', 'ajax_functions_autocomplete');
    url.addParam('input_field', '_function_view');
    url.autoComplete(form.elements['_function_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      width: '200px',
      afterUpdateElement: function(field, selected) {
        if ($V(field) == '') {
          $V(field, selected.down('.view').innerHTML);
        }

        $V(field.form.elements['function_id'], selected.getAttribute('id').split('-')[2])
      }
    });
  });
</script>


{{if $action == 'import'}}
  <form name="filterTarifs" method="post" action="?" enctype="multipart/form-data" onsubmit="return onSubmitFormAjax(this, null, 'import_results');">
    <input type="hidden" name="m" value="cabinet">
    <input type="hidden" name="dosql" value="do_import_tarifs">
    <input type="hidden" name="ajax" value="1">
{{elseif $action == 'export'}}
  <form name="filterTarifs" method="post" action="?" target="_blank">
    <input type="hidden" name="m" value="cabinet">
    <input type="hidden" name="dosql" value="do_export_tarifs">
{{/if}}

  <table class="form">
    <tr>
      <th>
        {{mb_label object=$tarif field=chir_id}}
      </th>
      <td>
        {{mb_field object=$tarif field=chir_id hidden=true onchange="onSelectObject(this);"}}
        <input type="text" name="_chir_view" value="" style="width: 180px;">
        <button type="button" class="cancel notext" onclick="emptyField(this.form.elements['chir_id']);">{{tr}}Empty{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$tarif field=function_id}}
      </th>
      <td>
        {{mb_field object=$tarif field=function_id hidden=true onchange="onSelectObject(this);"}}
        <input type="text" name="_function_view" value="" style="width: 180px;">
        <button type="button" class="cancel notext" onclick="emptyField(this.form.elements['function_id']);">{{tr}}Empty{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$tarif field=group_id}}
      </th>
      <td>
        <select name="group_id" onchange="onSelectObject(this);">
          <option value="">&mdash; {{tr}}Select{{/tr}}</option>
          {{foreach from=$groups item=group}}
            <option value="{{$group->_id}}">
              {{$group->text}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    {{if $action == 'import'}}
      <tr>
        <th>
          <label for="import">Fichier d'import</label>
        </th>
        <td>
          {{mb_include module=system template=inc_inline_upload lite=true paste=false extensions='csv'}}
        </td>
      </tr>
      <tr>
        <td class="button" colspan="2">
          <button type="submit" id="btn_import_tarif" class="upload" disabled>
            {{tr}}Import{{/tr}}
          </button>
        </td>
      </tr>
      <tr>
        <th class="category" colspan="2">
          Résultats de l'import
        </th>
      </tr>
      <tr>
        <td id="import_results" colspan="2"></td>
      </tr>
    {{elseif $action == 'export'}}
      <tr>
        <td class="button" colspan="2">
          <button type="submit" id="btn_export_tarif" class="upload" disabled>
            {{tr}}Export{{/tr}}
          </button>
        </td>
      </tr>
    {{/if}}
  </table>
</form>