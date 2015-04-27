<?php

namespace SimpleBus\Message\CallableResolver;

use SimpleBus\Message\CallableResolver\Exception\CouldNotResolveCallable;

class ServiceLocatorAwareCallableResolver implements CallableResolver
{
    /**
     * @var callable
     */
    private $serviceLocator;

    public function __construct(callable $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param $maybeCallable
     * @return callable
     */
    public function resolve($maybeCallable)
    {
        if (is_callable($maybeCallable)) {
            return $maybeCallable;
        }

        if (is_string($maybeCallable)) {
            // a string can be converted to an object, which may then be a callable
            return $this->resolve($this->loadService($maybeCallable));
        }

        if (is_array($maybeCallable) && count($maybeCallable) === 2) {
            list($serviceId, $method) = $maybeCallable;
            if (is_string($serviceId)) {
                return $this->resolve([$this->loadService($serviceId), $method]);
            }
        }

        throw new CouldNotResolveCallable();
    }

    private function loadService($serviceId)
    {
        return call_user_func($this->serviceLocator, $serviceId);
    }
}
