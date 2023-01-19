{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse}}
{{mb_script module=jfse script=UserManagement}}

<script type="text/javascript">
    Main.add(function() {
      UserManagement.initializeIndexView();
    });
</script>

<ul id="tabs-user-management-index" class="control_tabs">
    <li><a href="#users-container">{{tr}}CJfseUserView-title-list{{/tr}}</a></li>
    <li><a href="#establishments-container">{{tr}}CJfseEstablishmentView-title-list{{/tr}}</a></li>
</ul>

<div id="users-container" style="display: none;" class="me-no-align"></div>
<div id="establishments-container" style="display: none;" class="me-no-align"></div>
