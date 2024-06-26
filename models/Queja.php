<?php
class Queja {
    private $conn;
    private $table = 'quejas';

    public $idQueja;
    public $idCliente;
    public $idSolicitud;
    public $motivo;
    public $descripcion;
    public $imagen;
    public $fechaHoraRegistro;

    public function __construct($db) {
        $this->conn = $db;
    } 

    public function createQueja() {
        $query = "INSERT INTO " . $this->table . " (idCliente, idSolicitud, motivo, descripcion, imagen, fechaHoraRegistro) VALUES (:idCliente, :idSolicitud, :motivo, :descripcion, :imagen, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idCliente', $this->idCliente);
        $stmt->bindParam(':idSolicitud', $this->idSolicitud);
        $stmt->bindParam(':motivo', $this->motivo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':imagen', $this->imagen);
        return $stmt->execute();
    }
}
?>