/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Fields = window.Fields || {
  empty: {},
  value_mode: null,
  fields: {},
  max_sections: null,
  current_item: {},

  reloadItem: (select) => {
    let mode = select.dataset.mode;
    let rank = parseInt(select.dataset.rank);

    if (!Fields.current_item[mode]) {
        Fields.current_item[mode] = {};
    }

    if (rank === 0) {
        Fields.current_item[mode] = {};
    }

    Fields.current_item[mode][select.id] = $V(select);

    let subitem_select = $('section-' + mode + '-' + (rank + 1)).update();
    subitem_select.selectedIndex = -1;
    subitem_select.scrollTop = 0;

    if (Fields.max_sections) {
      let i = rank + 1;

      do {
        let select = $('section-' + mode + '-' + i);

        if (select) {
          select.update();
        }

        i++;
      } while (i <= Fields.max_sections);
    }

    $H(Fields.getSubItems(select)).each(function (item) {
      let is_section = Object.isUndefined(item[1].field);
      var sub_item = '';
      let vw_item = item[0];

      if (is_section) {
        sub_item = vw_item;
        vw_item += ' &gt;';
      } else {
        sub_item = btoa(Fields.value_mode ? item[1].valueHTML : item[1].fieldHTML);
        vw_item = vw_item.split(' - ')[1]
      }

      // On retire le préfixe SIH si nécessaire
      if (/^SIH.*/.test(mode)) {
        vw_item = vw_item.replace(/^(SIH.*) - /, '');
      }

      let options_properties = {value: sub_item};

      if (item[1].options && item[1].options.identifier) {
        options_properties['data-identifier'] = item[1].options.identifier;
      }

      subitem_select.insert(DOM.option(options_properties, vw_item));
    });
  },

  getSubItems: (select) => {
    let mode = select.dataset.mode;
    let rank = parseInt(select.dataset.rank);
    let subitems = Fields.fields[mode];
    let i = 0;

    do {
        subitems = subitems[$('section-' + mode + '-' + i).value];
        // Si c'est une feuille, pas de sous-items à afficher
        if (subitems && subitems.field) {
          subitems = {};
        }
        i++
    } while (i <= rank);

    return subitems;
  },

  insertHTML: (string, identifier = '', decode_base64 = false) => {
    if (decode_base64 && string) {
      string = atob(string);
    }

    let editor = CKEDITOR.instances.htmlarea;
    let sHtml = '';
    if (Fields.value_mode) {
      sHtml = "<span class='field'>" + string + "</span>&nbsp;";
    }
    else {
      let className = 'field';
      if (string.match(/Meta Donn&eacute;es/)) {
        className = 'metadata';
      }
      identifier = identifier ? ('data-identifier="' + identifier + '"') : '';

      sHtml = '<span class="' + className + '" ' + identifier + ' contenteditable="false">' + string + '</span>';
    }
    editor.focus();
    var elt = CKEDITOR.dom.element.createFromHtml(sHtml, editor.document);
    editor.insertElement(elt);
    editor.insertText(" ");
    Control.Modal.close();
  },

  eventKey: (e) => {
    return (window.event && (window.event.keyCode || window.event.which)) || e.which || e.keyCode || false;
  },

  search: function (event, mode) {
    if (this.value.length === 1) {
      return;
    }
    if (this.value === "" && !Fields.empty[mode]) {
      Fields.empty[mode] = true;
      $('classic-' + mode).toggle();
      $('search-' + mode).toggle();
    }
    else if (this.value !== "") {
      Fields.searchWord.curry(event, mode).bind(this).delay(0.3);
    }
  },

  searchWord: function (event, mode) {
    if (Fields.empty[mode]) {
      Fields.empty[mode] = false;
      $('classic-' + mode).toggle();
      $('search-' + mode).toggle();
    }

    var resultsearch = $('resultsearch-' + mode);

    var keyCode = Fields.eventKey(event);
    var length = resultsearch.options.length;

    switch (keyCode) {
      case 38: // Up
        if (resultsearch.selectedIndex == -1 || resultsearch.selectedIndex == 0) {
          resultsearch.selectedIndex = length - 1;
        }
        else {
          resultsearch.selectedIndex = (resultsearch.selectedIndex - 1) % length;
        }
        Event.stop(event);
        return;

      case 40: // Down
        resultsearch.selectedIndex = (resultsearch.selectedIndex + 1) % length;
        Event.stop(event);
        return;

      case 13:
        if (resultsearch.selectedIndex > -1) {
          insertHTML(resultsearch.value);
        }
        Event.stop(event);
        return;
    }

    // Recherche
    resultsearch.update();

    var value_lowercase = this.value.toLowerCase();

    for (var section in Fields.fields[mode]) {
      var items = Fields.fields[mode][section];
      $H(items).each((function (item) {
        // Si l'item contient des sous-items
        if (Object.isUndefined(item[1].field)) {
          $H(item[1]).each((function (_subItem) {
            if (_subItem[0].toString().toLowerCase().indexOf(value_lowercase) != -1) {
              resultsearch.insert(DOM.option({value: btoa(Fields.value_mode ? _subItem[1].valueHTML : _subItem[1].fieldHTML)}, _subItem[1].field));
            }
          }).bind(this));
        }
        else if (item[1].field.toLowerCase().indexOf(value_lowercase) != -1) {
          resultsearch.insert(DOM.option({value: btoa(Fields.value_mode ? item[1].valueHTML : item[1].fieldHTML)}, item[1].field));
        }
      }).bind(this));
    }
    resultsearch.selectedIndex = -1;
  }
};
