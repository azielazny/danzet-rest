<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 15.02.2018
 * Time: 10:21
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->group('/services', function () {
    $this->get('', function (Request $request, Response $response) {
        try {
            $con = $this->db;
            $sql = "SELECT * FROM services";
            $result = null;
            foreach ($con->query($sql) as $row) {
                $result[] = $row;
            }

            if ($result) {
                $this->logger->debug('Services list ', ['success']);
                return $response->withJson(array('status' => 'true', 'result' => $result), 200);
            } else {
                $this->logger->info('Services list ', ['status' => 'Services Not Found']);
                return $response->withJson(array('status' => 'Services Not Found'), 200);
            }
        } catch (\Exception $ex) {
            $this->logger->warning('Services list ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->post('', function (Request $request, Response $response) {

        try {
            $con = $this->db;
            $sql = "INSERT INTO `services`(name, code, net_price, gross_price, vat, info, unit_id, modification_date) 
                    VALUES (:name, :code, :net_price, :gross_price, :vat, :info, :unit_id, :modification_date)";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':name' => $request->getParam('name'),
                ':code' => $request->getParam('code'),
                ':net_price' => $request->getParam('net_price'),
                ':gross_price' => $request->getParam('gross_price'),
                ':vat' => $request->getParam('vat'),
                ':info' => $request->getParam('info'),
                ':unit_id' => $request->getParam('unit_id'),
                ':modification_date' => date('Y-m-d H:i:s', time())
            );
            $result = $pre->execute($values);
            $lastId = $con->lastInsertId();
            $this->logger->warning('Services added ', ['success']);

            return $response->withJson(array('status' => 'Services Created', 'id' => $lastId), 200);

        } catch (\Exception $ex) {
            $this->logger->warning('Services not added exception', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->get('/{service_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $service_id = $request->getAttribute('service_id');
            $con = $this->db;
            $sql = "SELECT * FROM services WHERE service_id = :service_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':service_id' => $service_id
            );
            $pre->execute($values);
            $result = $pre->fetch();
            if ($result) {
                $this->logger->info('Read the service data by service id', ['id' => $service_id]);
                return $response->withJson(array('status' => 'true', 'result' => $result), 200);
            } else {
                $this->logger->info('Not found database exception to service id', ['id' => $service_id]);
                return $response->withJson(array('status' => 'Service Not Found'), 200);
            }
        } catch (\Exception $ex) {
            $this->logger->warning('Read the service data from Service table. ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });


    $this->put('/{service_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $service_id = $request->getAttribute('service_id');
            $con = $this->db;
            $sql = "UPDATE services SET name=:name, code=:code, net_price=:net_price, gross_price=:gross_price, vat=:vat, 
                    info=:info, unit_id=:unit_id, modification_date=:modification_date WHERE service_id = :service_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':name' => $request->getParam('name'),
                ':code' => $request->getParam('code'),
                ':net_price' => $request->getParam('net_price'),
                ':gross_price' => $request->getParam('gross_price'),
                ':vat' => $request->getParam('vat'),
                ':info' => $request->getParam('info'),
                ':unit_id' => $request->getParam('unit_id'),
                ':modification_date' => date('Y-m-d H:i:s', time()),
                ':service_id' => $service_id
            );
            $result = $pre->execute($values);
            if ($result) {
                $this->logger->info('Update the service data of the services table', ['service_id' => $service_id]);
                return $response->withJson(array('status' => 'Service Updated'), 200);
            } else {
                $this->logger->info('Not found Exception for update the Service data of the Service table',
                    ['service_id' => $service_id]);
                return $response->withJson(array('status' => 'Service Not Found'), 200);
            }

        } catch (\Exception $ex) {
            $this->logger->warning('Service update error ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->delete('/{service_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $service_id = $request->getAttribute('service_id');
            $con = $this->db;
            $sql = "DELETE FROM services WHERE service_id = :service_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':service_id' => $service_id
            );
            $result = $pre->execute($values);
            if ($result) {
                $this->logger->info('Service deleted', ['service_id' => $service_id]);
                return $response->withJson(array('status' => 'Service Deleted'), 200);
            } else {
                $this->logger->info('Not found ecxeption for the service delete from the Services table',
                    ['service_id' => $service_id]);
                return $response->withJson(array('status' => 'Service Not Found'), 200);
            }

        } catch (\Exception $ex) {
            $this->logger->warning('Service deleted error ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

});
