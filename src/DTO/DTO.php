<?php
namespace CryCMS\DTO;

abstract class DTO
{
    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function array(): array
    {
        $result = [];

        $properties = get_object_vars($this);
        foreach ($properties as $property => $value) {
            if ($value instanceof DTO) {
                $result[$property] = $value->array();
                continue;
            }

            $result[$property] = $value;
        }

        return $result;
    }
}