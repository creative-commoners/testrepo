<?php

namespace TractorCow\ClassProxy\Proxied;

/**
 * Identifies a class as a proxy
 */
interface Proxied
{
    /**
     * @return ProxiedBehaviour
     */
    public function proxy();
}
