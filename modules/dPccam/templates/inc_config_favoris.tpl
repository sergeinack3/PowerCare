{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  onChangeOwner = function(field, count) {
    if ($V(field) != '') {
      if (field.name == 'user_id') {
        $V(field.form.elements['_function_view'], '');
        $V(field.form.elements['function_id'], '', false);
      }
      else if (field.name == 'function_id') {
        $V(field.form.elements['_user_view'], '');
        $V(field.form.elements['user_id'], '', false);
      }

      if (count) {
        countFavoris(field);
      }
    }
  };

  countFavoris = function(field) {
    var url = new Url('ccam', 'countFavoris');
    url.addParam(field.name, $V(field));
    url.requestJSON(function(data) {
      if (data.count != 0) {
        $('favoris_count').innerHTML = data.count + ' favoris à exporter';
        $('favoris_count').removeClassName('empty');
        $('button_export_favoris').enable();
      }
      else {
        $('favoris_count').innerHTML = 'Aucun favoris à exporter';
        $('favoris_count').addClassName('empty');
        $('button_export_favoris').disable();
      }
    });
  };

  emptySelector = function(form, object) {
    $V(form.elements['_' + object + '_view'], '', false);
    $V(form.elements[object + '_id'], '', false);
    $('button_export_favoris').disable();
  };

  Main.add(function() {
    var form = getForm('exportFavoris');

    var url = new Url('mediusers', 'ajax_users_autocomplete');
    url.addParam('praticiens', 1);
    url.addParam('input_field', '_user_view');
    url.autoComplete(form.elements['_user_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        $V(field, selected.down('.view').innerHTML);
        $V(field.form.elements['user_id'], selected.getAttribute('id').split('-')[2]);
      }
    });

    url = new Url('mediusers', 'ajax_functions_autocomplete');
    url.addParam('type', 'cabinet');
    url.addParam('input_field', '_function_view');
    url.autoComplete(form.elements['_function_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        $V(field, selected.down('.view').innerHTML);
        $V(field.form.elements['function_id'], selected.getAttribute('id').split('-')[2]);
      }
    });

    form = getForm('importFavoris');

    url = new Url('mediusers', 'ajax_users_autocomplete');
    url.addParam('praticiens', 1);
    url.addParam('input_field', '_user_view');
    url.autoComplete(form.elements['_user_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        $V(field, selected.down('.view').innerHTML);
        $V(field.form.elements['user_id'], selected.getAttribute('id').split('-')[2]);
      }
    });

    url = new Url('mediusers', 'ajax_functions_autocomplete');
    url.addParam('type', 'cabinet');
    url.addParam('input_field', '_function_view');
    url.autoComplete(form.elements['_function_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        $V(field, selected.down('.view').innerHTML);
        $V(field.form.elements['function_id'], selected.getAttribute('id').split('-')[2]);
      }
    });
  });
</script>

<fieldset style="display: inline-block; width: 49%;">
  <legend>Export de favoris</legend>
  <form name="exportFavoris" method="post" action="?" target="_blank">
    <input type="hidden" name="m" value="ccam"/>
    <input type="hidden" name="dosql" value="exportFavoris"/>

    <table class="form">
      <tr>
        <td colspan="2">
          <div class="small-info">
            Sélectionner l'utilisateur ou la fonction dont vous voulez exporter les favoris
          </div>
        </td>
      </tr>
      <tr>
        <th>
          <label for="user_id">{{tr}}CUser{{/tr}}</label>
        </th>
        <td>
          <input type="text" name="_user_view" value="" style="width: 12em;">
          <input type="hidden" name="user_id" value="" onchange="onChangeOwner(this, true)">
          <button type="button" class="cancel notext" onclick="emptySelector(this.form, 'user');">{{tr}}Empty{{/tr}}</button>
        </td>
      </tr>
      <tr>
        <th>
          <label for="function_id">{{tr}}CFunctions{{/tr}}</label>
        </th>
        <td>
          <input type="text" name="_function_view" value="" style="width: 12em;">
          <input type="hidden" name="function_id" value="" onchange="onChangeOwner(this, true)">
          <button type="button" class="cancel notext" onclick="emptySelector(this.form, 'function');">{{tr}}Empty{{/tr}}</button>
        </td>
      </tr>
      <tr>
        <th>Nombre de favoris</th>
        <td id="favoris_count" class="empty">Aucun favoris à importer</td>
      </tr>
      <tr>
        <td class="button" colspan="2">
          <button id="button_export_favoris" type="submit" class="fa fa-download" disabled>
            Exporter
          </button>
        </td>
      </tr>
    </table>
  </form>
</fieldset>

<fieldset style="display: inline-block; width: 49%;">
  <legend>Import de favoris</legend>
  <form name="importFavoris" method="post" action="?" enctype="multipart/form-data" onsubmit="return onSubmitFormAjax(this);">
    <input type="hidden" name="m" value="ccam">
    <input type="hidden" name="dosql" value="importFavoris">
    <input type="hidden" name="ajax" value="1" />

    <table class="form">
      <tr>
        <td colspan="2">
          <div class="small-info">
            Le fichier doit être un fichier CSV (au format ISO), dont les champs sont séparés par des <strong>;</strong><br/>
            et les textes par <strong>"</strong>, la première ligne étant sautée :
            <ul>
              <li>Type de propriétaire (CUser ou CFunctions)</li>
              <li>Identifiant Mediboard du propriétaire</li>
              <li>Liste des tags (séparés par des |)</li>
              <li>Rang</li>
              <li>Code *</li>
              <li>Type d'objet (Consultation, Intervention ou Séjour), Intervention par défaut</li>
            </ul>
            <em>* : champs obligatoires</em>
            <br/>
            <br/>
            Dans le cas où un utilisateur ou une fonction est sélectionnée via les champs ci-dessous,<br/>
            les propriétaires renseignés dans le fichier seront ignorés,<br>
            et les favoris seront attribués au propriétaire sélectionné dans les champs.
          </div>
        </td>
      </tr>
      <tr>
        <th>
          <label for="user_id">{{tr}}CUser{{/tr}}</label>
        </th>
        <td>
          <input type="text" name="_user_view" value="" style="width: 12em;">
          <input type="hidden" name="user_id" value="" onchange="onChangeOwner(this, false);">
          <button type="button" class="cancel notext" onclick="emptySelector(this.form, 'user');">{{tr}}Empty{{/tr}}</button>
        </td>
      </tr>
      <tr>
        <th>
          <label for="function_id">{{tr}}CFunctions{{/tr}}</label>
        </th>
        <td>
          <input type="text" name="_function_view" value="" style="width: 12em;">
          <input type="hidden" name="function_id" value="" onchange="onChangeOwner(this, false);">
          <button type="button" class="cancel notext" onclick="emptySelector(this.form, 'function');">{{tr}}Empty{{/tr}}</button>
        </td>
      </tr>
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
          <button id="button_import_favoris" type="submit" class="fa fa-upload">
            Importer
          </button>
        </td>
      </tr>
    </table>
  </form>
</fieldset>
