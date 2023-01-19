{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
    Main.add(function() {
        let iframe = $('jfse-gui-admin-settings-container');
        ViewPort.SetAvlHeight(iframe, 1.0);
        ViewPort.SetAvlWidth(iframe, 1.0)
    });
</script>

<iframe id="jfse-gui-admin-settings-container" src="{{$url}}" frameborder="0" style="width: 100%; height: 100%;"></iframe>
