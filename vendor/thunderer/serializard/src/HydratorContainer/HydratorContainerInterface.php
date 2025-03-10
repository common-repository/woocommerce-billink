<?php
namespace Thunder\Serializard\HydratorContainer;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface HydratorContainerInterface
{
    /**
     * @param string $class Class name
     *
     * @return callable
     */
    public function getHandler($class);
}
