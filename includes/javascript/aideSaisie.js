/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Provides auto-completion to helped fields
 */
var AideSaisie = {
  localStoragePrefix: "aidesaisie",
  cache: {},
  lock_get: {},

  AutoComplete: Class.create({
    timestamp: "",
    initialize: function(element, options) {
      this.element = $(element);

      if ($(this.element.form).isReadonly()) {
        return;
      }

      this.options = Object.extend({
        dependField1: null,
        classDependField1: null,
        classDependField2: null,
        dependField2: null, 
        searchField: null, 
        objectClass: null, 
        userId: User.id,
        userView: User.view,
        contextUserId: User.id,
        contextUserView: User.view,
        validate: null,//element.form.onsubmit.bind(element.form),
        validateOnBlur: true,
        resetSearchField: true,
        resetDependFields: true,
        filterWithDependFields: true,
        defaultUserId: null,
        defaultUserView: null,
        show_group: true,
        show_function: true,
        updateDF: true,
        property: '',
        strict: true,
        timestamp: AideSaisie.timestamp,
        height: "auto",
        width: null
      }, options);
      this.init();
    },

    init: function() {
      this.options.defaultUserId = this.options.contextUserId;
      this.options.defaultUserView = this.options.contextUserView;
      this.searchField = $(this.options.searchField || this.element);
      this.isContextOwner = this.options.userId == this.options.contextUserId;
      this.list = this.createListContainer();

      var url = new Url("compteRendu", "ajax_get_aides");
      url.addParam("property", this.options.property || this.element.name);
      url.addParam("object_class", this.options.objectClass);
      url.addParam("user_id", this.options.defaultUserId);

      // If it is a textarea
      if (/^textarea$/i.test(this.searchField.tagName)) {
        this.buildAdvancedUI(url);
      }
      else {
        url.autoComplete(this.searchField, this.list, {
          minChars: 2,
          updateElement: this.update.bind(this),
          paramName: "_search",
          dropdown: true,
          localStorage: true,
          callback: (function() { return this.makeKey(); }).bind(this)
        });
      }
    },

    makeKey: function() {
      return AideSaisie.localStoragePrefix + "-" +
             this.options.objectClass      + "-" +
             this.options.defaultUserId    + "-" +
             this.options.property || this.searchField.name;
    },

    // Create div to feed
    createListContainer: function() {
      var list = new Element("div", {
        id: this.searchField.id + "_auto_complete"
      }).addClassName("autocomplete").setStyle({
        width: "400px",
        height: this.options.height
      }).hide();

      this.searchField.insert({after: list});
      return list;
    },

    getSelectedData: function(selected) {
      var oDepend1 = selected.down(".depend1");
      var oDepend2 = selected.down(".depend2");
      var oText    = selected.down(".value");
      var links    = selected.select("a.hypertext_links");

      return {
        depend1: oDepend1 ? oDepend1.getText() : "",
        depend2: oDepend2 ? oDepend2.getText() : "",
        text:    oText.getText(),
        links:   links
      };
    },

    // Update field after selection
    update: function(selected) {
      var data = this.getSelectedData(selected);

      $V(this.options.dependField1, data.depend1);
      $V(this.options.dependField2, data.depend2);
      $V(this.element, data.text.strip());
      this.element.tryFocus();
    },

    // Update depend fields after selection
    updateDependFields: function(input, selected) {
      if (!this.options.updateDF) return;

      var data = this.getSelectedData(selected);
      if ($V(input).charAt($V(input).length - 1) != '\n') {
        $V(input, $V(input) + ' ');
      }
      if (Object.isFunction(input.onchange)) {
        input.onchange.bindAsEventListener(input)();
      }
      input.fire("ui:change");
      input.tryFocus();

      if (data.depend1) {
        $V(this.options.dependField1, data.depend1);
      }
      if (data.depend2) {
        $V(this.options.dependField2, data.depend2);
      }

      var links_area;
      if (data.links && (links_area = input.up("form").down("div.hypertext_links_area"))) {
        data.links.each(function(link) {
          var link_id = link.get("link_id");
          link.show();
          links_area.insert(
            DOM.div(null,
            DOM.input({type: "hidden", name: "_hypertext_links_ids[" + link_id + "]", value: link_id}),
            DOM.button({className: "remove notext", type: "button", onclick: "this.up('div').remove()"}),
            link)
          );
        });

        links_area.up("fieldset").show();
      }
    },

    buildAdvancedUI: function(url) {
      var throbber, list, toolbar,
          options = this.options,
          buttons = {};

      var down_img = DOM.i({class: "me-icon caret-down", title: $T('CAideSaisie-see_all_choice')});
      var group_img = DOM.i({class: "me-icon group", title: $T('CAideSaisie-new_for')+User["group"].view});
      var user_func_img = DOM.i({class: "me-icon function", title: $T('CAideSaisie-new_for')+User["function"].view });
      var user_img = DOM.i({class: "me-icon user", title: $T('CAideSaisie-new_for')+User.view});
      var new_img = DOM.i({class: "me-icon add", title: $T('CAideSaisie-new')});
      var user_glow_img = DOM.i({class: "me-icon user", title: this.options.defaultUserView});
      var timestamp_img = DOM.i({class: "me-icon clock", title: $T('CAideSaisie-add_timestamp')});
      var valid_img = DOM.i({class: "me-icon save", title: $T('Validate')});

      var container = 
        DOM.div({className: "textarea-helped"},
        toolbar = DOM.div({className: "toolbar " + Preferences.textareaToolbarPosition},
          throbber = DOM.a({href: "#1", className: "throbber"}).hide(),
          //buttons.grid   = DOM.a({href: "#1"}, DOM.img({src: "images/icons/grid.png", title: "Mode grille"})),
          buttons.down   = DOM.a({href: "#1"}, down_img),
          buttons.create = DOM.a({href: "#1"},
            DOM.span({style: "display: none;", className: "sub-toolbar"},
              !options.show_group    ? null : DOM.span ({},
                buttons.newGroup    = group_img,
                DOM.br({})
              ),
              !options.show_function ? null : DOM.span ({},
                buttons.newFunction = user_func_img,
                DOM.br({})
              ),
              DOM.span ({},
                buttons.newUser = user_img
              )
            ),
            buttons.createIcon = new_img
          ),
          buttons.owner     = DOM.a({href: "#1"}, user_glow_img).setVisible(Preferences.aideOwner == '1'),
          buttons.timestamp = DOM.a({href: "#1"}, timestamp_img).setVisible(Preferences.aideTimestamp == '1'),
          buttons.valid     = DOM.a({href: "#1"}, valid_img).setVisible(this.options.validate)
        ).hide(),
        list = this.list.setStyle({marginLeft: "-2px"})
      );

      toolbar.doShow = function() {
        if (toolbar.timeout) {
          window.clearTimeout(toolbar.timeout);
          toolbar.timeout = null;
        }
        toolbar.show();
      };

      toolbar.doHide = function() {
        if (toolbar.timeout) {
          return;
        }

        toolbar.timeout = (function() {
          toolbar.hide();
          toolbar.select(".sub-toolbar").invoke("hide");
          toolbar.canHide = false;
        }).delay(0.5);
      };

      this.searchField.up().
        observe(Preferences.aideShowOver == '1' ? 'mousemove' : 'dblclick', toolbar.doShow).
        observe('mouseout', toolbar.doHide)/*.
        observe('click'   , toolbar.doHide).
        observe('keydown' , toolbar.doHide)*/;

      // to prevent mousemove on the list to trigger toolbar.show
      list.observe("mousemove", Event.stop);

      if (Preferences.aideShowOver == '0') {
        toolbar.observe('mousemove', toolbar.doShow);
      }

      if (this.searchField.className.indexOf("markdown") != -1) {
        function insertTag(event) {
          var field = this.searchField;
          var caret = field.caret();
          var value = $V(field);
          var button = Event.element(event);
          var type = button.get("type");
          var newText;

          if (type == "list") {
            var lines = value.substr(caret.begin, caret.end - caret.begin).split(/[\r\n]+/g);
            for (var i = 0, l = lines.length; i < l; i++) {
              lines[i] = " * " + lines[i];
            }

            newText = lines.join("\n");
          }
          else {
            var marker = button.get('marker');
            newText = marker +
                      value.substr(caret.begin, caret.end - caret.begin) +
                      marker;
          }

          value =
            value.substr(0, caret.begin) +
            newText +
            value.substr(caret.end);

          $V(field, value);

          field.caret(caret.begin, caret.begin + newText.length);

          Event.stop(event);
        }

        /*var that = this;
        var colors = ['00', '33', '66', '99', 'CC', 'FF'];
        var colorPalette = DOM.div({className: "sub-toolbar", style: "display: none;"});
        var colorButton;
        colors.each(function(r) {
          var square = DOM.div({style: "width: 60px; height: 60px; display: inline-block;"});
          colors.each(function(g) {
            colors.each(function(b) {
              var color = "#"+r+g+b;
              square.insert(
                DOM.div({className: "color", style: "background: "+color, 'data-type': 'color', 'data-color': color}, "&nbsp;")
                  .observe('click', insertTag.bindAsEventListener(that))
              );
            });
          });

          colorPalette.insert(square);
        });*/

        // markdown buttons
        var mdButtons = [
          DOM.span({style: 'border-left: 1px solid #999; margin: 0 3px;', className: 'me-no-display'}),

          DOM.button({className: 'fa fa-bold notext me-small me-tertiary', type: 'button', 'data-marker': '**'})
            .observe('click', insertTag.bindAsEventListener(this)),
          DOM.button({className: 'fa fa-italic notext me-small me-tertiary', type: 'button', 'data-marker': '_'})
            .observe('click', insertTag.bindAsEventListener(this)),
          DOM.button({className: 'fa fa-list notext me-small me-tertiary', type: 'button', 'data-type': 'list'})
            .observe('click', insertTag.bindAsEventListener(this)),
          DOM.button({className: 'fa fa-strikethrough notext me-small me-tertiary', type: 'button', 'data-marker': '~~'})
            .observe('click', insertTag.bindAsEventListener(this)),

          //colorButton = DOM.button({className: 'drawing notext color-palette', type: 'button'},
          //  colorPalette
          //),

          DOM.button({className: 'fa help notext me-small me-tertiary', type: 'button'})
            .observe('click', App.openMarkdownHelp)
        ];

        mdButtons.each(function(button) {
          toolbar.insert(button);
        });
      }

      //buttons.invoke('observe', 'mouseover', Event.stop);

      var validate = this.options.validate ? function() {
        this.text = $V(this.searchField);
        this.options.validate(this.text);

        if (this.options.resetDependFields) {
          $V(this.options.dependField1, '');
          $V(this.options.dependField2, '');
        }
        if (this.options.resetSearchField) {
          $V(this.searchField, '');
        }
      }.bind(this) : Prototype.emptyFunction;

      var autocompleteDelays = {
        "short": 0.1,
        "medium": 0.5,
        "long": 1.0
      };

      // Setup the autocompleter
      var autocomplete = url.autoComplete(this.searchField, list, {
        minChars: Preferences.aideAutoComplete == '0' ? 65536 : 2,
        tokens: "\n",
        indicator: throbber,
        select: "value", 
        paramName: "_search",
        caretBounds: true,
        width: this.options.width ? this.options.width : "auto",
        frequency: autocompleteDelays[Preferences.autocompleteDelay],
        localStorage: true,
        callback: (function(input, query) {
          return {
            params:
            options.filterWithDependFields ? (
              (options.dependField1 ? ("&depend_value_1=" + ($V(options.dependField1) || "")) : "") +
              (options.dependField2 ? ("&depend_value_2=" + ($V(options.dependField2) || "")) : "")
            ) : "",
            key: this.makeKey()
          };
        }).bind(this),
        getDependFields: function() {
          return {
            dependField1: options.filterWithDependFields && options.dependField1 ? $V(options.dependField1) : "",
            dependField2: options.filterWithDependFields && options.dependField2 ? $V(options.dependField2) : ""
          };
        },
        getProperty: (function() {
          return (options.property || this.element.name);
        }).bind(this),
        dontSelectFirst: true,
        onAfterShow: function(element, update) {
          if (update.select("li").length == 0) {
            autocomplete.active = false;
            autocomplete.hasFocus = false;
            autocomplete.hide();
            return;
          }

          /*update.down('ul').observe("click", function(event){
            autocomplete.tokenBoundsBlurIE = autocomplete.element.getInputSelection();
            console.debug(autocomplete.tokenBoundsBlurIE);
          });*/
        },
        afterUpdateElement: this.updateDependFields.bind(this)
      });

      // The blur event must not hide the list
      Event.stopObserving(this.element, 'blur');
      Event.observe(this.element, 'blur', function() {
        // needed to make click events working
        //setTimeout(this.hide.bind(this), 2500);
        this.hasFocus = false;
        this.active = false;
      }.bindAsEventListener(autocomplete));
      
      document.observe("click", function(e) {
        // if click outside the container
        var element = Event.element(e); // Sometimes, element is null (maybe when it is <html>)
        if (element && !element.descendantOf(container)) {
          autocomplete.hasFocus = false;
          autocomplete.active = false;
          autocomplete.hide();
        }
      });

      // Grid mode 
      var gridMode = function(e) {
        var options = this.options,
            fragment = "",
            dependValue,
            url = new Url("compteRendu", "aides_saisie_grid");

        dependValue = $V(options.dependField1);
        if (dependValue) {
          fragment += options.objectClass+"-"+dependValue;
        }

        dependValue = $V(options.dependField2);
        if (dependValue) {
          fragment += (fragment ? "," : "") + options.objectClass+"-"+dependValue;
        }

        url.addParam("object_class", options.objectClass);
        url.addParam("user_id", options.defaultUserId);
        url.addParam("property", this.element.name);
        url.setFragment(fragment);
        url.popup(900, 600, $T('CAideSaisie-grille'));

        url.oWindow.applyHelper = function(title, text) {
          this.element.value += text+"\n";
        }.bind(this);
      }.bindAsEventListener(this);

      // quick creation
      var createQuick = function(owner, id) {
        var text = this.text || this.element.value;
        var name = text.split(/\s+/).splice(0, 3).join(" ");
        
        var url = new Url()
          .addParam("m", "compteRendu")
          .addParam("@class", "CAideSaisie")
          .addParam("del", 0)
          .addParam("class", options.objectClass)
          .addParam("field", options.property || this.element.name)

          .addParam("name", name)
          .addParam("text", text)

          .addParam("depend_value_1", $V(options.dependField1))
          .addParam("depend_value_2", $V(options.dependField2))
          .addParam(owner, id);

        url.requestUpdate("systemMsg", {
          method: "post",
          onComplete: function() {
            AideSaisie.removeLocalStorage();
          }
        });
      }.bind(this);

      buttons.newUser    .observe('click', createQuick.curry("user_id", User.id));
      if (options.show_function) {
        buttons.newFunction.observe('click', createQuick.curry("function_id", User["function"].id));
      }
      if (options.show_group) {
        buttons.newGroup   .observe('click', createQuick.curry("group_id", User["group"].id));
      }

      // Toolbar buttons actions
      if (!this.isContextOwner) {
        buttons.owner.observe('click', function (e) {
          if (this.options.defaultUserId == this.options.userId) {
            this.options.defaultUserId = this.options.contextUserId;
            this.options.defaultUserView = this.options.contextUserView;
            buttons.owner.down().src = "images/icons/user-glow.png";
            buttons.owner.down().title = this.options.contextUserView;
          }
          else {
            this.options.defaultUserId = this.options.userId;
            this.options.defaultUserView = this.options.userView;
            buttons.owner.down().src = "images/icons/user.png";
            buttons.owner.down().title = this.options.userView;
          }

          var params = autocomplete.url.toQueryParams();
          params.user_id = this.options.defaultUserId;
          autocomplete.url = "?"+Object.toQueryString(params);
          autocomplete.hide();

        }.bind(this));
      }

      var activate = function() {
        this.changed = false;
        this.hasFocus = true;
        // We save the default params, change it so that _search 
        // is empty to have all the entries and restore it after
        var oldDependFields = this.options.getDependFields;

        this.options.getDependFields = (function() {
          return {
            dependField1: options.dependField1 ? $V(options.dependField1) : "",
            dependField2: options.dependField2 ? $V(options.dependField2) : ""
          };
        }).bind(this);

        this.getUpdatedChoices();
        this.options.getDependFields = oldDependFields;
      }.bind(autocomplete);

      buttons.down.observe('click', activate);
      //buttons.grid.observe('mousedown', gridMode);
      buttons.valid.observe('click', validate);
      if (Preferences.aideFastMode == '1') {
        buttons.create.observe('mouseover', function(e) {
          buttons.create.down('.sub-toolbar').show();
        });
        buttons.create.observe('mouseout', function(e) {
          buttons.create.down('.sub-toolbar').hide();
        });
      }

      buttons.createIcon.observe('click', function(e) {
        AideSaisie.create(
          this.options.objectClass, 
          this.element, 
          this.options.property, 
          $V(this.options.dependField1),
          $V(this.options.dependField2), 
          this.text,
          this.options.defaultUserId,
          this.options.classDependField1,
          this.options.classDependField2
        );
      }.bindAsEventListener(this));

      buttons.timestamp.observe('click', function() {
        var timestamp = DateFormat.format(new Date(), this.options.timestamp);
        var parts = this.options.userView.split(" ");

        timestamp = timestamp.replace(/%p/g, parts[1]);
        timestamp = timestamp.replace(/%n/g, parts[0]);
        timestamp = timestamp.replace(/%i/g, parts[1].charAt(0) + ". " + parts[0].charAt(0) + ". ");

        if (this.element.value[this.element.value.length -1] != '\n' && this.element.value.length != 0) {
          timestamp = '\n' + timestamp;
        }

        $V(this.element, this.element.value + timestamp + '\n');
        this.element.scrollTop = this.element.scrollHeight;
        this.element.tryFocus();
      }.bindAsEventListener(this));

      // We wrap the textarea with the new container
      this.searchField.insert({before: container});

      // We simulate the blur catch
      if (this.options.validateOnBlur) {
        document.observe("click", function(e) {
          // if click outside the container
          if (this.searchField.value && !Event.element(e).descendantOf(container))
            validate();
        }.bindAsEventListener(this));

        document.observe("keydown", function(e) {
          // if TAB key pressed
          if (this.searchField.value && Event.key(e) == Event.KEY_TAB)
            validate();
        }.bindAsEventListener(this));
      }
    }
  }),

  create: function (objectClass, field, name, dependValue1, dependValue2, text, userId, class_depend_value_1, class_depend_value_2) {
    var url = new Url("compteRendu", "edit_aide");
    url.addParam("text", text || field.value);

    url.requestModal("80%", "80%", {
      title: $T('CAideSaisie-title-create'),
      method: "post",
      showReload: false,
      getParameters: {
        m      : "compteRendu",
        dialog : "edit_aide",
        user_id: userId,
        'class': objectClass,
        field  : name || field.name,
        depend_value_1: dependValue1 || null,
        depend_value_2: dependValue2 || null,
        class_depend_value_1: class_depend_value_1 || null,
        class_depend_value_2: class_depend_value_2 || null
      }
    });
  },

  removeLocalStorage: function(key) {
    if (key) {
      store.remove(AideSaisie.localStoragePrefix + "-" + key);
    }
    else {
      AideSaisie.cache = {};

      // Remove also from parent windows
      ["opener", "parent"].each(function(w){
        if (window[w] && window[w] != window) {
          try {
            window[w].AideSaisie.cache = {};
          } catch (e) {}
        }
      });

      for (var i = 0; i < localStorage.length; i++) {
        var skey = localStorage.key(i);
        if (skey.indexOf(AideSaisie.localStoragePrefix) === 0) {
          store.remove(skey);
        }
      }
    }
  },

  gcLocalStorage: function() {
    var now = Date.now() / 1000;

    for (var i = 0; i < localStorage.length; i++) {
      var skey = localStorage.key(i);
      if (skey.indexOf(AideSaisie.localStoragePrefix) === 0) {
        var data = store.get(skey);
        if (data.expire < now) {
          store.remove(skey);
        }
      }
    }
  }
};
