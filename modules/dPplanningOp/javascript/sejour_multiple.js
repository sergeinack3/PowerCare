/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SejourMultiple = {
  ranks: 1,

  addSlot: function(entree, sortie) {
    this.ranks++;

    var sejour = DOM.div({id: 'sejour_' + this.ranks, className: 'dhe_multiple', style: 'display: inline-block;'});

    $('sejours_area').insert(sejour);

    var form = getForm('editSejour');

    new Url('planningOp', 'ajax_add_slot_sejour')
      .addParam('rank'  , this.ranks)
      .addParam("entree", entree)
      .addParam("sortie", sortie)
      .addParam('_hour_entree_prevue', $V(form._hour_entree_prevue))
      .addParam('_min_entree_prevue', $V(form._min_entree_prevue))
      .addParam('_hour_sortie_prevue', $V(form._hour_sortie_prevue))
      .addParam('_min_sortie_prevue', $V(form._min_sortie_prevue))
      .requestUpdate(sejour);
  },

  removeSlot: function(id) {
    var slot = $('sejour_' + id);
    if (!slot) {
      return;
    }
    slot.remove();
  },

  removeSlots: function() {
    var i = this.ranks;
    while (i > 1) {
      this.removeSlot(i);
      i--;
    }
  },

  showFrequency: function() {
    Modal.open($('seancesFrequency'), {
      showClose: true,
      title: $T('CSejour-title-add_frequency')
    });
  },

  setFrequency: function(form) {
    var start = new Date($V(form.elements['start'])).getTime();
    var end = new Date($V(form.elements['end'])).getTime();
    var frequency = parseInt($V(form.elements['frequency'])) * 86400000;
    var entree = $V(form.elements['entree']);
    var sortie = $V(form.elements['sortie']);
    var date = start, last_seance;

    while (date <= end) {
      if ((last_seance && date - last_seance >= frequency) || !last_seance) {
        this.addSlot(new Date(date).toDATE() + ' ' + entree, new Date(date).toDATE() + ' ' + sortie);
        last_seance = date;
      }

      /* Add a day */
      date = date + 86400000;
    }

    $V(form.elements['start'], '');
    $V(form.elements['start_da'], '');
    $V(form.elements['end'], '');
    $V(form.elements['end_da'], '');
    $V(form.elements['frequency'], '');
    Control.Modal.close();

    return false;
  },

  fillSlots: function() {
    if (!window.sejours_multiples.length) {
      return;
    }

    window.sejours_multiples.each(function(_sejour) {
      SejourMultiple.addSlot(_sejour.entree, _sejour.sortie);
    })
  },

  validateSlots: function() {
    window.sejours_multiples = [];

    var sejour_area = $('sejours_multiples');

    // Validate all forms
    var forms_validated = false;
    $$('.dhe_multiple').each(function(_element) {
      forms_validated = checkForm(_element.down('form'));
    });

    if (!forms_validated) {
      return;
    }

    sejour_area.update();

    $$('.dhe_multiple').each(function(_element) {
      var form = _element.down('form');

      var rank = $V(form._rank_sejour_multiple);

      var hour_entree = SejourMultiple.addPad($V(form._hour_entree_prevue));
      var min_entree  = SejourMultiple.addPad($V(form._min_entree_prevue));
      var hour_sortie = SejourMultiple.addPad($V(form._hour_sortie_prevue));
      var min_sortie  = SejourMultiple.addPad($V(form._min_sortie_prevue));

      var _sejour = {
        entree: $V(form._date_entree_prevue) + ' ' + hour_entree + ':' + min_entree + ':00',
        sortie: $V(form._date_sortie_prevue) + ' ' + hour_sortie + ':' + min_sortie + ':00',
        rank  : rank
      };

      var sejour_view = DOM.div(
        null,
        DOM.button({type: 'button', className: 'remove notext', onclick: "this.up('div').remove(); SejourMultiple.delLine(" + rank + ");"}),
        "Du " + Date.fromDATETIME(_sejour.entree).toLocaleDateTime() + ' au ' + Date.fromDATETIME(_sejour.sortie).toLocaleDateTime()
      );

      sejour_area.insert(sejour_view);

      window.sejours_multiples.push(_sejour);
    });

    Control.Modal.close();
  },

  delLine: function(rank) {
    Object.keys(window.sejours_multiples).each(function(key) {
      if (window.sejours_multiples[key].rank == rank) {
        window.sejours_multiples.splice(key, 1);
      }
    });
  },

  addPad: function(number) {
    if (number >= 10) {
      return number;
    }

    return '0' + number;
  }
};
