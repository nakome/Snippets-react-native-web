<?php

/*
 * Declara al principio del archivo, las llamadas a las funciones respetarán
 * estrictamente los indicios de tipo (no se lanzarán a otro tipo).
 */
declare (strict_types = 1);

// global vars
define('ACCESS', true);
define('ROOT', rtrim(dirname(__FILE__), '\\/'));
define('LIBRARY', ROOT . '/library');

// Database mysql config
define('DBHOST', "localhost:3306");
define('DBNAME', "mydb");
define('DBUSER', 'root');
define('DBPASS', 'root');
define('DBCHARSET', 'utf8mb4');
// Token
define('TOKEN', '55f88217afd0eb63bb71749bd5241a2e91edd97658d6047fb2b0f9f303392aec83829814c2c9730a904a5568bdd0dd');

/**
 * Prevenir accesso
 */
defined('ACCESS') or die('Sin accesso al script.');

trait Create
{
    /**
     * Create table.
     * 
     * @param string $dbname
     */
    public function createTable(string $dbname = '')
    {
        if ($this->auth()) {

            $pdo = $this->dbConnect();
            $sql = <<<STR
            CREATE TABLE IF NOT EXISTS $dbname (
                uid INT(11) NOT NULL AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL UNIQUE,
                author VARCHAR(50) NULL DEFAULT 'Anonymous',
                author_info VARCHAR(255) NULL DEFAULT '',
                title VARCHAR(50) NULL DEFAULT '? title',
                description VARCHAR(140) NULL DEFAULT '? description',
                content JSON,
                created DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
                updated DATETIME,
                category VARCHAR(25) NULL DEFAULT 'default',
                public INT(1) NULL DEFAULT 1,
                token VARCHAR(50) NULL,
                PRIMARY KEY (uid)
            ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;
            STR;
            $stmt = $pdo->query($sql);
            if ($stmt) {
                $this->createApiResponse([
                    'MESSAGE' => $this->format($this->languages["successCreateTable"], [$dbname]),
                ]);
            } else {
                $this->createApiResponse([
                    'MESSAGE' => $this->format($this->languages["errorCreateTable"], [$dbname]),
                ]);
            }
        }

        $this->debug();
    }

    /**
     * Export table
     * 
     * @param string $dbname
     */
    public function exportTable(string $dbname = '')
    {
        // [url]api/export/[dbname]
        if ($this->auth()) {
            try {
                $pdo = $this->dbConnect();
                $sql = "SELECT * FROM {$dbname}";
                $stmt = $pdo->prepare($sql);
                $output = $stmt->execute();
                if ($output) {

                    $delimiter = ",";
                    $filename = $dbname . " " . date('Y-m-d') . ".csv";
                    // Create a file pointer
                    $f = fopen('php://memory', 'w');

                    // Set column headers
                    $fields = array(
                        'uid',
                        'name',
                        'author',
                        'author_info',
                        'title',
                        'description',
                        'content',
                        'created',
                        'updated',
                        'category',
                        'public',
                        'token',
                    );
                    fputcsv($f, $fields, $delimiter);
                    // Output each row of the data, format line as csv and write to file pointer
                    while ($row = $stmt->fetch()) {
                        $lineData = array(
                            $row['uid'],
                            $row['name'],
                            $row['author'],
                            $row['author_info'],
                            $row['title'],
                            $row['description'],
                            $row['content'],
                            $row['created'],
                            $row['updated'],
                            $row['category'],
                            $row['public'],
                            $row['token'],
                        );
                        fputcsv($f, $lineData, $delimiter);
                    }

                    // Move back to beginning of file
                    fseek($f, 0);

                    // Set headers to download file rather than displayed
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="' . $filename . '";');

                    //output all remaining data on a file pointer
                    fpassthru($f);
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        } else {
            $this->json([
                'STATUS' => $_SERVER['REDIRECT_STATUS'],
                'IP' => $this->getIp(),
                'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                'PARAMS' => $_GET,
                'MESSAGE' => $this->format($this->languages['errotNotConsult'], ['export']),
            ]);
        }
    }
}

trait Get
{

    /**
     * Create Response for uid
     *
     * @param string $dbname
     */
    public function createResponseUid(string $dbname = '')
    {
        // [url]api/g/[dbname]/?uid=1 with auth
        if ($this->auth() and array_key_exists('uid', $_GET)) {
            try {
                $uid = (string) trim($_GET["uid"]);
                $pdo = $this->dbConnect();
                $sql = "SELECT * FROM {$dbname} WHERE uid=:uid";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute(array(':uid' => $uid))) {
                    $output = $this->createSingleResponse($stmt->fetch());
                    $this->createApiResponse($output);
                } else {
                    $this->showErrorMessage("Sorry, the consult '{$uid}' in '{$dbname}' not has not obtained results");
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        }
    }
    /**
     * Create Response for name
     *
     * @param string $dbname
     */
    public function createResponseName(string $dbname = '')
    {
        // [url]api/g/[dbname]/?name=asdfae with auth
        if ($this->auth() and array_key_exists('name', $_GET)) {
            try {
                $name = (string) trim($_GET["name"]);
                $pdo = $this->dbConnect();
                $sql = "SELECT * FROM {$dbname} WHERE name=:name";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute(array(':name' => $name))) {
                    $output = $this->createSingleResponse($stmt->fetch());
                    $this->createApiResponse($output);
                } else {
                    $this->showErrorMessage("Sorry, the consult '{$name}' in '{$dbname}' not has not obtained results");
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        }
    }
    /**
     * Create Response for token
     *
     * @param string $dbname
     */
    public function createResponseToken(string $dbname = '')
    {
        // [url]api/g/[dbname]/?token=1asdfasdf with auth
        if ($this->auth() and array_key_exists('token', $_GET)) {
            try {
                $token = (string) trim(urldecode($_GET["token"]));
                $pdo = $this->dbConnect();
                $sql = "SELECT * FROM {$dbname} WHERE token=:token";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute(array(':token' => $token))) {
                    $output = $this->createSingleResponse($stmt->fetch());
                    $this->createApiResponse($output);
                } else {
                    $this->showErrorMessage("Sorry, the consult '{$token}' in '{$dbname}' not has not obtained results");
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        }
    }
    /**
     * Create Response for author
     *
     * @param string $dbname
     */
    public function createResponseAuthor(string $dbname = '')
    {
        // [url]api/g/[dbname]/?author=snippet with auth
        if ($this->auth() and array_key_exists('author', $_GET)) {
            try {
                $author = (string) trim(urldecode($_GET["author"]));
                $limit = (isset($_GET["limit"])) ? (string) urldecode($_GET["limit"]) : 5;
                $offset = (isset($_GET["offset"])) ? (string) urldecode($_GET["offset"]) : 0;
                // connect database
                $pdo = $this->dbConnect();
                $sql = "SELECT * FROM {$dbname} WHERE author LIKE '%{$author}%' ORDER BY created DESC LIMIT {$limit} OFFSET {$offset}";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute()) {
                    $output = $this->createFullResponse($stmt->fetchAll());
                    $this->createApiResponse($output);
                } else {
                    $this->showErrorMessage($this->languages["errotNotConsult"], [$dbname]);
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        }
    }
    /**
     * Create Response for category
     *
     * @param string $dbname
     */
    public function createResponseCategory(string $dbname = '')
    {
        // [url]api/g/[dbname]/?category=snippet with auth
        if ($this->auth() and array_key_exists('category', $_GET)) {
            try {
                $category = (string) trim(urldecode($_GET["category"]));
                $limit = (isset($_GET["limit"])) ? (string) urldecode($_GET["limit"]) : 5;
                $offset = (isset($_GET["offset"])) ? (string) urldecode($_GET["offset"]) : 0;
                // connect database
                $pdo = $this->dbConnect();
                $sql = "SELECT * FROM {$dbname} WHERE category=:category ORDER BY created DESC LIMIT {$limit} OFFSET {$offset}";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute(array(':category' => $category))) {
                    $output = $this->createFullResponse($stmt->fetchAll());
                    $this->createApiResponse($output);
                } else {
                    $this->showErrorMessage($this->languages["errotNotConsult"], [$dbname]);
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        }
    }
    /**
     * Create Response for title
     *
     * @param string $dbname
     */
    public function createResponseTitle(string $dbname = '')
    {
        // [url]api/g/[dbname]/?title=abc with auth
        if ($this->auth() and array_key_exists('title', $_GET)) {
            try {
                $title = (string) urldecode($_GET["title"]);
                $limit = (isset($_GET["limit"])) ? (string) urldecode($_GET["limit"]) : 5;
                $offset = (isset($_GET["offset"])) ? (string) urldecode($_GET["offset"]) : 0;
                // connect database
                $pdo = $this->dbConnect();
                $sql = "SELECT * FROM {$dbname} WHERE title LIKE '%{$title}%' ORDER BY created DESC LIMIT {$limit} OFFSET {$offset}";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute()) {
                    $output = $this->createFullResponse($stmt->fetchAll());
                    $this->createApiResponse($output);
                } else {
                    $this->showErrorMessage($this->languages["errotNotConsult"], [$dbname]);
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        }
    }
    /**
     * Create Response for description
     *
     * @param string $dbname
     */
    public function createResponseDescription(string $dbname = '')
    {
        // [url]api/g/[dbname]/?description=abc with auth
        if ($this->auth() and array_key_exists('description', $_GET)) {
            try {
                $description = (string) urldecode($_GET["description"]);
                $limit = (isset($_GET["limit"])) ? (string) urldecode($_GET["limit"]) : 5;
                $offset = (isset($_GET["offset"])) ? (string) urldecode($_GET["offset"]) : 0;
                // connect database
                $pdo = $this->dbConnect();
                $sql = "SELECT * FROM {$dbname} WHERE description LIKE '%{$description}%' ORDER BY created DESC LIMIT {$limit} OFFSET {$offset}";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute()) {
                    $output = $this->createFullResponse($stmt->fetchAll());
                    $this->createApiResponse($output);
                } else {
                    $this->showErrorMessage($this->languages["errotNotConsult"], [$dbname]);
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        }
    }
    /**
     * Create Response for created
     *
     * @param string $dbname
     */
    public function createResponseCreated(string $dbname = '')
    {
        // [url]api/g/[dbname]/?created=2021 with auth
        if ($this->auth() and array_key_exists('created', $_GET)) {
            try {
                $created = (string) urldecode($_GET["created"]);
                $limit = (isset($_GET["limit"])) ? (string) urldecode($_GET["limit"]) : 5;
                $offset = (isset($_GET["offset"])) ? (string) urldecode($_GET["offset"]) : 0;
                // connect database
                $pdo = $this->dbConnect();
                $sql = "SELECT * FROM {$dbname} WHERE created LIKE '%{$created}%' ORDER BY created DESC LIMIT {$limit} OFFSET {$offset}";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute()) {
                    $output = $this->createFullResponse($stmt->fetchAll());
                    $this->createApiResponse($output);
                } else {
                    $this->showErrorMessage($this->languages["errotNotConsult"], [$dbname]);
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        }
    }
    /**
     * Create Response for updated
     *
     * @param string $dbname
     */
    public function createResponseUpdated(string $dbname = '')
    {
        // [url]api/g/[dbname]/?updated=2021 with auth
        if ($this->auth() and array_key_exists('updated', $_GET)) {
            try {
                $updated = (string) urldecode($_GET["updated"]);
                $limit = (isset($_GET["limit"])) ? (string) urldecode($_GET["limit"]) : 5;
                $offset = (isset($_GET["offset"])) ? (string) urldecode($_GET["offset"]) : 0;
                // connect database
                $pdo = $this->dbConnect();
                $sql = "SELECT * FROM {$dbname} WHERE updated LIKE '%{$updated}%' ORDER BY updated DESC LIMIT {$limit} OFFSET {$offset}";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute()) {
                    $output = $this->createFullResponse($stmt->fetchAll());
                    $this->createApiResponse($output);
                } else {
                    $this->showErrorMessage($this->languages["errotNotConsult"], [$dbname]);
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        }
    }
    /**
     * Create Response for all
     *
     * @param string $dbname
     */
    public function createResponseAll(string $dbname = '')
    {
        // [url]api/g/[dbname]/?all=1
        if ($this->auth() and array_key_exists('all', $_GET)) {
            try {
                $all = (string) urldecode($_GET["all"]);
                $limit = (isset($_GET["limit"])) ? (string) urldecode($_GET["limit"]) : 5;
                $offset = (isset($_GET["offset"])) ? (string) urldecode($_GET["offset"]) : 0;
                // connect database
                $pdo = $this->dbConnect();
                $sql = "SELECT * FROM {$dbname} ORDER BY created DESC LIMIT {$limit} OFFSET {$offset}";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute()) {
                    $output = $this->createFullResponse($stmt->fetchAll());
                    $this->createApiResponse($output);
                } else {
                    $this->showErrorMessage($this->languages["errotNotConsult"], [$dbname]);
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        }
    }
    /**
     * Create Response for public
     *
     * @param string $dbname
     */
    public function createResponsePublic(string $dbname = '')
    {

        // [url]api/g/[dbname]/?public=1
        if (array_key_exists('public', $_GET)) {
            try {
                $limit = (isset($_GET["limit"])) ? (string) urldecode($_GET["limit"]) : 5;
                $offset = (isset($_GET["offset"])) ? (string) urldecode($_GET["offset"]) : 0;
                // connect database
                $pdo = $this->dbConnect();
                $stmt = $pdo->prepare("SELECT * FROM {$dbname} WHERE public=1 ORDER BY created DESC LIMIT {$limit} OFFSET {$offset}");
                if ($stmt->execute()) {
                    $output = $this->createFullResponse($stmt->fetchAll());
                    $this->createApiResponse($output);
                } else {
                    $this->showErrorMessage($this->languages["errotNotConsult"], [$dbname]);
                }
            } catch (Exception $e) {
                $this->showErrorMessage($e->getMessage());
            }
        }
    }

    /**
     * Get data.
     *
     * @param string $dbname
     */
    public function getData(string $dbname = '')
    {
        $this->createResponseUid($dbname);
        $this->createResponseName($dbname);
        $this->createResponseToken($dbname);
        $this->createResponseAuthor($dbname);
        $this->createResponseCategory($dbname);
        $this->createResponseTitle($dbname);
        $this->createResponseDescription($dbname);
        $this->createResponseCreated($dbname);
        $this->createResponseUpdated($dbname);
        $this->createResponseAll($dbname);
        $this->createResponsePublic($dbname);
        $this->createApiResponse(); // empty response
    }

    /**
     * Show error msg
     *
     * @param string $message
     * @return json
     */
    public function showErrorMessage(string $message = "")
    {
        return $this->json([
            'STATUS' => 404,
            'IP' => $this->getIp(),
            'HTTP_HOST' => $_SERVER['HTTP_HOST'],
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'MESSAGE' => $message,
            'PARAMS' => $_GET,
            'DATA' => $_POST,
        ]);
    }

    /**
     * Create output response.
     *
     * @return array
     */
    public function createFullResponse(array $response = [])
    {
        $arr = [];
        foreach ($response as $row) {
            $arr[] = [
                'uid' => (int) $row['uid'],
                'name' => (string) $row['name'],
                'author' => (string) $row['author'],
                'author_info' => (string) $row['author_info'],
                'category' => (string) $row['category'],
                'content' => is_array(json_decode($row['content'])) ? json_decode($row['content'], true) : $row['content'],
                'public' => (string) $row['public'],
                'token' => (string) $row['token'],
                'title' => (string) $row['title'],
                'description' => (string) $row['description'],
                'created' => (string) $row['created'],
                'updated' => (string) $row['updated'],
            ];
        }
        return $arr;
    }

    /**
     * Create single array response.
     *
     * @return array
     */
    public function createSingleResponse(array $response = [])
    {
        $arr = [
            'uid' => (int) $response['uid'],
            'name' => (string) $response['name'],
            'author' => (string) $response['author'],
            'author_info' => (string) $response['author_info'],
            'category' => (string) $response['category'],
            'content' => is_array(json_decode($response['content'])) ? json_decode($response['content'], true) : $response['content'],
            'public' => (string) $response['public'],
            'token' => (string) $response['token'],
            'title' => (string) $response['title'],
            'description' => (string) $response['description'],
            'created' => (string) $response['created'],
            'updated' => (string) $response['updated'],
        ];
        return $arr;
    }
}

trait Post
{
    /**
     * POST routes.
     */
    public function postData(string $dbname = '')
    {
        if ($this->auth()) {
            $_POST = json_decode(file_get_contents('php://input'), true);
            if (is_array($_POST)) {
                //generate random string
                $rand_token = openssl_random_pseudo_bytes(16);
                //change binary to hexadecimal
                $token = bin2hex($rand_token);
                // random uid
                $randomUid = $this->randomUid("ABCDEFGHIJKLMNOPQRSTUVWXYZ", 6);
                // data
                $data = array(
                    'name' => $randomUid,
                    'author' => isset($_POST['author']) ? $_POST['author'] : 'Anonymous',
                    'author_info' => isset($_POST['author_info']) ? $_POST['author_info'] : 'Not info provide',
                    'title' => isset($_POST['title']) ? $_POST['title'] : 'default title',
                    'description' => isset($_POST['description']) ? $_POST['description'] : 'default description',
                    'category' => isset($_POST['category']) ? $_POST['category'] : 'default',
                    'public' => isset($_POST['public']) ? $_POST['public'] : (string) 0,
                    'token' => $token,
                    'content' => isset($_POST['content']) ? $_POST['content'] : '[]',
                );
                $sql = "INSERT INTO {$dbname} (name,author,author_info,title,description,category, public, token,content) VALUES (:name,:author,:author_info,:title,:description,:category,:public,:token,:content)";
                $pdo = $this->dbConnect();
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute($data)) {
                    $this->json([
                        'STATUS' => $_SERVER['REDIRECT_STATUS'],
                        'IP' => $this->getIp(),
                        'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                        'MESSAGE' => $this->languages["successCreateRow"],
                        'PARAMS' => $_GET,
                        'DATA' => $_POST,
                    ]);
                } else {
                    $this->json([
                        'STATUS' => 404,
                        'IP' => $this->getIp(),
                        'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                        'MESSAGE' => $this->languages["errorCreateRow"],
                        'PARAMS' => $_GET,
                        'DATA' => $_POST,
                    ]);
                }
            }
        }
        $this->debug();
    }
}

trait Put
{
    /**
     * POST routes.
     */
    public function putData(string $dbname = '')
    {
        if ($this->auth()) {
            $_POST = json_decode(file_get_contents('php://input'), true);
            // uid params
            $uid = (isset($_GET['uid'])) ? urldecode($_GET['uid']) : false;
            if ($uid && is_array($_POST)) {
                //generate random string
                $rand_token = openssl_random_pseudo_bytes(16);
                //change binary to hexadecimal
                $token = bin2hex($rand_token);
                // random uid
                $randomUid = $this->randomUid("ABCDEFGHIJKLMNOPQRSTUVWXYZ", 6);
                $pdo = $this->dbConnect();
                $sql1 = "SELECT * FROM {$dbname} WHERE uid=:uid";
                $stmt = $pdo->prepare($sql1);
                if ($stmt->execute(array(':uid' => $uid))) {
                    $row = $stmt->fetch();
                    // data
                    $data = array(
                        'uid' => $uid,
                        'name' => $row['name'],
                        'author' => isset($_POST['author']) ? $_POST['author'] : $row['author'],
                        'author_info' => isset($_POST['author_info']) ? $_POST['author_info'] : $row['author_info'],
                        'title' => isset($_POST['title']) ? $_POST['title'] : $row['title'],
                        'description' => isset($_POST['description']) ? $_POST['description'] : $row['description'],
                        'category' => isset($_POST['category']) ? $_POST['category'] : $row['category'],
                        'public' => isset($_POST['public']) ? $_POST['public'] : $row['public'],
                        'token' => $token,
                        'content' => isset($_POST['content']) ? $_POST['content'] : $row['content'],
                        'created' => $row['created'],
                        'updated' => date("Y-m-d H:i:s"),
                    );
                    $sql = "UPDATE {$dbname} SET name=:name, author=:author, author_info=:author_info, title=:title, description=:description, category=:category,public=:public,token=:token,content=:content,created=:created,updated=:updated WHERE uid=:uid";
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute($data)) {
                        $this->json([
                            'STATUS' => $_SERVER['REDIRECT_STATUS'],
                            'IP' => $this->getIp(),
                            'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                            'MESSAGE' => $this->format($this->languages['successUpdateRow'], [$uid]),
                            'PARAMS' => $_GET,
                            'DATA' => $_POST,
                        ]);
                    } else {
                        $this->json([
                            'STATUS' => 404,
                            'IP' => $this->getIp(),
                            'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                            'MESSAGE' => $this->format($this->languages['errorUpdateRow'], [$uid]),
                            'PARAMS' => $_GET,
                            'DATA' => $_POST,
                        ]);
                    }
                } else {
                    $this->json([
                        'STATUS' => 404,
                        'IP' => $this->getIp(),
                        'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                        'MESSAGE' => $this->format($this->languages['successUpdateRow'], [$uid]),
                        'PARAMS' => $_GET,
                        'DATA' => $_POST,
                    ]);
                }

            }
        }

        $this->debug();
    }
}

trait Del
{
    /**
     * DEL routes.
     */
    public function delData(string $dbname = '')
    {
        if ($this->auth()) {
            $_POST = json_decode(file_get_contents('php://input'), true);
            // uid params
            $uid = (isset($_GET['uid'])) ? urldecode($_GET['uid']) : false;
            if ($uid && is_array($_POST)) {

                $sql = "DELETE FROM {$dbname} WHERE uid=:uid";
                $pdo = $this->dbConnect();
                $statement = $pdo->prepare($sql);
                $statement->bindParam(':uid', $uid, PDO::PARAM_INT);
                if ($statement->execute()) {
                    $this->json([
                        'STATUS' => $_SERVER['REDIRECT_STATUS'],
                        'IP' => $this->getIp(),
                        'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                        'MESSAGE' => $this->format($this->languages['successDeleteRow'], [$uid]),
                        'PARAMS' => $_GET,
                        'DATA' => $_POST,
                    ]);
                } else {
                    $this->json([
                        'STATUS' => 404,
                        'IP' => $this->getIp(),
                        'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                        'MESSAGE' => $this->format($this->languages['errorDeleteRow'], [$uid]),
                        'PARAMS' => $_GET,
                        'DATA' => $_POST,
                    ]);
                }
            }
        }

        $this->debug();
    }
    /**
     * Create table.
     */
    public function clean(string $dbname = '')
    {
        if ($this->auth()) {
            // connect database
            $pdo = $this->dbConnect();
            $sql = "TRUNCATE TABLE {$dbname}";
            $output = $pdo->query($sql);
            if ($output) {
                $this->createApiResponse([
                    'MESSAGE' => $this->format($this->languages['successCleanTable'], [$dbname]),
                ]);
            } else {
                $this->createApiResponse([
                    'MESSAGE' => $this->format($this->languages['errorCleanTable'], [$dbname]),
                ]);
            }
        }

        $this->debug();
    }
    /**
     * Create table.
     */
    public function destroy(string $dbname = '')
    {
        if ($this->auth()) {
            // connect database
            $pdo = $this->dbConnect();
            $sql = "DROP TABLE {$dbname}";
            $output = $pdo->query($sql);
            if ($output) {
                $this->createApiResponse([
                    'MESSAGE' => $this->format($this->languages['successDeleteTable'], [$dbname]),
                ]);
            } else {
                $this->createApiResponse([
                    'MESSAGE' => $this->format($this->languages['errorDeleteTable'], [$dbname]),
                ]);
            }
        }

        $this->debug();
    }
}

trait Url
{
    /**
     * C.O.R.S.
     */
    public static function cors()
    {
        // Permitir que desde cualquier origen
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Decide el origen $_SERVER['HTTP_ORIGIN']
            // que quieres permitir, y si es así:
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // cache de 1 dia
        }
        // Los encabezados de Control de Acceso se reciben durante las solicitudes de OPCIONES
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            }
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }
            exit(0);
        }
    }

    /**
     * Get url.
     */
    public static function siteUrl()
    {
        $https = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https://' : 'http://';
        return $https . rtrim(rtrim($_SERVER['HTTP_HOST'], '\\/') . dirname($_SERVER['PHP_SELF']), '\\/');
    }

    /**
     * Get ip
     */
    public function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}

/**
 * @author      Moncho Varela / Nakome <nakome@gmail.com>
 * @copyright   2020 Moncho Varela / Nakome <nakome@gmail.com>
 *
 * @version     0.0.1
 */
class App
{
    //traits
    use Url;
    use Post;
    use Put;
    use Del;
    use Get;
    use Create;

    private $languages = array(
        "successCreateTable" => "Correcto, la tabla {} se ha creado",
        "errorCreateTable" => "Error, no se pudo crear la tabla {}",
        "successCreateRow" => "Correcto, se ha creado la nueva fila",
        "errorCreateRow" => "Error, no se pudo crear la nueva fila",
        "successUpdateRow" => "Correcto, el id {} se ha actualizado",
        "errorUpdateRow" => "Error, no se pudo actualizar el identificador {}",
        "successDeleteRow" => "Correcto, el identificador {} se ha eliminado",
        "errorDeleteRow" => "Error, no se pudo eliminar el identificador {}",
        "successDeleteTable" => "Correcto, la tabla {} se ha quitado",
        "errorDeleteTable" => "Error, no se pudo quitar la tabla {}",
        "errotNotConsult" => "Lo sentimos, la consulta en '{}' no ha obtenido resultados ",
        "successCleanTable" => "Correcto, se ha vaciado la tabla {}",
        "errorCleanTable" => "Error, no se ha podido vaciar la tabla {}",
    );

    private $routes = array();

    /**
     * Construct.
     */
    public function __construct(string $token = "")
    {
        // mysql config
        $host = DBHOST;
        $db = DBNAME;
        $user = DBUSER;
        $pass = DBPASS;
        $charset = DBCHARSET;
        $this->data = "mysql:host=$host;dbname=$db;charset=$charset";
        $this->user = $user;
        $this->pass = $pass;
        $this->token = $token;
    }

    /**
     * Alternative format Python
     *
     * <code>
     *   format("hello {}",array('world'));
     * </code>
     *
     * @param string $msg
     * @param array $vars
     */
    public function format($msg, $vars)
    {
        $vars = (array) $vars;

        $msg = preg_replace_callback('#\{\}#', function ($r) {
            static $i = 0;
            return '{' . ($i++) . '}';
        }, $msg);

        return str_replace(
            array_map(function ($k) {
                return '{' . $k . '}';
            }, array_keys($vars)),

            array_values($vars),

            $msg
        );
    }

    /**
     * Random uid
     *
     * @param string $input
     * @param int $strlen
     */
    public function randomUid(string $input = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890", int $strlen = 6)
    {
        $input_length = strlen($input);
        $random_string = '';
        for ($i = 0; $i < $strlen; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
        return uniqid($random_string);
    }

    /**
     *  Render Assets.
     *
     *  @param array $patterns  array
     *  @param array $callback  function
     */
    public function route($patterns, $callback)
    {
        // if not in array
        if (!is_array($patterns)) {
            $patterns = array($patterns);
        }
        foreach ($patterns as $pattern) {
            // trim /
            $pattern = trim($pattern, '/');

            // get any num all
            $pattern = str_replace(
                array('\(', '\)', '\|', '\:any', '\:num', '\:all', '#'),
                array('(', ')', '|', '[^/]+', '\d+', '.*?', '\#'),
                preg_quote($pattern, '/')
            );

            // this pattern
            $this->routes['#^' . $pattern . '$#'] = $callback;
        }
    }

    /**
     *  launch routes.
     */
    public function launch()
    {
        // Turn on output buffering
        ob_start();

        // launch
        $url = $_SERVER['REQUEST_URI'];

        $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

        if (strpos($url, $base) === 0) {
            $url = substr($url, strlen($base));
        }

        $url = trim($url, '/');

        foreach ($this->routes as $pattern => $callback) {

            if (preg_match($pattern, $url, $params)) {
                array_shift($params);
                //return function
                return call_user_func_array($callback, array_values($params));
            }
        }

        // Page not found
        if ($this->is404($url)) {
            @header('Content-type: application/json');
            $arr = array(
                'STATUS' => 404,
                'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                'OPTS' => $_GET,
            );
            exit(json_encode($arr));
        }

        // end flush
        ob_end_flush();
        exit;
    }

    /**
     * Determines if 404.
     *
     * @param      <type>   $url    The url
     *
     * @return     bool  True if 404, False otherwise
     */
    public function is404($url)
    {
        $handle = curl_init($url);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        curl_close($handle);

        /* If the document has loaded successfully without any redirection or error */
        if ($httpCode >= 200 && $httpCode < 300) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Connect Database.
     *
     * @return new Connection
     */
    public function dbConnect()
    {
        $pdo = null;
        try {
            $pdo = new PDO($this->data, $this->user, $this->pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int) $e->getCode());
        }
        return $pdo;
    }

    /**
     * Debug data.
     *
     * @return json
     */
    public function debug(array $arr = [], bool $success = false)
    {
        @header('Content-type: application/json');

        if ($success) {
            return exit(json_encode([
                'STATUS' => 200,
                'IP' => $this->getIp(),
                'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                'OPTS' => $_GET,
                'DATA' => $arr,
            ]));
        }

        return exit(json_encode(['STATUS' => '404', 'IP' => $this->getIp()]));
    }

    /**
     * Array to json
     *
     * @param array $arr
     */
    public function json(array $arr = [])
    {
        @header('Content-type: application/json');
        exit(json_encode($arr));
    }

    /**
     * Routes.
     */
    public function routes()
    {
        /*
         *
         * p = post
         * g = get
         * u = update
         * d = delete
         * s = search
         *
         * - Available routes
         * - localhost/api/[post,get]/dbname
         * - localhost/api/[post,get,put,del,search]/dbname/?filter=blog&length=10
         * - localhost/api/g/dbname/?filter=blog&length=10
         */
        $this->route([
            '/(:any)/(:any)', // localhost/api/[post,get]/clients
            '/(:any)/(:any)/(:any)', // localhost/api/[post,get]/?data=post
        ], function (string $type = '', string $dbname = '', string $any = '') {

            $this->cors();

            switch ($type) {
                case 'c':
                    return $this->createTable($dbname);
                    break;
                case 'p':
                    return $this->postData($dbname);
                    break;
                case 'u':
                    return $this->putData($dbname);
                    break;
                case 'd':
                    return $this->delData($dbname);
                    break;
                case 'g':
                    return $this->getData($dbname);
                    break;
                case 'export':
                    return $this->exportTable($dbname);
                    break;
                case 'destroy':
                    return $this->destroy($dbname);
                    break;
                case 'clean':
                    return $this->clean($dbname);
                    break;
            }
            exit(1);
        });
    }

    /**
     * Init application.
     */
    public function init()
    {
        // add routes
        $this->routes();
        // launch router
        $this->launch();
    }

    /**
     * Get header Authorization.
     */
    public function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        return $headers;
    }

    /**
     * get access token from header.
     */
    public function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     *  Basic auth.
     */
    public function auth()
    {
        if ($this->getBearerToken() === $this->token) {
            return true;
        }

        return false;
    }

    /**
     * Create response.
     *
     * @return json
     */
    public function createApiResponse(array $output = [])
    {
        return $this->json([
            'STATUS' => $_SERVER['REDIRECT_STATUS'],
            'IP' => $this->getIp(),
            'HTTP_HOST' => $_SERVER['HTTP_HOST'],
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'PARAMS' => $_GET,
            'DATA' => is_array($output) ? $output : [],
        ]);
    }
}

// init App
$App = new App();
$App->token = TOKEN;
$App->init();
