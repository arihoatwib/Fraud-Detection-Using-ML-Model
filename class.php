<?php
require_once 'config.php';

class db_class extends db_connect {
    public function __construct() {
        $this->connect();
    }

    /* User Functions */
    public function add_user($username, $password, $firstname, $lastname) {
        $query = $this->conn->prepare("INSERT INTO `user` (`username`, `password`, `firstname`, `lastname`) VALUES(?, ?, ?, ?)");
        $query->bind_param("ssss", $username, $password, $firstname, $lastname);

        if ($query->execute()) {
            $query->close();
            return true;
        }
        return false;
    }

    public function update_user($user_id, $username, $password, $firstname, $lastname) {
        $query = $this->conn->prepare("UPDATE `user` SET `username`=?, `password`=?, `firstname`=?, `lastname`=? WHERE `user_id`=?");
        $query->bind_param("ssssi", $username, $password, $firstname, $lastname, $user_id);

        if ($query->execute()) {
            $query->close();
            return true;
        }
        return false;
    }

    public function login($username, $password) {
        $query = $this->conn->prepare("SELECT * FROM `user` WHERE `username`=? AND `password`=?");
        $query->bind_param("ss", $username, $password);

        if ($query->execute()) {
            $result = $query->get_result();
            $valid = $result->num_rows;
            $fetch = $result->fetch_array();

            return array(
                'user_id' => isset($fetch['user_id']) ? $fetch['user_id'] : 0,
                'count' => isset($valid) ? $valid : 0
            );
        }
        return array('user_id' => 0, 'count' => 0);
    }

    public function user_acc($user_id) {
        $query = $this->conn->prepare("SELECT * FROM `user` WHERE `user_id`=?");
        $query->bind_param("i", $user_id);

        if ($query->execute()) {
            $result = $query->get_result();
            $fetch = $result->fetch_array();

            return $fetch['firstname'] . " " . $fetch['lastname'];
        }
        return null;
    }

    public function display_user() {
        $query = $this->conn->prepare("SELECT * FROM `user`");
        if ($query->execute()) {
            return $query->get_result();
        }
        return null;
    }

    public function delete_user($user_id) {
        $query = $this->conn->prepare("DELETE FROM `user` WHERE `user_id`=?");
        $query->bind_param("i", $user_id);

        if ($query->execute()) {
            $query->close();
            return true;
        }
        return false;
    }

    /* Fraud Detection Functions */
    public function check_fraud($transaction_data) {
        // Example: Call an external ML model API
        $fraud_status = $this->call_fraud_model($transaction_data);
        return $fraud_status;
    }

    private function call_fraud_model($data) {
        // Placeholder function to demonstrate calling an external ML model
        // In reality, this function would use cURL or similar to call a REST API
        $url = "https://example.com/fraud_detection";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function log_fraudulent_transaction($transaction_id, $details) {
        $query = $this->conn->prepare("INSERT INTO `fraud_log` (`transaction_id`, `details`) VALUES (?, ?)");
        $query->bind_param("is", $transaction_id, $details);

        if ($query->execute()) {
            $query->close();
            return true;
        }
        return false;
    }

    public function display_fraud_logs() {
        $query = $this->conn->prepare("SELECT * FROM `fraud_log`");
        if ($query->execute()) {
            return $query->get_result();
        }
        return null;
    }

    /* Transaction Functions */
    public function process_payment($user_id, $amount, $payment_method) {
        $transaction_id = $this->create_transaction($user_id, $amount, $payment_method);
        $transaction_data = $this->get_transaction_data($transaction_id);

        $fraud_status = $this->check_fraud($transaction_data);

        if ($fraud_status['is_fraud']) {
            $this->log_fraudulent_transaction($transaction_id, $fraud_status['details']);
            return array('status' => 'fraudulent', 'transaction_id' => $transaction_id);
        }

        $this->update_transaction_status($transaction_id, 'processed');
        return array('status' => 'processed', 'transaction_id' => $transaction_id);
    }

    private function create_transaction($user_id, $amount, $payment_method) {
        $query = $this->conn->prepare("INSERT INTO `transaction` (`user_id`, `amount`, `payment_method`) VALUES (?, ?, ?)");
        $query->bind_param("iis", $user_id, $amount, $payment_method);

        if ($query->execute()) {
            return $this->conn->insert_id;
        }
        return null;
    }

    private function get_transaction_data($transaction_id) {
        $query = $this->conn->prepare("SELECT * FROM `transaction` WHERE `transaction_id`=?");
        $query->bind_param("i", $transaction_id);

        if ($query->execute()) {
            return $query->get_result()->fetch_assoc();
        }
        return null;
    }

    private function update_transaction_status($transaction_id, $status) {
        $query = $this->conn->prepare("UPDATE `transaction` SET `status`=? WHERE `transaction_id`=?");
        $query->bind_param("si", $status, $transaction_id);

        if ($query->execute()) {
            $query->close();
            return true;
        }
        return false;
    }

    /* Role and Permission Management */
    public function add_role($role_name) {
        $query = $this->conn->prepare("INSERT INTO `role` (`role_name`) VALUES (?)");
        $query->bind_param("s", $role_name);

        if ($query->execute()) {
            $query->close();
            return true;
        }
        return false;
    }

    public function add_permission($role_id, $permission) {
        $query = $this->conn->prepare("INSERT INTO `role_permission` (`role_id`, `permission`) VALUES (?, ?)");
        $query->bind_param("is", $role_id, $permission);

        if ($query->execute()) {
            $query->close();
            return true;
        }
        return false;
    }

    public function get_permissions($role_id) {
        $query = $this->conn->prepare("SELECT `permission` FROM `role_permission` WHERE `role_id`=?");
        $query->bind_param("i", $role_id);

        if ($query->execute()) {
            return $query->get_result();
        }
        return null;
    }

    /* CSV Upload and Processing */
    public function upload_csv($file) {
        if ($file['error'] == 0) {
            $filename = $file['tmp_name'];
            $file = fopen($filename, 'r');

            // Assuming the first row contains column names
            $header = fgetcsv($file);

            while ($row = fgetcsv($file)) {
                $transaction_data = array_combine($header, $row);
                $this->process_csv_transaction($transaction_data);
            }

            fclose($file);
            return true;
        }
        return false;
    }

    private function process_csv_transaction($transaction_data) {
        $fraud_status = $this->check_fraud($transaction_data);

        if ($fraud_status['is_fraud']) {
            // Log fraudulent transaction
            $transaction_id = $transaction_data['transaction_id'] ?? null;
            $details = $fraud_status['details'];
            $this->log_fraudulent_transaction($transaction_id, $details);
        }
    }
}
?>
