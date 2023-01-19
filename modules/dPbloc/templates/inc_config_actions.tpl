{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="narrow">{{tr}}Classname{{/tr}}</th>
    <th>{{tr}}Action{{/tr}}</th>
  </tr>

  <tr>
    <td>
      {{tr}}CPlageOp{{/tr}}
    </td>
    <td>
      <script type="text/javascript">

        CPlageOp = {
          reaffect: function(mode_real) {
            new Url('bloc', 'httpreq_reaffect_plagesop') .
              addParam("mode_real", mode_real) .
              requestModal(400);
          },

          purgeEmpty: function(form) {
            var url = new Url('bloc', 'purge_empty_plagesop');

            if (form) {
              url.addNotNullElement(form.purge);
              url.addNotNullElement(form.max  );
              url.addElement(form.auto);
            }

            var modal = Control.Modal.stack.last();
            if (modal) {
              url.requestUpdate(modal.container.down('.content'));
            }
            else {
              url.requestModal(600);
            }

            return false;
          },

          purgeAuto: function() {
            var form = document.PurgeEmpty;
            if (form && $(form.auto.checked)) {
              CPlageOp.purgeEmpty(form);
            }
          },

          purgeSome : function() {
            var url = new Url('bloc', 'vw_purge_plagesop');
            url.requestModal(800);
          },

          mergeDuplicate: function(form) {
            var url = new Url('bloc', 'merge_duplicate_plagesop');

            if (form) {
              url.addNotNullElement(form.merge);
              url.addNotNullElement(form.max  );
              url.addElement(form.auto);
            }

            var modal = Control.Modal.stack.last();
            if (modal) {
              url.requestUpdate(modal.container.down('.content'));
            }
            else {
              url.requestModal(600);
            }

            return false;
          },

          mergeAuto: function() {
            var form = document.MergeDuplicate;
            if (form && $(form.auto.checked)) {
              CPlageOp.mergeDuplicate(form);
            }
          },

          edit: function(plageop_id, bloc_id, date) {
            var url = new Url('bloc', 'inc_edit_planning');
            url.addParam('plageop_id', plageop_id);
            url.addParam('bloc_id', bloc_id);
            url.addParam('date', date);
            url.requestModal(800);
          },

          merge: function(plage_ids) {
            var url = new Url('system', 'object_merger');
            url.addParam('objects_class', 'CPlageOp');
            url.addParam('objects_id', plage_ids);
            url.popup(800, 600);
          }
        };

        Datamining = {
          board: function() {
            this.url = new Url('bloc', 'ajax_mine_salle');
            this.url.requestModal('800', '600');
          },

          refresh : function() {
            this.url.modalObject.refresh();
          }
        };

        CBloc = {
          purgeEmpty: function(form) {
            var url = new Url('bloc', 'purge_empty_blocop');

            if (form) {
              url.addNotNullElement(form.purge);
              url.addNotNullElement(form.max  );
            }

            var modal = Control.Modal.stack.last();
            if (modal) {
              url.requestUpdate(modal.container.down('.content'));
            }
            else {
              url.requestModal(600);
            }

            return false;
          }
        };

        openMineCSalle = function() {
          var url = new Url("bloc", "ajax_mine_salle");
          url.requestModal();
        };

      </script>

      <div>
        <button class="search" onclick="CPlageOp.reaffect(0)">Tester les plages à réattribuer</button>
        <button class="change" onclick="CPlageOp.reaffect(1)">Réattribuer les plages</button>
      </div>

      <div>
        <button class="search" onclick="CPlageOp.purgeSome();">{{tr}}bloc-action-Purge some operating ranges{{/tr}}</button>
        <button class="search" onclick="CPlageOp.purgeEmpty();">{{tr}}mod-bloc-tab-purge_empty_plagesop{{/tr}}</button>
        <button class="search" onclick="CPlageOp.mergeDuplicate();">{{tr}}mod-bloc-tab-merge_duplicate_plagesop{{/tr}}</button>
      </div>

    </td>
  </tr>

  <tr>
    <td>{{tr}}CSalle{{/tr}}</td>
    <td>
      <button class="search" onclick="Datamining.board();">Exploration de données</button>
    </td>
  </tr>

  <tr>
    <td>{{tr}}CBlocOperatoire{{/tr}}</td>
    <td>
      <button class="search" onclick="CBloc.purgeEmpty();">{{tr}}mod-bloc-tab-purge_empty_blocop{{/tr}}</button>
    </td>
  </tr>
</table>