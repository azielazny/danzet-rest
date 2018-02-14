<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 14.02.2018
 * Time: 17:25
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->group('/clients', function () {
    $this->get('', function (Request $request, Response $response) {
        try {
            $con = $this->db;
            $sql = "SELECT * FROM clients";
            $result = null;
            foreach ($con->query($sql) as $row) {
                $result[] = $row;
            }

            if ($result) {
                $this->logger->debug('Clients list ', ['success']);
                return $response->withJson(array('status' => 'true', 'result' => $result), 200);
            } else {
                $this->logger->info('Clients list ', ['status' => 'Client Not Found']);
                return $response->withJson(array('status' => 'Client Not Found'), 422);
            }
        } catch (\Exception $ex) {
            $this->logger->warning('Clients list ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->post('', function (Request $request, Response $response) {

        try {
            $con = $this->db;
            $sql = "INSERT INTO `clients`(firstname, lastname, zip, city, street, house_number, apartment_number, phone, 
                    email, company, nip, `modification_date`) VALUES (:firstname, :lastname, :zip, :city, :street, 
                    :house_number, :apartment_number, :phone, :email, :company, :nip, :modification_date)";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':firstname' => $request->getParam('firstname'),
                ':lastname' => $request->getParam('lastname'),
                ':zip' => $request->getParam('zip'),
                ':city' => $request->getParam('city'),
                ':street' => $request->getParam('street'),
                ':house_number' => $request->getParam('house_number'),
                ':apartment_number' => $request->getParam('apartment_number'),
                ':phone' => $request->getParam('phone'),
                ':email' => $request->getParam('email'),
                ':company' => $request->getParam('company'),
                ':nip' => $request->getParam('nip'),
                ':modification_date' => date('Y-m-d H:i:s', time())
            );
            $result = $pre->execute($values);
            $lastId = $con->lastInsertId();
            $this->logger->warning('Client added ', ['success']);

            return $response->withJson(array('status' => 'Client Created', 'id' => $lastId), 200);

        } catch (\Exception $ex) {
            $this->logger->warning('Client not added exception', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->get('/{client_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $client_id = $request->getAttribute('client_id');
            $con = $this->db;
            $sql = "SELECT * FROM clients WHERE client_id = :client_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':client_id' => $client_id
            );
            $pre->execute($values);
            $result = $pre->fetch();
            if ($result) {
                $this->logger->info('Read client number id equal to ', ['id' => $client_id]);
                return $response->withJson(array('status' => 'true', 'result' => $result), 200);
            } else {
                $this->logger->info('Not found database exception to client id', ['id' => $client_id]);
                return $response->withJson(array('status' => 'Client Not Found'), 422);
            }
        } catch (\Exception $ex) {
            $this->logger->warning('Read client data from Cars table. ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });


    $this->put('/{client_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $client_id = $request->getAttribute('client_id');
            $con = $this->db;
            $sql = "UPDATE clients SET firstname=:firstname, lastname=:lastname, zip=:zip, city=:city, street=:street, 
                    house_number=:house_number, apartment_number=:apartment_number, phone=:phone, email=:email, 
                    company=:company, nip=:nip, modification_date=:modification_date WHERE client_id = :client_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':firstname' => $request->getParam('firstname'),
                ':lastname' => $request->getParam('lastname'),
                ':zip' => $request->getParam('zip'),
                ':city' => $request->getParam('city'),
                ':street' => $request->getParam('street'),
                ':house_number' => $request->getParam('house_number'),
                ':apartment_number' => $request->getParam('apartment_number'),
                ':phone' => $request->getParam('phone'),
                ':email' => $request->getParam('email'),
                ':company' => $request->getParam('company'),
                ':nip' => $request->getParam('nip'),
                ':modification_date' => date('Y-m-d H:i:s', time()),
                ':client_id' => $client_id
            );
            $result = $pre->execute($values);
            if ($result) {
                $this->logger->info('Update Client data in Cars table', ['client_id' => $client_id]);
                return $response->withJson(array('status' => 'Client Updated'), 200);
            } else {
                $this->logger->info('Not found Exception for update Client data in Cars table',
                    ['client_id' => $client_id]);
                return $response->withJson(array('status' => 'Client Not Found'), 422);
            }

        } catch (\Exception $ex) {
            $this->logger->warning('Client update error ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->delete('/{client_id:[0-9]+}', function (Request $request, Response $response) {
        try {
            $client_id = $request->getAttribute('client_id');
            $con = $this->db;
            $sql = "DELETE FROM clients WHERE client_id = :client_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':client_id' => $client_id
            );
            $result = $pre->execute($values);
            if ($result) {
                $this->logger->info('Client deleted', ['client_id' => $client_id]);
                return $response->withJson(array('status' => 'Client Deleted'), 200);
            } else {
                $this->logger->info('Not found ecxeption for car delete from Clients table',
                    ['client_id' => $client_id]);
                return $response->withJson(array('status' => 'Client Not Found'), 422);
            }

        } catch (\Exception $ex) {
            $this->logger->warning('Client deleted error ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }

    });

    $this->get('/search/{phrase}', function (Request $request, Response $response) {
        try {
            $phrase = "%".$request->getAttribute('phrase')."%";
            $con = $this->db;
            $sql = "SELECT * FROM clients WHERE lastname LIKE :client_id OR phone LIKE :phone OR email LIKE :email";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':client_id' => $phrase,
                ':phone' => $phrase,
                ':email' => $phrase
            );
            $pre->execute($values);
            $result = null;
            foreach ($pre->fetchAll(PDO::FETCH_ASSOC) as $key=>$value) {
                $result[$key] = $value;
            }

            if ($result) {
                $this->logger->info('Search client by phrase ', ['phrase' => $phrase]);
                return $response->withJson(array('status' => 'true', 'result' => $result), 200);
            } else {
                $this->logger->info('Not found client by phrase', ['id' => $phrase]);
                return $response->withJson(array('status' => 'Client Not Found', 'result' =>[]), 200);
            }
        } catch (\Exception $ex) {
            $this->logger->warning('Search client by phrase ', ['error' => $ex->getMessage()]);
            return $response->withJson(array('error' => $ex->getMessage()), 422);
        }
    });
});
