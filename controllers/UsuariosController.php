<?php
include_once './config/Database.php';
include_once './models/User.php';

class UsuariosController {
    private $db;
    private $requestMethod;
    private $userId;

    private $user;

    public function __construct($db, $requestMethod, $userId) {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;

        $this->user = new User($db);
    }

    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->userId) {
                    $response = $this->getUser($this->userId);
                } else {
                    $response = $this->getAllUsers();
                }
                break;
            case 'POST':
                if (isset($_GET['action']) && $_GET['action'] === 'login') {
                    $response = $this->loginUser();
                } else if((isset($_GET['action']) && $_GET['action'] === 'registro')) {
                    $response = $this->createUser();
                } else {

                }
                break;
            case 'PUT':
                $response = $this->updateUser($this->userId);
                break;
            case 'DELETE':
                $response = $this->deleteUser($this->userId);
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

    private function getAllUsers() {
        $result = $this->user->getAllUsers();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result->fetchAll(PDO::FETCH_ASSOC));
        return $response;
    }

    private function getUser($id) {
        $this->user->id = $id;
        $result = $this->user->getUserById();
        if ($result->rowCount() > 0) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result->fetch(PDO::FETCH_ASSOC));
        } else {
            $response = $this->notFoundResponse();
        }
        return $response;
    }

    private function createUser() {

        $input = json_decode(file_get_contents('php://input'), true);

        if (is_null($input)) {
            // Error en la decodificación del JSON
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = json_encode(['error' => 'Entrada JSON no válida']);
            return $response;
        }

        $this->user->nombre = $input['nombre'];
        $this->user->apellido = $input['apellido'];
        $this->user->telefono = $input['telefono'];
        $this->user->email = $input['email'];
        $this->user->contrasena = crypt($input['contrasena'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');

        $emailExists = $this->user->checkEmailExists();
        if ($emailExists->rowCount() > 0) {
            $response = $this->unprocessableEntityResponse("El email ya fue registrado.");
        } else {
            if ($this->user->createUser()) {
                $response['status_code_header'] = 'HTTP/1.1 201 Created';
                $response['body'] = json_encode([
                    'mensaje' => 'Usuario creado.'
                ]);
            } else {
                $response = $this->unprocessableEntityResponse("Error al crear el usuario.");
            }
        }

        return $response;
    }

   
    private function loginUser() {
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (is_null($input)) {
            // Error en la decodificación del JSON
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = json_encode(['error' => 'Entrada JSON no válida']);
            return $response;
        }
    
        if (!isset($input['email']) || !isset($input['password'])) {
            // Campos necesarios no proporcionados
            $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
            $response['body'] = json_encode(['error' => 'Faltan campos necesarios']);
            return $response;
        }
    
        // Asignar valores a la clase usuario
        $this->user->email = $input['email'];
        $this->user->contrasena = crypt($input['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
    
        // Ejecutar el método loginUser del modelo de usuario
        $result = $this->user->loginUser();
    
        if ($result->rowCount() > 0) {
               // Usuario encontrado
               $user = $result->fetch(PDO::FETCH_ASSOC);
               $response['status_code_header'] = 'HTTP/1.1 200 OK';
               $response['body'] = json_encode([
                   'estado' => 1,
                   'mensaje' => 'El usuario está registrado.',
                   'id' => $user['id'],
                   'nombre' => $user['nombre'],
                   'apellido' => $user['apellido']
               ]);
        } else {
            // Usuario no encontrado o datos incorrectos
            $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
            $response['body'] = json_encode(['error' => 'Datos incorrectos']);
        }
    
        return $response;
    }
    

    private function updateUser($id) {
        $result = $this->user->getUserById($id);
        if ($result->rowCount() == 0) {
            return $this->notFoundResponse();
        }

        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateUser($input)) {
            return $this->unprocessableEntityResponse();
        }

        $this->user->id = $id;
        $this->user->nombre = $input['nombre'];
        $this->user->apellido = $input['apellido'];
        $this->user->telefono = $input['telefono'];
        $this->user->email = $input['email'];
        $this->user->contrasena = $input['contrasena']; // Assuming password is already encrypted

        if ($this->user->updateUser()) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode([
                'mensaje' => 'Usuario actualizado.'
            ]);
        } else {
            $response = $this->unprocessableEntityResponse("Error al actualizar el usuario.");
        }

        return $response;
    }

    private function deleteUser($id) {
        $result = $this->user->getUserById($id);
        if ($result->rowCount() == 0) {
            return $this->notFoundResponse();
        }

        $this->user->id = $id;
        if ($this->user->deleteUser()) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode([
                'mensaje' => 'Usuario eliminado.'
            ]);
        } else {
            $response = $this->unprocessableEntityResponse("Error al eliminar el usuario.");
        }

        return $response;
    }

    // private function validateUser($input, $isLogin = false) {
    //     if (!isset($input['email'])) {
    //         return false;
    //     }

    //     if ($isLogin) {
    //         if (!isset($input['contrasena'])) {
    //             return false;
    //         }
    //     } else {
    //         if (!isset($input['nombre']) || !isset($input['apellido']) || !isset($input['telefono']) || !isset($input['contrasena'])) {
    //             return false;
    //         }
    //     }

    //     return true;
    // }

    private function unprocessableEntityResponse($message = "Datos invalidos.") {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'mensaje' => $message
        ]);
        return $response;
    }

    private function notFoundResponse($message = "Recurso no encontrado.") {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode([
            'mensaje' => $message
        ]);
        return $response;
    }
}
?>
