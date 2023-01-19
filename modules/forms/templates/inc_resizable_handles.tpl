{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_rotate value=false}}

<div data-way="nw" class="handle top-left"></div>
<div data-way="n"  class="handle top-center"></div>
<div data-way="ne" class="handle top-right"></div>
<div data-way="e"  class="handle middle-right"></div>
<div data-way="se" class="handle bottom-right"></div>
<div data-way="s"  class="handle bottom-center"></div>
<div data-way="sw" class="handle bottom-left"></div>
<div data-way="w"  class="handle middle-left"></div>

{{if $show_rotate}}
  <div data-way="rotate"  class="handle rotate"></div>
{{/if}}