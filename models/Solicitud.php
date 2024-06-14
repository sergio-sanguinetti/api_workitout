<?php
class Solicitud {
    private $conn;
    private $table = 'solicitud';

    public $idSolicitud;
    public $idCliente;
    public $idServicio;
    public $descripcion;
    public $ubicacion;
    public $lat_long;
    public $fechaHoraSolicitud;
    public $fechaHoraAtencion;
    public $precio;
    public $estado;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllSolicitudes() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getSolicitudById() {
        $query = "SELECT * FROM " . $this->table . " WHERE idSolicitud = :idSolicitud";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idSolicitud', $this->idSolicitud);
        $stmt->execute();
        return $stmt;
    }

    public function getSolicitudesByCliente() {
        $query = "SELECT * FROM " . $this->table . " WHERE idCliente = :idCliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idCliente', $this->idCliente);
        $stmt->execute();
        return $stmt;
    }

    public function getSolicitudesByServicio() {
        $query = "SELECT * FROM " . $this->table . " WHERE idServicio = :idServicio";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idServicio', $this->idServicio);
        $stmt->execute();
        return $stmt;
    }

    public function createSolicitud() {
        $query = "INSERT INTO " . $this->table . " (idCliente, idServicio, descripcion, ubicacion,lat_long, fechaHoraSolicitud, fechaHoraAtencion, precio, estado) VALUES (:idCliente, :idServicio, :descripcion, :ubicacion,:lat_long, NOW(), :fechaHoraAtencion, :precio, 1)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idCliente', $this->idCliente);
        $stmt->bindParam(':idServicio', $this->idServicio);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':ubicacion', $this->ubicacion);
        $stmt->bindParam(':lat_long', $this->lat_long);
        $stmt->bindParam(':fechaHoraAtencion', $this->fechaHoraAtencion);
        $stmt->bindParam(':precio', $this->precio);
        return $stmt->execute();
    }
}
?>
