<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 15.02.2018
 * Time: 15:17
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->group('/warehouses', function () {
    $this->get('', function (Request $request, Response $response) {
        try {
            $con = $this->db;
            $sql = "SELECT * FROM warehouses";
            $result = null;
            foreach ($con->query($sql) as $row) {
                $result[] = $row;
            }

            if ($result) {
                $this->logger->debug('Warehouses list ', ['success']);
                return $response->withJson(array('status' => 'true', 'result' => $result), 200);
            } else {
                $this->logger->info('Warehouses list ', ['status' => 'Warehouses Not Found']);
                return $response->withJson(array('status' => 'Warehouses Not Found'), 200);
            }
        } catch (\Exception $ex) {
            $this->logger->warning('Warehouses list ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->post('', function (Request $request, Response $response) {

        try {
            $con = $this->db;
            $sql = "INSERT INTO `warehouses`(info, name, modification_date) 
                    VALUES (:name, :info, :modification_date)";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':info' => $request->getParam('info'),
                ':name' => $request->getParam('name'),
                ':modification_date' => date('Y-m-d H:i:s', time())
            );
            $result = $pre->execute($values);
            $lastId = $con->lastInsertId();
            $this->logger->warning('Warehouse added ', ['success']);

            return $response->withJson(array('status' => 'Warehouse Created', 'id' => $lastId), 200);

        } catch (\Exception $ex) {
            $this->logger->warning('Warehouse not added exception', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->get('/{warehouse_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $warehouse_id = $request->getAttribute('warehouse_id');
            $con = $this->db;
            $sql = "SELECT * FROM warehouses WHERE warehouse_id = :warehouse_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':warehouse_id' => $warehouse_id
            );
            $pre->execute($values);
            $result = $pre->fetch();
            if ($result) {
                $this->logger->info('Read the warehouse data by warehouse id', ['id' => $warehouse_id]);
                return $response->withJson(array('status' => 'true', 'result' => $result), 200);
            } else {
                $this->logger->info('Not found database exception to warehouse id', ['id' => $warehouse_id]);
                return $response->withJson(array('status' => 'Warehouse Not Found'), 200);
            }
        } catch (\Exception $ex) {
            $this->logger->warning('Read the warehouse data from Warehouses table. ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });


    $this->put('/{warehouse_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $warehouse_id = $request->getAttribute('warehouse_id');
            $con = $this->db;
            $sql = "UPDATE warehouses SET info=:info, name=:name, modification_date=:modification_date WHERE warehouse_id = :warehouse_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':info' => $request->getParam('info'),
                ':name' => $request->getParam('name'),
                ':modification_date' => date('Y-m-d H:i:s', time()),
                ':warehouse_id' => $warehouse_id
            );
            $result = $pre->execute($values);
            if ($result) {
                $this->logger->info('Update the warehouse data of the warehouses table', ['warehouse_id' => $warehouse_id]);
                return $response->withJson(array('status' => 'Warehouse Updated'), 200);
            } else {
                $this->logger->info('Not found Exception for update the warehouse data of the Warehouses table',
                    ['warehouse_id' => $warehouse_id]);
                return $response->withJson(array('status' => 'Warehouse Not Found'), 422);
            }

        } catch (\Exception $ex) {
            $this->logger->warning('Warehouse update error ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->delete('/{warehouse_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $warehouse_id = $request->getAttribute('warehouse_id');
            $con = $this->db;
            $sql = "DELETE FROM warehouses WHERE warehouse_id = :warehouse_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':warehouse_id' => $warehouse_id
            );
            $result = $pre->execute($values);
            if ($result) {
                $this->logger->info('Warehouse deleted', ['warehouse_id' => $warehouse_id]);
                return $response->withJson(array('status' => 'Warehouse Deleted'), 200);
            } else {
                $this->logger->info('Not found ecxeption for the warehouse delete from the Warehouses table',
                    ['warehouse_id' => $warehouse_id]);
                return $response->withJson(array('status' => 'Warehouse Not Found'), 200);
            }

        } catch (\Exception $ex) {
            $this->logger->warning('Warehouse deleted error ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

});
