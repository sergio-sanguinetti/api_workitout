<?php
include_once './config/Database.php';
include_once './models/Solicitud.php';

class SolicitudesController {
    private $db;
    private $requestMethod;
    private $solicitudId;
    private $clienteId;
    private $servicioId;

    private $solicitud;

    public function __construct($db, $requestMethod, $solicitudId = null, $clienteId = null, $servicioId = null) {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->solicitudId = $solicitudId;
        $this->clienteId = $clienteId;
        $this->servicioId = $servicioId;

        $this->solicitud = new Solicitud($db);
    }

    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->solicitudId) {
                    $response = $this->getSolicitud($this->solicitudId);
                } else if ($this->clienteId) {
                    $response = $this->getSolicitudesByCliente($this->clienteId);
                } else if ($this->servicioId) {
                    $response = $this->getSolicitudesByServicio($this->servicioId);
                } else {
                    $response = $this->getAllSolicitudes();
                }
                break;
            case 'POST':
                $response = $this->createSolicitud();
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllSolicitudes() {
        $result = $this->solicitud->getAllSolicitudes();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result->fetchAll(PDO::FETCH_ASSOC));
        return $response;
    }

    private function getSolicitud($id) {
        $this->solicitud->idSolicitud = $id;
        $result = $this->solicitud->getSolicitudById();
        if ($result->rowCount() > 0) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result->fetch(PDO::FETCH_ASSOC));
        } else {
            $response = $this->notFoundResponse();
        }
        return $response;
    }

    private function getSolicitudesByCliente($idCliente) {
        $this->solicitud->idCliente = $idCliente;
        $result = $this->solicitud->getSolicitudesByCliente();
        if ($result->rowCount() > 0) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result->fetchAll(PDO::FETCH_ASSOC));
        } else {
            $response = $this->notFoundResponse();
        }
        return $response;
    }

    private function getSolicitudesByServicio($idServicio) {
        $this->solicitud->idServicio = $idServicio;
        $result = $this->solicitud->getSolicitudesByServicio();
        if ($result->rowCount() > 0) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result->fetchAll(PDO::FETCH_ASSOC));
        } else {
            $response = $this->notFoundResponse();
        }
        return $response;
    }

    private function createSolicitud() {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        if (!$this->validateSolicitud($input)) {
            return $this->unprocessableEntityResponse('Todos los campos son obligatorios');
        }

        $this->solicitud->idCliente = $input['idCliente'];
        $this->solicitud->idServicio = $input['idServicio'];
        $this->solicitud->descripcion = $input['descripcionServicio'];
        $this->solicitud->ubicacion = $input['direccion'];
        $this->solicitud->lat_long = $input['lat_long'];
        $this->solicitud->fechaHoraAtencion = $input['fechaHoraAtencion'];
        $this->solicitud->precio = $input['precio'];

        if ($this->solicitud->createSolicitud()) {
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode(['mensaje' => 'Solicitud registrada correctamente','estado' => 1]);
        } else {
            $response = $this->unprocessableEntityResponse('Error al registrar la solicitud');
        }

        return $response;
    }

    private function validateSolicitud($input) {
        return isset($input['idCliente'], $input['idServicio'], $input['descripcionServicio'], $input['direccion'], $input['fechaHoraAtencion'], $input['precio']);
    }

    private function unprocessableEntityResponse($message = "Datos invalidos.") {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode(['mensaje' => $message]);
        return $response;
    }

    private function notFoundResponse($message = "Recurso no encontrado.") {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode(['mensaje' => $message]);
        return $response;
    }
}
?>
