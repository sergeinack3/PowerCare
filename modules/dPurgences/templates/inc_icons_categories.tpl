{{*
 * @package Mediboard\urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$rpu->_ref_rpu_categories|@count}}
  {{mb_return}}
{{/if}}

<div>
  {{foreach from=$rpu->_ref_rpu_categories item=_link_cat}}
    {{assign var=cat value=$_link_cat->_ref_cat}}
    {{assign var=icone value=$cat->_ref_icone}}

    {{thumbnail document=$icone profile=small style="width: 20px; height: 20px; margin :2px; background-color: transparent;" title=$cat->motif}}
  {{/foreach}}
</div>