<?php

namespace db;

require __DIR__ . '/dbClass.php';

class dbResolver extends dbClass
{
    /**
     * Instance
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Log Transaction
     *
     * @param array $payload
     * @return bool
     */
    public function logTransaction(array $payload): bool {
        $sql = "INSERT INTO ".parent::TABLES['transactions']." (email,transaction_id,status,transaction_date) VALUES (?,?,?,?)";
        $query = parent::query($sql,$payload)->lastInsertID();

        return $query > 0;
    }

    /**
     * Check if a duplicate transaction ID already exists
     *
     * @param string $transactionID
     * @return bool
     */
    public function checkDuplicateTransactionID(string $transactionID): bool {
        $sql = 'SELECT * FROM '.parent::TABLES['transactions'].' WHERE transaction_id = ?';
        $query = parent::query($sql,$transactionID)->numRows();

        return $query > 0;
    }
}