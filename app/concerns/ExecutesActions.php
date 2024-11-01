<?php

namespace Tussendoor\Billink\Concerns;

use Tussendoor\Billink\Action;

trait ExecutesActions
{
    /**
     * Create and return a new Action instance with the given name.
     * @param  string $name
     * @return \Tussendoor\Billink\Action
     */
    public function trigger($name)
    {
        return new Action($name);
    }
}
