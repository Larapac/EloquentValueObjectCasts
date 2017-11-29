<?php

namespace Larapac\EloquentValueObjectCasts\Contracts;

interface ValueObject
{
    /**
     * Named constructor to make a Value Object from a native value.
     *
     * @param $value
     * @return mixed
     */
    public static function fromNative($value);

    /**
     * Compares two Value Objects and tells if they can be considered equal.
     *
     * @param ValueObject $object
     * @return bool
     */
    public function sameValueAs(ValueObject $object);

    /**
     * Returns the string representation of this Value Object.
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns the native value of this Value Object.
     *
     * @return mixed
     */
    public function getNativeValue();
}
