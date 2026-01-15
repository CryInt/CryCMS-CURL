<?php
namespace CryCMS\DTO;

/**
 * @property array $defaultProperties
 */
abstract class CUrlDTO
{
    protected array $defaultProperties = [];

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function __set(string $name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return null;
    }

    public function __isset($name): bool
    {
        if (property_exists($this, $name)) {
            return true;
        }

        return false;
    }

    public function array(): array
    {
        $result = [];

        $properties = get_object_vars($this);
        foreach ($properties as $property => $value) {
            if ($property === 'defaultProperties') {
                continue;
            }

            if ($value instanceof CURLDTO) {
                $result[$property] = $value->array();
                continue;
            }

            $result[$property] = $value;
        }

        return $result;
    }
}