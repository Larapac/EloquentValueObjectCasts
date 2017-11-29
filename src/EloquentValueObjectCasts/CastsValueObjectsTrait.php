<?php

namespace Larapac\EloquentValueObjectCasts;

use Larapac\EloquentValueObjectCasts\Contracts\ValueObject;
use Illuminate\Contracts\Support\Arrayable;

trait CastsValueObjectsTrait
{
    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        $cast_to = $this->getCastObjectType($key);

        if (null !== $cast_to) {
            return $cast_to::fromNative($value);
        }

        return parent::castAttribute($key, $value);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $cast_to = $this->getCastObjectType($key);

        if (null !== $cast_to) {
            return $this->fromValueObject($key, $value, $cast_to);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($attributes as $key => $value) {
            $attributes[$key] = $value instanceof ValueObject ? $this->fromValueObjectToArray($key, $value) : $value;
        }

        return $attributes;
    }

    /**
     * Get a class for cast a model attribute.
     *
     * @param string $key
     * @return string|null
     */
    protected function getCastObjectType($key)
    {
        $cast_to = $this->getCasts()[$key] ?? null;

        return null !== $cast_to && is_a($cast_to, ValueObject::class, true) ? $cast_to : null;
    }

    /**
     * Convert ValueObject to array.
     *
     * @param string $key
     * @param \Larapac\EloquentValueObjectCasts\Contracts\ValueObject $value
     * @return array|mixed
     */
    protected function fromValueObjectToArray($key, $value)
    {
        return $value instanceof Arrayable ? $value->toArray() : $value->getNativeValue();
    }

    /**
     * Set a given attribute on the model from ValueObject.
     *
     * @param string $key
     * @param mixed $value
     * @param string $class
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function fromValueObject($key, $value, $class)
    {
        if (! ($value instanceof $class)) {
            throw new \InvalidArgumentException("Attribute '{$key}' must be an instance of {$class}");
        }

        $this->attributes[$key] = $value->getNativeValue();

        return $this;
    }
}
