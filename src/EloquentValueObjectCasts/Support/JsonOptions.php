<?php

namespace Larapac\EloquentValueObjectCasts\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Larapac\EloquentValueObjectCasts\Contracts\ValueObject;

class JsonOptions implements ValueObject, Jsonable, JsonSerializable, Arrayable
{
    /**
     * Data in array format.
     *
     * @var array
     */
    protected $value;

    /**
     * Defaults values and scheme of data.
     *
     * @var array
     */
    protected $blueprint = [];

    /**
     * Use blueprint as scheme.
     *
     * :TODO: Implement strict mode
     *
     * @var bool
     */
    protected $strict = false;

    /**
     * Constructor.
     *
     * @param $value
     */
    public function __construct(array $value = null)
    {
        $this->value = $this->mergeDefaults($value);
    }

    /**
     * Named constructor to make a Value Object from a native value.
     *
     * @param $value
     * @return mixed
     */
    public static function fromNative($value)
    {
        $parameters = json_decode((string) $value, true);

        return new static($parameters);
    }

    /**
     * Compares two Value Objects and tells if they can be considered equal.
     *
     * @param ValueObject $object
     * @return bool
     */
    public function sameValueAs(ValueObject $object)
    {
        return (string) $this === (string) $object;
    }

    /**
     * Returns the native value of this Value Object.
     *
     * @return mixed
     */
    public function getNativeValue()
    {
        $value = $this->extractDefaults($this->value);
        return [] === $value || null === $value ? null : json_encode($value);
    }

    /**
     * Returns the string representation of this Value Object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->value, $options);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->value;
    }

    protected function mergeDefaults($value)
    {
        return $this->mergeRecursive($value ?? [], $this->blueprint ?? []);
    }

    protected function extractDefaults($value)
    {
        return $this->diffRecursive($value ?? [], $this->blueprint ?? []);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->hasAccessor($name)
            ? call_user_func([$this, $this->getAccessorMethod($name)])
            : $this->value[$name] ?? null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->hasMutator($name)
            ? call_user_func([$this, $this->getMutatorMethod($name)], $value)
            : $this->value[$name] = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->value);
    }

    protected function getAccessorMethod($name)
    {
        return camel_case("get_{$name}_option");
    }

    protected function getMutatorMethod($name)
    {
        return camel_case("set_{$name}_option");
    }

    public function hasAccessor($name)
    {
        return method_exists($this, $this->getAccessorMethod($name));
    }

    public function hasMutator($name)
    {
        return method_exists($this, $this->getMutatorMethod($name));
    }

    protected function diffRecursive(array $array1, array $array2)
    {
        foreach ($array1 as $key => $value) {
            if (! array_key_exists($key, $array2)) {
                continue;
            }

            if ($value === $array2[$key]) {
                unset($array1[$key]);
                continue;
            }

            if ($this->isNestedValue($array2[$key])) {
                $array1[$key] = $this->diffRecursive($value, $array2[$key]);
            }
        }

        return $array1;
    }

    protected function mergeRecursive(array $array1, array $array2)
    {
        foreach ($array2 as $key => $value) {
            if (! array_key_exists($key, $array1)) {
                $array1[$key] = $value;
                continue;
            }

            if ($value === $array1[$key]) {
                continue;
            }

            if ($this->isNestedValue($value)) {
                $array1[$key] = $this->mergeRecursive($array1[$key], $value);
            }
        }

        return $array1;
    }

    protected function isNestedValue($value)
    {
        if (!is_array($value)) {
            return false;
        }

        $keys = array_keys($value);

        return array_keys($keys) !== $keys;
    }
}
