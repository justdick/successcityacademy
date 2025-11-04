<?php

class Subject {
    public $id;
    public $name;
    public $created_at;

    public function __construct($id = null, $name = null, $created_at = null) {
        $this->id = $id;
        $this->name = $name;
        $this->created_at = $created_at;
    }

    /**
     * Convert subject object to array
     * 
     * @return array Subject data as associative array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at
        ];
    }
}
