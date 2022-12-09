<?php

use Adianti\Database\TRecord;

class TSSFacilRecord extends TRecord
{
    /**
     * Fill the Active Record properties from an indexed array
     * @param $data An indexed array containing the object properties
     */
    public function fromArray($data)
    {
        if (count($this->attributes) > 0)
        {
            $pk = $this->getPrimaryKey();
            foreach ($data as $key => $value)
            {
                // set just attributes defined by the addAttribute()
                if ((in_array(strtolower($key), array_map('strtolower', $this->attributes)) AND is_string($key)) OR (strtolower($key) === strtolower($pk)))
                {
                    $this->data[strtolower($key)] = $data[$key];
                }
            }
        }
        else
        {
            foreach ($data as $key => $value)
            {
                $this->data[strtolower($key)] = $data[$key];
            }
        }
    }
    
    /**
     * Return the Active Record properties as an indexed array
     * @param $filter_attributes Array of attributes to be returned.
     * @return An indexed array containing the object properties
     */
    public function toArray( $filter_attributes = null )
    {
        $attributes = $filter_attributes ? $filter_attributes : $this->attributes;
        
        $data = array();
        if (count($attributes) > 0)
        {
            $pk = $this->getPrimaryKey();
            if (!empty($this->data))
            {
                foreach ($this->data as $key => $value)
                {
                    if ((in_array(strtolower($key), array_map('strtolower', $this->attributes)) AND is_string($key)) OR (strtolower($key) === strtolower($pk)))
                    {
                        $data[$key] = $this->data[$key];
                    }
                }
            }
        }
        else
        {
            $data = $this->data;
        }
        return $data;
    }
}