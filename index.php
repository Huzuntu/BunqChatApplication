<?php

require 'vendor/autoload.php';

$databaseConnection = new PDO("sqlite:" . __DIR__ . "/chat.db");
$databaseConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();


// This is the index route of my application like homepage
$app->get('/', function (Request $request, Response $response)
{
    $htmlContent = file_get_contents('index.html');
    $response->getBody()->write($htmlContent);
    return $response->withHeader('Content-Type', 'text/html');
});

// This is signup route, when a new user comes they should sign up first
$app->post('/signup', function (Request $request, Response $response) 
{
    // These are getting the inputs from signup html form and creating local variables
    $formData = $request->getParsedBody();
    $newUsername = $formData['username'] ?? '';
    $newPassword = $formData['password'] ?? '';


    // I think it is always a good idea to store the password as hashed version of it
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $database = new SQLite3('chat.db');
    try 
    {
        // After database connection I am inserting the new users with the inputted values
        // I am not looking for edge cases of these problems
        $query = $database->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        // These bindValue function are just parsing the input values to the queries
        $query->bindValue(':username', $newUsername, SQLITE3_TEXT);
        $query->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
        $query->execute();

        $response->getBody()->write(json_encode(['message' => 'User registered successfully']));

        return $response->withHeader('Location', '/chatRoom.html')->withStatus(302); 
    }
    catch (Exception $e) 
    {
        // I can only think of a problem like Username already exists 
        // because username is a UNIQUE property in my sqlite database
        $response = $response->withStatus(400);
        $response->getBody()->write(json_encode(['error' => 'Username already exists']));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// This is login route, it gets triggered when user fills the login form and clicks the login button
$app->post('/login', function (Request $request, Response $response) 
{
    // These are getting the inputs from index login html form and creating local variables
    $formData = $request->getParsedBody();
    $inputUsername = $formData['username'] ?? '';
    $inputPassword = $formData['password'] ?? '';

    // Creating sqlite database connection and writing query to 
    // Get id and password from users table
    $database = new SQLite3('chat.db');
    $query = $database->prepare("SELECT id, password FROM users WHERE username = :username");
    $query->bindValue(':username', $inputUsername, SQLITE3_TEXT);
    $queryResult = $query->execute();
    $userRecord = $queryResult->fetchArray(SQLITE3_ASSOC);
  
    // This if statement checks if there is a user, 
    // and also checks input password and hashed password in the database are the same
    if($userRecord && password_verify($inputPassword,$userRecord['password']))
    {
        // Start session and store user id
        session_start();
        $userId = $userRecord['id'];
        $_SESSION['user_id'] = $userId;
        // I am storing user id in the session
        // I will use that later in my other html files

        // After verifying the user, user can go the chat room
        return $response->withHeader('Location', '/chatRoom.html')->withStatus(302); 
    } 
    else 
    {
        // If not verified warning should appear
        $errorMessage = urlencode('Invalid username or password');
        return $response->withHeader('Location', '/index.html?error=' . $errorMessage)->withStatus(302);
    }
});


// Every user can create chat groups
$app->post('/createChatGroup', function (Request $request, Response $response)
{
    // Getting the group name from the form
    $formData = $request->getParsedBody();
    $chatGroupName = $formData['groupName'];
    
    $database = new SQLite3('chat.db');

    // Checking if the group name already exists
    $checkQuery = $database->prepare('SELECT COUNT(*) FROM groups WHERE name = :name');
    $checkQuery->bindValue(':name', $chatGroupName, SQLITE3_TEXT);
    $result = $checkQuery->execute();
    $count = $result->fetchArray(SQLITE3_ASSOC)['COUNT(*)'];

    if ($count > 0) {
        // If group name already exists I am sending an error message in the url
        // After that I will use that error message to pop up an alert
        $errorMessage = urlencode('Group name already exists. Please choose a different name.');
        return $response->withHeader('Location', '/chatRoom.html?error=' . $errorMessage)->withStatus(302);
    }

    // If it is unique then I am inserting without thinking about the edge cases
    // Like group names has to be text
    $query = $database->prepare('INSERT INTO groups (name) VALUES (:name)');
    $query->bindValue(':name', $chatGroupName, SQLITE3_TEXT);
    $query->execute();

    // Redirect to the chat room page after successful group creation
    return $response->withHeader('Location', '/chatRoom.html')->withStatus(302);
});

// I am using this route in two ways
// First just like in the name I am getting the chat groups when user enters the chat room
// Second is after user searches certain chat groups, I created a button to again show all of the chat groups
$app->get('/getChatGroups', function (Request $request, Response $response)
{
    $database = new SQLite3('chat.db');
    $queryResult = $database->query('SELECT id, name FROM groups');
    $chatGroups = [];
    // With this loop I am filling this chat groups array
    while ($row = $queryResult->fetchArray(SQLITE3_ASSOC)) {
        $chatGroups[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    $response->getBody()->write(json_encode($chatGroups));
    return $response->withHeader('Content-Type', 'application/json');
});

// I created this root to get the user id from the session
// I am sure there was another way, probably a better way to do this
// But I wanted to be practical at this point
$app->get('/getUserId', function (Request $request, Response $response) 
{
    session_start();
    $currentUserId = $_SESSION['user_id'] ?? null;

    if ($currentUserId) 
    {
        $response->getBody()->write(json_encode(['user_id' => $currentUserId]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } 
});

// I don't think this was asked but since I created an UI for this project
// I wanted to expand or add some features when I thought about it
// This is a basic feature: User can search chat groups in the chat group list
$app->get('/searchChatGroups', function ($request, $response) 
{
    $database = new SQLite3('chat.db');
    $query = $database->prepare('SELECT name FROM groups WHERE name = :name');
    $query->bindValue(':name', $query, PDO::PARAM_STR);
    $queryResult = $query->execute();
    $matchingGroups = $queryResult->fetchArray(PDO::FETCH_COLUMN);

    $response->getBody()->write(json_encode($matchingGroups));
    return $response->withHeader('Content-Type', 'application/json');
});


// This route is getting the messages for the current group that was selected
// We start a session then retrieve group ID from request and user ID from session
// It is sorted by the time --> oldest first
$app->get('/getMessages', function (Request $request, Response $response) 
{
    session_start();
    $database = new SQLite3('chat.db');

    $selectedGroupId = $_GET['group_id'];
    $currentUserId = $_SESSION['user_id'];

    // I am joining two tables (messages and users)
    // Ordering the messages by timestamp --> oldest first
    $query = $database->prepare("SELECT m.content, m.timestamp, m.user_id, u.username
                                FROM messages m
                                JOIN users u ON m.user_id = u.id
                                WHERE m.group_id = :group_id
                                ORDER BY m.timestamp ASC");

    $query->bindValue(':group_id', $selectedGroupId, SQLITE3_INTEGER);
    $result = $query->execute();

    $messages = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) 
    {
        // Filling this messages array in this while loop for the current chat group
        $messages[] = [
            'content' => $row['content'],
            'timestamp' => $row['timestamp'],
            'username' => $row['username'],
            'user_id' => $row['user_id'],
        ];
    }

    $response->getBody()->write(json_encode($messages));
    return $response->withHeader('Content-Type', 'application/json');
});

// Using this route to send message
// Gets message content and group ID from request 
// then saves to the database with current timestamp
$app->post('/sendMessage', function (Request $request, Response $response) 
{
    session_start();
    $database = new SQLite3('chat.db');

    // Decodes JSON input to get group ID and message content
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'];
    $groupId = $input['group_id'];
    $message = $input['content'];

    // Query to insert messages into messages table
    $query = $database->prepare("INSERT INTO messages (user_id, group_id, content, timestamp) 
                           VALUES (:user_id, :group_id, :content, datetime('now'))");
    $query->bindValue(':user_id', $userId, SQLITE3_INTEGER);
    $query->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
    $query->bindValue(':content', $message, SQLITE3_TEXT);
    $query->execute();

    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});



// Route to log out the current user
// Clears session data, destroys the session, and deletes the session cookie if it exists
$app->post('/logout', function (Request $request, Response $response) 
{
    session_start();
    session_unset();
    session_destroy();
    if (isset($_COOKIE[session_name()])) 
    {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
});


// Run the app
$app->run();