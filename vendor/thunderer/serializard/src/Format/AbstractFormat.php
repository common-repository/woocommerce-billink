<?php
namespace Thunder\Serializard\Format;

use Thunder\Serializard\HydratorContainer\HydratorContainerInterface as Hydrators;
use Thunder\Serializard\NormalizerContainer\NormalizerContainerInterface as Normalizers;
use Thunder\Serializard\NormalizerContext\NormalizerContextInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
abstract class AbstractFormat implements FormatInterface
{
    protected function doSerialize($var, Normalizers $handlers, NormalizerContextInterface $context, array $state = array(), array $classes = array())
    {
        if(is_object($var)) {
            $class = get_class($var);
            $handler = $handlers->getHandler($class);

            if(null === $handler) {
                throw new \RuntimeException(sprintf('No serialization handler for class %s!', $class));
            }

            $hash = spl_object_hash($var);
            $classes[] = get_class($var);
            if(isset($state[$hash])) {
                throw new \RuntimeException('Nesting cycle: '.implode(' -> ', $classes));
            }
            $state[$hash] = 1;

            return $this->doSerialize($handler($var, $context), $handlers, $context->withParent($var), $state, $classes);
        }

        if(is_array($var)) {
            $return = array();
            foreach($var as $key => $value) {
                $return[$key] = $this->doSerialize($value, $handlers, $context, $state, $classes);
            }

            return $return;
        }

        // FIXME: just return scalars, exception for different types like resources or streams

        return $var;
    }

    protected function doUnserialize($var, $class, Hydrators $hydrators)
    {
        $handler = $hydrators->getHandler($class);
        if(null === $handler) {
            throw new \RuntimeException(sprintf('No unserialization handler for class %s!', $class));
        }

        return $handler($var, $hydrators);
    }
}
