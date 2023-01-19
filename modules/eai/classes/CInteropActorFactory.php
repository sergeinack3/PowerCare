<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CClassMap;

class CInteropActorFactory
{
    /** @var string */
    protected const MAIN_ACTOR = '';

    /**
     * @return CInteropSenderFactory
     */
    final public function sender(): CInteropSenderFactory
    {
        return new CInteropSenderFactory();
    }

    /**
     * @return CInteropReceiverFactory
     */
    final public function receiver(): CInteropReceiverFactory
    {
        return new CInteropReceiverFactory();
    }

    /**
     * Get receiver instance
     *
     * @param string $parent
     * @param string $actor_type
     *
     * @return CInteropReceiver
     * @throws CEAIException
     */
    protected function make(string $parent, ?string $actor_type = null): CInteropActor
    {
        $childrens = CClassMap::getInstance()->getClassChildren($this::MAIN_ACTOR);
        if (!in_array($parent, $childrens)) {
            throw new CReceiverException(0, 500, 'CInteropReceiverFactory-msg-Class missing', $parent);
        }

        if ($actor_type === null) {
            return new $parent();
        }

        $parent_sn = CClassMap::getSN($parent);
        foreach ($childrens as $_child) {
            $short_name = CClassMap::getSN($_child);
            if ($short_name === ($parent_sn . $actor_type)) {
                return new $_child();
            }
        }

        // if class not found
        return new $parent();
    }
}

