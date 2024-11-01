<?php

namespace Tussendoor\Billink\Contracts;

interface PluginCompatible
{
    /**
     * Register the required actions/filters to achieve compatibility here.
     * @return void
     */
    public function register();
}
