<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Ajuste extends Eloquent {

    //protected $table = 'ajustes';
    protected $visible = array('id', 'key', 'value_type', 'description');
    protected $appends = array('value');

    public function getValueAttribute() {
        return $this->value_type.'_value';
    }

    public function setValueAttribute($value) {
        $this->attributes[$this->value_type.'_value'] = $value;
    }

}