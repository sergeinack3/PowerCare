{{*
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=integration_uid}}

<script>
  Main.add(function(){
    new Url("context", "ajax_widget_integration")
      .addParam("object_class", '{{$object->_class}}')
      .addParam("object_id",    '{{$object->_id}}')
      .addParam("location",     '{{$location}}')
      .addParam("uid",          '{{$integration_uid}}')
      .requestUpdate("integration-{{$integration_uid}}", function(){
        $("integration-{{$integration_uid}}").setStyle({display: "inline-block"});
      });
  });

</script>

<div style="display: none;" id="integration-{{$integration_uid}}"></div>