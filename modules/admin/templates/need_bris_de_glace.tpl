{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  afterSuccessB2G = function() {
    Url.queueRequests = false;
    var pendings_requests = $H(Url.pendingRequests).values();
    Url.pendingRequests = {}; // empty the list
    if (pendings_requests.length) {
      pendings_requests.each(function(_url) {
        _url.url.requestUpdate(_url.ioTarget, _url.oOptions);
      });
    }
    else {
      window.location.reload();
    }
  };

  initModal = function(sejour_id) {
    new Url("admin", "ajax_need_bris_de_glace")
    .addParam("sejour_id", sejour_id)
    .requestModal(null, null, {
      onClose : function() {
        Url.queueRequests = false;
      },
      dontQueue : true
    });
  };

  if (!Url.queueRequests) {
    Main.add(function () {
      {{if $modale}}
        initModal('{{$sejour->_id}}');
      {{/if}}
    });
  }

  // we request the save of nexts ajax
  Url.queueRequests = true;

</script>

{{if $modale}}
  <div class="small-info">
      Bris de glace requis pour cette vue
  </div>
{{else}}
  {{mb_include module=admin template=inc_vw_form_bris_de_glace sejour_id=$sejour->_id}}
{{/if}}