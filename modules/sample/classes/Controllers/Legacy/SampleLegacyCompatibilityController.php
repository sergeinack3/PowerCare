<?php

namespace Ox\Mediboard\Sample\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\EntryPoint;
use Ox\Core\Kernel\Routing\RouterBridge;
use Ox\Mediboard\Admin\CUser;

class SampleLegacyCompatibilityController extends CLegacyController
{

    public function legacy_compat()
    {
        $this->checkPermRead();
        $current_user = CUser::get();
        $entry_point  = new EntryPoint('legacy_compat', RouterBridge::getInstance());

        $entry_point->setScriptName('sampleLegacyCompat');
        $entry_point->addData('user', [
            'name' => ucfirst($current_user->user_last_name) . ' ' . ucfirst($current_user->user_first_name),
            'guid' => $current_user->_guid,
        ]);

        $this->renderSmarty('legacy_compat', [
            'legacy_compat' => $entry_point,
            'user'          => $current_user,
        ]);
    }
}
