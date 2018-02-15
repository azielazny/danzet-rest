<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 15.02.2018
 * Time: 15:59
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->group('/products', function () {
    $this->get('', function (Request $request, Response $response) {
        try {
            $con = $this->db;
            $sql = "SELECT * FROM products";
            $result = null;
            foreach ($con->query($sql) as $row) {
                $result[] = $row;
            }

            if ($result) {
                $this->logger->debug('Products list ', ['success']);
                return $response->withJson(array('status' => 'true', 'result' => $result), 200);
            } else {
                $this->logger->info('Warehouses list ', ['status' => 'Products Not Found']);
                return $response->withJson(array('status' => 'Products Not Found'), 200);
            }
        } catch (\Exception $ex) {
            $this->logger->warning('Products list ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->post('', function (Request $request, Response $response) {

        try {
            $con = $this->db;
            $sql = "INSERT INTO `products`(name, code, info, unit_id,  modification_date) 
                    VALUES (:name, :code, :info, :unit_id, :modification_date)";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':name' => $request->getParam('name'),
                ':code' => $request->getParam('code'),
                ':info' => $request->getParam('info'),
                ':unit_id' => $request->getParam('unitId'),
                ':modification_date' => date('Y-m-d H:i:s', time())
            );
            $result = $pre->execute($values);
            $lastId = $con->lastInsertId();
            $this->logger->warning('Product added ', ['success']);

            return $response->withJson(array('status' => 'Product Created', 'id' => $lastId), 200);

        } catch (\Exception $ex) {
            $this->logger->warning('Product not added exception', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->get('/{product_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $product_id = $request->getAttribute('product_id');
            $con = $this->db;
            $sql = "SELECT * FROM products WHERE product_id = :product_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':product_id' => $product_id
            );
            $pre->execute($values);
            $result = $pre->fetch();
            if ($result) {
                $this->logger->info('Read the product data by product id', ['id' => $product_id]);
                return $response->withJson(array('status' => 'true', 'result' => $result), 200);
            } else {
                $this->logger->info('Not found database exception to product id', ['id' => $product_id]);
                return $response->withJson(array('status' => 'Product Not Found'), 200);
            }
        } catch (\Exception $ex) {
            $this->logger->warning('Read the product data from Products table. ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });


    $this->put('/{product_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $product_id = $request->getAttribute('product_id');
            $con = $this->db;
            $sql = "UPDATE products SET name=:name, code=:code, info=:info, unit_id=:unit_id, 
                    modification_date=:modification_date WHERE product_id = :product_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':name' => $request->getParam('name'),
                ':code' => $request->getParam('code'),
                ':info' => $request->getParam('info'),
                ':unit_id' => $request->getParam('unit_id'),
                ':modification_date' => date('Y-m-d H:i:s', time()),
                ':product_id' => $product_id
            );
            $result = $pre->execute($values);
            if ($result) {
                $this->logger->info('Update the product data of the products table', ['product_id' => $product_id]);
                return $response->withJson(array('status' => 'Product Updated'), 200);
            } else {
                $this->logger->info('Not found Exception for update the product data of the Products table',
                    ['product_id' => $product_id]);
                return $response->withJson(array('status' => 'Product Not Found'), 200);
            }

        } catch (\Exception $ex) {
            $this->logger->warning('Product update error ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->delete('/{product_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $product_id = $request->getAttribute('product_id');
            $con = $this->db;
            $sql = "DELETE FROM products WHERE product_id = :product_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':product_id' => $product_id
            );
            $result = $pre->execute($values);
            if ($result) {
                $this->logger->info('Product deleted', ['product_id' => $product_id]);
                return $response->withJson(array('status' => 'Product Deleted'), 200);
            } else {
                $this->logger->info('Not found ecxeption for the product delete from the Products table',
                    ['product_id' => $product_id]);
                return $response->withJson(array('status' => 'Product Not Found'), 200);
            }

        } catch (\Exception $ex) {
            $this->logger->warning('Product deleted error ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

});
