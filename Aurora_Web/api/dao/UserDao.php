<?php

require_once __DIR__ . '/../SupabaseClient.php';

class UserDao {

    private function fromRow($row) {
        return array(
            'id'         => (int)isset($row['id'])         ? $row['id']         : 0,
            'first_name' => isset($row['first_name'])      ? $row['first_name'] : '',
            'email'      => isset($row['email'])           ? $row['email']      : '',
            'password'   => isset($row['password'])        ? $row['password']   : '',
            'avatar_uri' => isset($row['avatar_uri'])      ? $row['avatar_uri'] : '',
            'banner_uri' => isset($row['banner_uri'])      ? $row['banner_uri'] : '',
            'bio'        => isset($row['bio'])             ? $row['bio']        : '',
        );
    }

    public function findByEmail($email) {
        $rows = SupabaseClient::get('users1', 'email=eq.' . urlencode($email) . '&limit=1');
        return count($rows) > 0 ? $this->fromRow($rows[0]) : null;
    }

    public function getById($id) {
        $rows = SupabaseClient::get('users1', 'id=eq.' . (int)$id . '&limit=1');
        return count($rows) > 0 ? $this->fromRow($rows[0]) : null;
    }

    public function login($email, $password) {
        $rows = SupabaseClient::get('users1',
            'email=eq.' . urlencode($email) . '&password=eq.' . urlencode($password) . '&limit=1'
        );
        return count($rows) > 0 ? $this->fromRow($rows[0]) : null;
    }

    public function insert($user) {
        $result = SupabaseClient::post('users1', array(
            'first_name' => $user['first_name'],
            'email'      => $user['email'],
            'password'   => $user['password'],
            'bio'        => isset($user['bio']) ? $user['bio'] : '',
        ));
        return $result ? $this->fromRow($result) : null;
    }

    public function update($user) {
        return SupabaseClient::patch('users1', 'id=eq.' . (int)$user['id'], array(
            'first_name' => $user['first_name'],
            'bio'        => isset($user['bio'])        ? $user['bio']        : '',
            'avatar_uri' => isset($user['avatar_uri']) ? $user['avatar_uri'] : '',
            'banner_uri' => isset($user['banner_uri']) ? $user['banner_uri'] : '',
        ));
    }
}
