<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Notificacion extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'notificaciones';
    protected $dates = array('deleted_at');
    protected $visible = array('id', 'usuario_id', 'fecha', 'mensaje');
    protected $appends = array('mensaje', 'fecha');
    protected $with = array('notificable');

    public function notificable() {
        return $this->morphTo();
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

    public function getFechaAttribute() {
        return $this->notificable->updated_at;
    }

    public function getMensajeAttribute() {
        return $this->notificable->mensaje;
    }

}
