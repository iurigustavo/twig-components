<?php

namespace Havit\TwigComponents\View;

use ReflectionClass;
use ReflectionParameter;

abstract class Component
{
    /** @var array */
    protected static $constructorParametersCache = [];

    /** @var string|null */
    protected $name = null;

    /** @var \Twig_Environment|null */
    protected $environment = null;

    protected $app = null;

    /**
     * Make the component instance with the given data.
     *
     * @param  array  $data
     *
     * @return static
     */
    public static function make($data = [])
    {
        $parameters = static::extractConstructorParameters();

        $data = self::parseAttributes($data);

        if (static::class === AnonymousComponent::class) {
            return new static($data);
        }

        return (new ReflectionClass(static::class))->newInstanceArgs(array_intersect_key(array_merge($parameters, $data), $parameters));
    }

    /**
     * Extract the constructor parameters for the component.
     *
     * @return array
     * @throws \ReflectionException
     */
    protected static function extractConstructorParameters()
    {
        $class       = new ReflectionClass(static::class);
        $constructor = $class->getConstructor();

        return !empty($constructor->getParameters())
            ? array_merge(...array_map(static function (ReflectionParameter $param) {
                return [$param->getName() => $param->getDefaultValue()];
            }, $constructor->getParameters()))
            : [];
//        if (!isset(static::$constructorParametersCache[static::class])) {
//            $class = new ReflectionClass(static::class);
//
//            $constructor = $class->getConstructor();
//
//            $parameters = !empty($constructor->getParameters())
//                ? array_merge(...array_map(static function (ReflectionParameter $param) {
//                    return [$param->getName() => $param->getDefaultValue()];
//                }, $constructor->getParameters()))
//                : [];
//            if (array_key_exists('app', $parameters)) {
//                $parameters['app'] = $application;
//            }
//
//            dd($parameters);
//
//            static::$constructorParametersCache[static::class] = !empty($constructor->getParameters())
//                ? array_merge(...array_map(static function (ReflectionParameter $param) {
//                    return [$param->getName() => $param->getDefaultValue()];
//                }, $constructor->getParameters()))
//                : [];
//        }
//
//        return static::$constructorParametersCache[static::class];
    }

    public function getTemplatePath()
    {
        return $this->environment->getGlobals()['app']['twig.options']['components']['path'];
    }

    public function withName(string $name): Component
    {
        $this->name = $name;

        return $this;
    }

    public function withEnvironment($enviroment): Component
    {
        $this->environment = $enviroment;

        return $this;
    }

    public function getContext($slots, $slot, $globalContext, $variables)
    {
        $context = [];

        if (method_exists($this, 'handle')) {
            $this->handle($globalContext['app']);
        }

        $variables = self::parseAttributes($variables);

        $context = array_merge($context, $globalContext);
        $context = array_merge($context, $slots);
        $context = array_merge($context, $variables);

        $context['slot'] = new ComponentSlot($slot);
        $context['this'] = $this;

        foreach ((new ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $context[$property->getName()] = &$this->{$property->getName()};
        }

        $context['attributes'] = new ComponentAttributeBag($variables);

        return $context;
    }

    public static function parseAttributes($data = [])
    {
        if (empty($data)) {
            return $data;
        }

        array_walk(
            $data,
            function (&$val, $key) use (&$desired_output) {
                $str                  = str_replace(' ', '', ucwords(str_replace('-', ' ', $key)));
                $str[0]               = strtolower($str[0]);
                $desired_output[$str] = $val;
            }
        );

        return $desired_output;
    }

    abstract public function template(): string;
}
