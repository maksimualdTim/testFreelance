<?php

namespace Parser;

class Db
{
    protected static \PDO $pdo;
    const SQLITE_FILE = __DIR__ . '/db.sqlite';

    private function __construct()
    {
//        Get object via init
    }

    public static function init(): Db
    {
        if (!isset(self::$pdo)) {
            if (!file_exists(self::SQLITE_FILE)) {
                file_put_contents(self::SQLITE_FILE, '');
            }
            self::$pdo = new \PDO('sqlite:' . self::SQLITE_FILE);
        }
        return new self();
    }

    public function migrate(bool $fresh = false)
    {
        $query = "
            CREATE TABLE IF NOT EXISTS categories
            (
                id	INTEGER NOT NULL UNIQUE,
                name VARCHAR(200) NOT NULL,
                PRIMARY KEY('id')
            );
            CREATE TABLE IF NOT EXISTS budgets
            (
                id	INTEGER NOT NULL UNIQUE,
                category_id	INTEGER NOT NULL,
                product_name VARCHAR(200) NOT NULL,
                January DECIMAL (12,4),
                February DECIMAL (12,4),
                March DECIMAL (12,4),
                April DECIMAL (12,4),
                May DECIMAL (12,4),
                June DECIMAL (12,4),
                July DECIMAL (12,4),
                August DECIMAL (12,4),
                September DECIMAL (12,4),
                October DECIMAL (12,4),
                November DECIMAL (12,4),
                December DECIMAL (12,4),
                TOTAL DECIMAL (12,4),
                PRIMARY KEY('id'),
                FOREIGN KEY('category_id') REFERENCES categories(id) ON DELETE CASCADE
            );
        ";
        if ($fresh) {
            $this->dropAllTables();
        }

        self::$pdo->exec($query);

    }

    public function dropAllTables()
    {
        $query = "
            DROP TABLE IF EXISTS categories;
            DROP TABLE IF EXISTS budgets;
            DROP TABLE IF EXISTS metadata;
        ";
        self::$pdo->exec($query);
    }

    public function saveCategory(string $name)
    {
        $sth = self::$pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
        $sth->execute(['name' => $name]);
    }

    public function getLastRecordID(string $table): int
    {
        $query = "SELECT id FROM $table ORDER BY ID DESC LIMIT 1";
        $response = self::$pdo->query($query);
        return $response->fetchColumn();
    }

    public function saveBudget(array $data)
    {
        $query = "INSERT INTO budgets (
                     category_id, 
                     product_name, 
                     January, 
                     February,
                     March, 
                     April, 
                     May, 
                     June, 
                     July, 
                     August, 
                     September,
                     October,
                     November, 
                     December,
                     TOTAL
                     ) 
        VALUES (
                :category_id, 
                :product_name, 
                :January,
                :February,
                :March,
                :April,
                :May, 
                :June, 
                :July, 
                :August, 
                :September,
                :October,
                :November, 
                :December,
                :TOTAL                
                )";
        $sth = self::$pdo->prepare($query);
        $sth->execute([
            'category_id' => $data['category_id'],
            'product_name' => $data['product_name'],
            'January' => $data['January'],
            'February' => $data['February'],
            'March' => $data['March'],
            'April' => $data['April'],
            'May' => $data['May'],
            'June' => $data['June'],
            'July' => $data['July'],
            'August' => $data['August'],
            'September' => $data['September'],
            'October' => $data['October'],
            'November' => $data['November'],
            'December' => $data['December'],
            'TOTAL' => $data['TOTAL'],
        ]);
    }

    public function getBudget(string $product_name, int $category_id)
    {
        $response = self::$pdo->query("SELECT 
        January,February,March, April, May, June, July,
        August, September,October,November, December,TOTAL
        FROM budgets WHERE product_name='$product_name' AND category_id=$category_id");
        return $response->fetch();
    }
    public function getCategory($name){
        $response = self::$pdo->query("SELECT * FROM categories WHERE name='$name'");
        return $response->fetch();
    }
    public function updateBudgets(array $data){
        $product_name = $data['product_name'];
        $category_id = $data['category_id'];
        $budget = $this->getBudget($product_name, $category_id);

        $query = "UPDATE budgets SET
        January=:January,
        February=:February,
        March=:March,
        April=:April,
        May=:May, 
        June=:June, 
        July=:July, 
        August=:August, 
        September=:September,
        October=:October,
        November=:November, 
        December=:December,
        TOTAL=:TOTAL
        WHERE product_name='$product_name' AND category_id=$category_id";
        $sth = self::$pdo->prepare($query);

        $sth->execute([
            'January' => $data['January'] ?? $budget['January'],
            'February' => $data['February'] ?? $budget['February'],
            'March' => $data['March'] ?? $budget['March'],
            'April' => $data['April'] ?? $budget['April'],
            'May' => $data['May'] ?? $budget['May'],
            'June' => $data['June'] ?? $budget['June'],
            'July' => $data['July'] ?? $budget['July'],
            'August' => $data['August'] ?? $budget['August'],
            'September' => $data['September'] ?? $budget['September'],
            'October' => $data['October'] ?? $budget['October'],
            'November' => $data['November'] ?? $budget['November'],
            'December' => $data['December'] ?? $budget['December'],
            'TOTAL' => $data['TOTAL'] ?? $budget['TOTAL'],
        ]);
    }
}