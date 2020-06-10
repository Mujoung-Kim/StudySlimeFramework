<?php

declare(strict_types = 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../includes/DbOperations.php';
// require __DIR__ . '/../includes/DbConnect.php';

$app = new \Slim\App([
    'settings'=>[
        'displayErrorDetails'=>true
    ]
]);

// $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
//     $name = $args['name'];
// 	$response->getBody()->write("Hello, $name");
// 	$db = new DbConnect;

// 	if($db->connect() != null) {
// 		echo 'Connection Successfull';

// 	}
	
// 	return $response;
	
// });

/*
	endpoint: createuser
	parameters: eamil, password, name, school
	method: POST
*/
$app->post('/createuser', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('email', 'password', 'name', 'school'), $request, $response)){
    	$request_data = $request->getParsedBody(); 

        $request_data = [
            'email' => 'asdf',
            'password' => 'zxcv',
            'name' => 'qwer',
            'school' => 'siwon',
        ];

        $email = $request_data['email']; 
        $password = $request_data['password'];
        $name = $request_data['name'];
        $school = $request_data['school']; 

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations; 

        $result = $db->createUser($email, $hash_password, $name, $school);
        
        if($result == USER_CREATED) {

            $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'User created successfully';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);

        }else if($result == USER_FAILURE) {

            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    

        }else if($result == USER_EXISTS) {
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'User Already Exists';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
						->withStatus(422);    
						
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
		->withStatus(422);    
		
});

$app->post('/userlogin', function(Request $request, Response $response) {
    if(!haveEmptyParameters(array('email', 'password'), $request, $response)) {
        $request_data = $request->getParsedBody();

        $request_data = [
            'email' => 'asdf',
            'password' => 'zxcv',
            // 'name' => 'qwer',
            // 'school' => 'siwon',
        ];

        $email = $request_data['email'];
        $password = $request_data['password'];

        $db = new DbOperations;

        $result = $db->userLogin($email, $password);

        if($result == USER_AUTHENTICATED) {
            $user = $db->getUserByEmail($email);
            $response_data = array();

            $response_data['error'] = false;
            $response_data['message'] = 'Login Successfull';
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));

            return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);

        } else if($result == USER_NOT_FOUND) {
            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';

            $response->write(json_encode($response_data));

            return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);

        } else if($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';

            $response->write(json_encode($response_data));

            return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);

});

$app->get('/allusers', function(Request $request, Response $response) {
    $db = new DbOperations;
    $users = $db->getAllUsers();
    $response_data = array();
    $response_data['error'] = false;
    $response_data['users'] = $users;
    $response->write(json_encode($response_data));

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);

});

$app->put('/updateuser/{id}', function(Request $request, Response $response, array $args) {
    $id = $args['id'];
    
    if(!haveEmptyParameters(array('email', 'name', 'school', 'id'), $request, $response)) {
        $request_data = $request->getParsedBody();

        $request_params = [
            'email' => 'sancho@dortmund.com',
            'name' => 'Sancho',
            'school' => 'dortmund',
            'id' => '1',
        ];

        $email = $request_data['email'];
        $name = $request_data['name'];
        $school = $request_data['school'];
        $id = $request_data['id'];

        $db = new DbOperations;

        if($db->updateUser($email, $name, $school, $id)) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'User Updated Successfully';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);

        } else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Please try again later';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);

        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);

});

$app->put('/updatepassword', function(Request $request, Response $response) {

    if(!haveEmptyParameters(array('currentpassword', 'newpassword', email), $request, $response)) {
        $request_data = $request->getParseBody();
        $currentpassword = $request_data['currentpassword'];
        $newpassword = $request_data['newpassword'];
        $email = $request_data['email'];
        
        $db = new DbOperations;

        $result = $db->updatePassword($currentpassword, $newpassword, $email);

        if($result == PASSWORD_CHANGED) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = "Password Changed";
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);

        } else if ($result == PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = "You have given wrong password";
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        } else if ($result == PASSWORD_NOT_CHANGED) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = "Some error occurred";
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);

});

$app->delete('/deletesuer/{id}', function(Request $request, Response $response, array $args) {
    $id = $args['id'];

    $db = new DbOperations;
    $response_data = array();

    if($db->deleteUser($id)) {
        $response_data['error'] = false;
        $response_data['message'] = 'User has been deleted';

    } else {
        $response_data['error'] = true;
        $response_data['message'] = 'Plase try again later';

    }

    $response->write(json_encode($response_data));

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);

});

function haveEmptyParameters($required_params, $request, $response){
    $error = false; 
    $error_params = '';
    $request_params = $request->getParsedBody(); 

    $request_params = [
        'email' => 'asdf',
        'password' => 'zxcv',
        'name' => 'qwer',
        'school' => 'siwon',
    ];


    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
            $error = true; 
            $error_params .= $param . ', ';
        }
    }

    if($error){
        $error_detail = array();
        $error_detail['error'] = true; 
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error; 
}

$app->run();