/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
RouteCreator = window.RouteCreator || {
  requirements: 1,
  parameters: 1,
  responses: 1,

  /**
   * Create the modal item to display route creation
   */
  showCreateRoute: function () {
    var url = new Url('dPdeveloppement', 'vw_create_route');
    url.requestModal(
      '100%', '100%',
      {
        onClose: function () {
          getForm("search").onsubmit();
        }
      }
    );
  },

  updateResult: function (value) {
    $V($('route-result'), value);
  },

  /**
   * Update the route-result field to add, remove or update requirements
   *
   * @param value
   * @param old
   */
  updateResultReq: function(value, old) {
    var maj_elem = $('route-result');

    var old_elem = $('requirement_old[' + old + ']')
    var old_value = $V(old_elem);

    if (old_value) {
      $V(maj_elem, $V(maj_elem).replace('{' + old_value + '}', '{' + value + '}'));
    }
    else {
      maj_elem.value += '/{' + value + '}';
    }

    $V(old_elem, value);
  },

  /**
   * Remove requirements from DOM
   *
   * @param req_id
   */
  removeRequirement: function (req_id) {
    // Remove from path result
    var div = $(req_id);
    var remove_value = $V(div.down('input'));
    $V($('route-result'), $V('route-result').replace('/{' + remove_value + '}', ''));

    // Remove elements
    div.remove();
  },

  removeParam: function (param_id) {
    $(param_id).remove();
  },

  /**
   * Add field to the form
   *
   * @param type
   * @param field_name
   * @param field_desc
   * @param place_holder_name
   * @param place_holder_desc
   */
  addFields: function (type, field_name, field_desc, place_holder_name, place_holder_desc) {
    var new_id = type + '[' + RouteCreator.parameters + ']';

    var dom = DOM.div(
      {id: new_id},
      DOM.button(
        {class: "remove notext", type: "button", onclick: "RouteCreator.removeParam('" + new_id + "');"},
        $T('Remove')
      ),
      DOM.input({type: "text", name: field_name + "[" + RouteCreator[type] + "]", placeholder: place_holder_name}),
      DOM.input({type: "text", name: field_desc + "[" + RouteCreator[type] + "]", placeholder: place_holder_desc}),
    );

    $('td-' + type).insert(dom);

    RouteCreator[type]++;
  },

  /**
   * Add requirements to the form
   */
  addRequirement: function () {
    var new_id = 'requirements[' + RouteCreator.requirements + ']';

    var dom = DOM.div(
      {id: new_id},
      DOM.button(
        {class: "remove notext", type: "button", onclick: "RouteCreator.removeRequirement('" + new_id + "');"},
        $T('Remove')
      ),
      DOM.input(
        {
          type: "text", name: "requirement_name[" + RouteCreator.requirements + "]", placeholder: "toto",
          onchange:"RouteCreator.updateResultReq(this.value, " + RouteCreator.requirements + ");"
        }
      ),
      DOM.input(
        {type: "text", name: "requirement_type[" + RouteCreator.requirements + "]", placeholder:"\\w+"}
      ),
      DOM.input(
        {type: 'hidden', id: 'requirement_old[' + RouteCreator.requirements + ']'}
      )
    );

    $('td-requirements').insert(dom);

    RouteCreator.requirements++;
  },

  updateLabel: function (elt_id, value) {
    var elem = $(elt_id);
    var label = $('label-' + elt_id);
    var short = $('short-' + elt_id);

    if (value || (elem && $V($(elt_id)))) {
      label.addClassName('notNullOK');
      label.removeClassName('notNull');

      if (short) {
        short.addClassName('notNullOK');
        short.removeClassName('notNull');
      }
    }
    else {
      label.addClassName('notNull');
      label.removeClassName('notNullOK');

      if (short) {
        short.addClassName('notNull');
        short.removeClassName('notNullOK');
      }
    }
  },

  updateLabelCheckbox: function (elt) {
    var not_null = false;
    $$('input.api-route-methods').each(
      function (elem) {
        if ($V(elem)) {
          RouteCreator.updateLabel('methods', $V(elem));
          not_null = true;
        }
      }
    );

    if (!not_null) {
      RouteCreator.updateLabel('methods');
    }
  }
};