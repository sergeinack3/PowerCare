/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DatabaseExplorer = {
  urlTableSchema: {},

  displayTableData: function (dsn, table, start, count, order_column, order_way, where_column, where_value, search) {
    var url = DatabaseExplorer.makeTableDataUrl(dsn, table, start, count, order_column, order_way, where_column, where_value);
    var target = (search) ? "table-data-search" : "table-data";
    url.addParam('search', search);
    url.requestUpdate(target);

    var table_row = $('col-' + dsn + '-' + table);
    if (table_row) {
      table_row.addUniqueClassName('selected');
    }

    return false;
  },

  makeTableDataUrl: function (dsn, table, start, count, order_column, order_way, where_column, where_value) {
    var url = new Url("importTools", "ajax_vw_table_data");
    url.addParam("dsn", dsn);
    url.addParam("table", table);
    url.addParam("start", start);
    url.addNotNullParam("count", count);
    url.addNotNullParam("order_column", order_column);
    url.addNotNullParam("order_way", order_way);
    url.addNotNullParam("where_column", where_column);
    url.addNotNullParam("where_value", where_value);
    return url;
  },

  displayTableDistinctData: function (dsn, table, column) {
    var url = new Url("importTools", "ajax_vw_table_distinct_data");
    url.addParam("dsn", dsn);
    url.addParam("table", table);
    url.addParam("column", column);
    url.requestModal(400, 600);

    return false;
  },

  selectPrimaryKey: function (dsn, table, column) {
    if (dsn === 'std' || dsn === 'slave') {
      return;
    }
    var url = new Url("importTools", "ajax_select_primary_key");
    url.addParam("dsn", dsn);
    url.addParam("table", table);
    url.addParam("column", column);
    url.requestModal(400, 700);

    return false;
  },

  saveTableInfo: function (dsn, table, type, value, callback) {
    if (dsn === 'std' || dsn === 'slave') {
      return;
    }
    var url = new Url();
    url.setModuleDosql("importTools", "do_save_table_info");
    url.addParam("dsn", dsn);
    url.addParam("table", table);
    url.addParam("type", type);
    url.addParam("value", value);
    var option = {method: "post"};
    if (callback) {
      Object.extend(option, {onComplete: callback});
    }
    url.requestUpdate($(SystemMessage.id).show(), option);


    return false;
  },

  searchColumn: function (input, column) {
    var rows = $$('.search-keyword');

    rows.invoke('show');

    var terms = $V(input);
    if (!terms) {
      return;
    }

    rows.invoke('hide');

    terms = terms.split(/\s+/);
    rows.each(function (e) {
      var search = e.down('.' + column + ' .search');

      if (!search) {
        return;
      }

      terms.each(function (term) {
        if (search.getText().like(term)) {
          e.show();
        }
      });
    });
  },

  toggleHidden: function (b) {
    $("tables").toggleClassName("show_hidden", b);
  },

  toggleEmpty: function (b) {
    $("tables").toggleClassName("show_empty", b);
  },

  toggleImportant: function (b) {
    $$('tr[data-important=0]').each(function (elt) {
      (b) ? elt.hide() : elt.show();
    })
  },

  initDbAutocomplete: function (input) {
    var url = new Url("importTools", "ajax_ds_autocomplete");
    url.autoComplete(input, null, {
      minChars:           2,
      dropdown:           true,
      afterUpdateElement: function (input, selected) {
        var dsn = selected.get("dsn");
        DatabaseExplorer.loadDbTables(dsn);
        input.value = '';
      }
    });
  },

  loadDbTables: function (dsn, order_col, order_way) {
    if (!dsn) {
      return;
    }
    var url = new Url("importTools", "ajax_vw_tables");
    url.addParam("dsn", dsn);
    url.addParam("order_col", order_col);
    url.addParam("order_way", order_way);
    url.addParam("show_updates", 1);
    url.requestUpdate("tables");
  },

  filterData: function (input, classe) {
    var tr = $$(classe);

    tr.each(
      function (e) {
        e.show();
      }
    );

    var terms = $V(input);
    if (!terms) {
      return;
    }

    tr.each(
      function (e) {
        e.hide();
      }
    );

    terms = terms.split(",");
    tr.each(function (e) {
      terms.each(function (term) {
        if (e.getText().like(term)) {
          e.show();
        }
      });
    });
  },

  onFilterData: function (input, classe) {
    if (input.value === '') {
      // Click on the clearing button
      DatabaseExplorer.filterData(input, classe);
    }
  },

  saveColumnInfo: function (dsn, table, column, type, value) {
    var callback = DatabaseExplorer.displayTableData.curry(dsn, table);

    if (type === 'foreign_key') {
      callback = function () {
        Control.Modal.close();
        DatabaseExplorer.displayTableData.curry(dsn, table);
      }
    }

    var url = new Url();
    url.setModuleDosql('importTools', 'do_save_column_info');
    url.addParam('dsn', dsn);
    url.addParam('table', table);
    url.addParam('column', column);
    url.addParam('type', type);
    url.addParam('value', value);
    url.requestUpdate($(SystemMessage.id).show(), {
      method:     'post',
      onComplete: callback
    });

    return false;
  },

  toggleHiddenColumns: function (input) {
    var state = (input.checked) ? 'hidden' : 'not_hidden';
    var other_state = (input.checked) ? 'not_hidden' : 'hidden';

    $$('.db-data .' + state).each(function (elt) {
      elt.removeClassName(state);
      elt.addClassName(other_state);
    });
  },

  toggleImportantTable: function (dsn, table, important) {
    if (dsn === 'std' || dsn === 'slave') {
      return;
    }
    important ^= true;
    return DatabaseExplorer.saveTableInfo(dsn, table, 'important', important, DatabaseExplorer.loadDbTables.curry(dsn));
  },

  seeColumnMetrics: function (dsn, table, column) {
    var url = new Url('importTools', 'ajax_see_column_metrics');
    url.addParam('dsn', dsn);
    url.addParam('table', table);
    url.addParam('column', column);

    url.requestModal();
  },

  importCSV: function (dsn) {
    if (dsn === 'std' || dsn === 'slave') {
      return;
    }
    var url = new Url('importTools', 'vw_import_csv_tables');
    url.addParam('dsn', dsn);

    url.pop(1000, 8000);
  },

  analyze: function (import_class) {
    var url = new Url('importTools', 'ajax_analyze');
    url.addParam('class', import_class);
    url.requestUpdate('import-log-' + import_class);

    return false;
  },

  reset: function (import_class) {
    var url = new Url('importTools', 'ajax_reset');
    url.addParam('class', import_class);
    url.requestUpdate('import-log-' + import_class);

    return false;
  },

  getImportedMaxID: function (import_class) {
    var url = new Url('importTools', 'ajax_get_imported_max_id');
    url.addParam('class', import_class);
    url.requestUpdate('import-log-' + import_class);

    return false;
  },

  showLine: function (dsn, table, where_column, where_value, count) {
    var url = DatabaseExplorer.makeTableDataUrl(dsn, table, 0, count, null, null, where_column, where_value);
    url.addParam("line_compare", where_value);
    url.requestModal(900, 800);

  },

  viewSearchLines: function (dsn, table) {
    var url = new Url('importTools', 'vw_search_lines');
    url.addParam('dsn', dsn);
    url.addParam('table', table);
    url.requestModal(800);
  },

  submitQuerySearch: function (form) {
    var url = new Url('importTools', 'ajax_search_lines');
    url.addFormData(form);

    url.requestModal('90%', '80%');
    return false;
  },

  seeTableSchema: function (dsn, table) {
    if (dsn === 'std' || dsn === 'slave') {
      return;
    }
    var url = new Url('importTools', 'ajax_vw_table_schema');
    url.addParam('dsn', dsn);
    url.addParam('table', table);

    DatabaseExplorer.urlTableSchema = url;

    url.requestModal(800, 600);
  },

  changePage: function(page) {
    var url = new Url('importTools', 'ajax_search_lines');
    url.addParam('start', page);
    var form = getForm('search_line');
    url.addFormData(form);
    url.requestUpdate('table-data-search');
  },

  changePageDistinct: function(page, args) {
    var _args = args.split('|');
    var url = new Url("importTools", "ajax_vw_table_distinct_data");
    url.addParam("dsn", _args[0]);
    url.addParam("table", _args[1]);
    url.addParam("column", _args[2]);
    url.addParam("start", page);
    url.addParam("total", _args[3]);
    url.requestUpdate('distinct-values');
  },

  exportResult: function() {
    var url = new Url('importTools', 'ajax_export_result', 'raw');
    var form = getForm('search_line');
    url.addFormData(form);
    url.open();
  }
};
