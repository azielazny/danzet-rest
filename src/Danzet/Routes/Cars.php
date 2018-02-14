<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 08.02.2018
 * Time: 21:22
 */

use \Danzet\Libs\JwtAuth as JwtAuth;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->group('/cars', function () {
    $this->get('', function (Request $request, Response $response) {
        try {
            $con = $this->db;
            $sql = "SELECT * FROM cars";
            $result = null;
            foreach ($con->query($sql) as $row) {
                $result[] = $row;
            }

            if ($result) {
                $this->logger->debug('Cars list ', ['success']);
                return $response->withJson(array('status' => 'true', 'result' => $result), 200);
            } else {
                $this->logger->info('Cars list ', ['status' => 'Cars Not Found']);
                return $response->withJson(array('status' => 'Cars Not Found'), 422);
            }
        } catch (\Exception $ex) {
            $this->logger->warning('Cars list ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });
//});
//$app->group('/car', function () {
    $this->post('', function (Request $request, Response $response) {

        try {
            $con = $this->db;
            $sql = "INSERT INTO `cars`(`car_id`, `brand`, `model`, `production_year`, `vin`, `registration_number`, 
                                        `registration_date`, `car_version`, `capacity`, `engine_power`, `fuel`, 
                                        `dr_series`, `course`, `date_added`, `info`, `client_id`, `modification_date`) 
                                        VALUES (:car_id, :brand, :model, :production_year, :vin, :registration_number, 
                                        :registration_date, :car_version, :capacity, :engine_power, :fuel, :dr_series, 
                                        :course, :date_added, :info, :client_id, :modification_date)";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':car_id' => $request->getParam('car_id'),
                ':brand' => $request->getParam('brand'),
                ':model' => $request->getParam('model'),
                ':production_year' => $request->getParam('production_year'),
                ':vin' => $request->getParam('vin'),
                ':registration_number' => $request->getParam('registration_number'),
                ':registration_date' => $request->getParam('registration_date'),
                ':car_version' => $request->getParam('car_version'),
                ':capacity' => $request->getParam('capacity'),
                ':engine_power' => $request->getParam('engine_power'),
                ':fuel' => $request->getParam('fuel'),
                ':dr_series' => $request->getParam('dr_series'),
                ':course' => $request->getParam('course'),
                ':date_added' => $request->getParam('date_added'),
                ':info' => $request->getParam('info'),
                ':client_id' => $request->getParam('client_id'),
                ':modification_date' => date('Y-m-d H:i:s', time())
//                'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT)
            );
            $result = $pre->execute($values);
            $lastId = $con->lastInsertId();
            $this->logger->warning('Car added ', ['success']);

            return $response->withJson(array('status' => 'Car Created', 'id' => $lastId), 200);

        } catch (\Exception $ex) {
            $this->logger->warning('Cars not added exception', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->get('/{car_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $car_id = $request->getAttribute('car_id');
            $con = $this->db;
            $sql = "SELECT * FROM cars WHERE car_id = :car_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':car_id' => $car_id
            );
            $pre->execute($values);
            $result = $pre->fetch();
            if ($result) {
                $this->logger->info('Read car number id equal to ', ['id' => $car_id]);
                return $response->withJson(array('status' => 'true', 'result' => $result), 200);
            } else {
                $this->logger->info('Not found database exception to car id', ['id' => $car_id]);
                return $response->withJson(array('status' => 'Car Not Found'), 422);
            }
        } catch (\Exception $ex) {
            $this->logger->warning('Read car data from Cars table. ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });


    $this->put('/{car_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $car_id = $request->getAttribute('car_id');
            $con = $this->db;
            $sql = "UPDATE cars SET brand=:brand, model=:model, production_year=:production_year, vin=:vin, 
                    registration_number=:registration_number, registration_date=:registration_date, 
                    car_version=:car_version, capacity=:capacity, engine_power=:engine_power, fuel=:fuel, 
                    dr_series=:dr_series, course=:course, date_added=:date_added, info=:info, client_id=:client_id, 
                    modification_date=:modification_date WHERE car_id = :car_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':brand' => $request->getParam('brand'),
                ':model' => $request->getParam('model'),
                ':production_year' => $request->getParam('production_year'),
                ':vin' => $request->getParam('vin'),
                ':registration_number' => $request->getParam('registration_number'),
                ':registration_date' => $request->getParam('registration_date'),
                ':car_version' => $request->getParam('car_version'),
                ':capacity' => $request->getParam('capacity'),
                ':engine_power' => $request->getParam('engine_power'),
                ':fuel' => $request->getParam('fuel'),
                ':dr_series' => $request->getParam('dr_series'),
                ':course' => $request->getParam('course'),
                ':date_added' => $request->getParam('date_added'),
                ':info' => $request->getParam('info'),
                ':client_id' => $request->getParam('client_id'),
                ':modification_date' => date('Y-m-d H:i:s', time()),
                ':car_id' => $car_id
            );
            $result = $pre->execute($values);
            if ($result) {
                $this->logger->info('Update Car data in Cars table', ['car_id' => $car_id]);
                return $response->withJson(array('status' => 'Car Updated'), 200);
            } else {
                $this->logger->info('Not found Exception for update Car data in Cars table', ['car_id' => $car_id]);
                return $response->withJson(array('status' => 'Car Not Found'), 422);
            }

        } catch (\Exception $ex) {
            $this->logger->warning('Car update error ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->delete('/{car_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $car_id = $request->getAttribute('car_id');
            $con = $this->db;
            $sql = "DELETE FROM cars WHERE car_id = :car_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':car_id' => $car_id
            );
            $result = $pre->execute($values);
            if ($result) {
                $this->logger->info('Car deleted', ['car_id' => $car_id]);
                return $response->withJson(array('status' => 'Car Deleted'), 200);
            } else {
                $this->logger->info('Not found ecxeption for car delete from Cars table', ['car_id' => $car_id]);
                return $response->withJson(array('status' => 'Car Not Found'), 422);
            }

        } catch (\Exception $ex) {
            $this->logger->warning('Car deleted error ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });


    //JWT AUTH LOGIN TEST to remove later
    $this->get('/xx/{car_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $car_id = $request->getAttribute('car_id');
            $con = $this->db;
            $sql = "SELECT * FROM cars WHERE car_id = :car_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':car_id' => $car_id
            );
            $pre->execute($values);
            $result = $pre->fetch(\PDO::FETCH_OBJ);

            //JWT------------
            // If user exist
            if ($result) {
                // If password is correct
//                if (password_verify($pass, $query->PASSWORD)) {
                // Create a new resource

                $data['token'] = JwtAuth::getToken($result->car_id, $result->vin);
                // Return the resource
//                    $response = $response->withHeader('Content-Type','application/json');
//                    $response = $response->withStatus(201);
//                    $response = $response->withJson($data);
                return $response->withJson($data, 201);

//                    return $response;
//                } else {
//                    // Password wrong
//                    die("Error: The password you have entered is wrong.");
//                }
            } else {
                // Username wrong
                die("Error: The user specified does not exist.");
            }


        } catch (\Exception $ex) {
            $this->logger->warning('Car xx error ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });


});











