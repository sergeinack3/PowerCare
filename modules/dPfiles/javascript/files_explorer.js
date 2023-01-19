FilesExplorer = window.FilesExplorer || {
  changeFilesPage: function (start) {
    var form = getForm('files_explorer');
    $V(form.start, start);
    form.onsubmit();
  },

  orderFilesList: function (order, way) {
    var form = getForm('files_explorer');
    $V(form._order, order);
    $V(form._way, way);
    $V(form.elements.start, '0');
    form.onsubmit();
  },

  exportAsCSV: function () {
    var form = getForm('files_explorer');
    $V(form.a, 'ajax_export_files');
    $(form).target = "_blank";
    $V($(form).suppressHeaders, '1');
    form.submit();
    $V(form.a, 'ajax_search_files');
    $(form).target = "";
    $V($(form).suppressHeaders, '0');
  },

  clearField: function (input_field, hidden_field) {
    $V(input_field, 0);
    $V(input_field.up('td').down('input'), '');
    $V(hidden_field, '');
  },

  makeUserAutocomplete: function (form, input_field) {
    var user_autocomplete = new Url('system', 'ajax_seek_autocomplete');
    user_autocomplete.addParam('object_class', 'CMediusers');
    user_autocomplete.addParam('input_field', input_field.name);

    user_autocomplete.autoComplete(input_field, null, {
      minChars:           2,
      method:             'get',
      select:             'view',
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        if ($V(input_field) === "") {
          $V(input_field, selected.down('.view').innerHTML);
        }
        var id = selected.getAttribute('id').split('-')[2];
        $V(form.elements.user_id, id, true);
      }
    });
  },

  makeFunctionAutocomplete: function (form, input_field) {
    var user_autocomplete = new Url('system', 'ajax_seek_autocomplete');
    user_autocomplete.addParam('object_class', 'CFunctions');
    user_autocomplete.addParam('input_field', input_field.name);
    user_autocomplete.autoComplete(input_field, null, {
      minChars:           2,
      method:             'get',
      select:             'view',
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        if ($V(input_field) === "") {
          $V(input_field, selected.down('.view').innerHTML);
        }
        var id = selected.getAttribute('id').split('-')[2];
        $V(form.elements.function_id, id, true);
      }
    });
  },

  makeCategoryAutocomplete: function (form, input_field) {
    var category_autocomplete = new Url("system", "ajax_seek_autocomplete");
    category_autocomplete.addParam('object_class', 'CFilesCategory');
    category_autocomplete.addParam('input_field', input_field.name);
    category_autocomplete.autoComplete(input_field, null, {
      minChars:           0,
      dropdown:           true,
      method:             'get',
      select:             'view',
      afterUpdateElement: function (field, selected) {
        if ($V(input_field) === "") {
          $V(input_field, selected.down('.view').innerHTML);
        }
        var id = selected.down('.value').innerHTML;
        $V(form.elements.category_id, id, true);
      }
    });
  },

  updateStats: function (count, min, max, mean, std_deviation) {
    $('stats-file-count').innerHTML = count;
    $('stats-file-min-time').innerHTML = min + ' ms';
    $('stats-file-max-time').innerHTML = max + ' ms';
    $('stats-file-mean-time').innerHTML = mean + ' ms';
    $('stats-file-std-deviation').innerHTML = std_deviation;
  }
};
