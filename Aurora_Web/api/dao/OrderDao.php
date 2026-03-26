<?php

require_once __DIR__ . '/../SupabaseClient.php';

class OrderDao {

    private function fromRow($row) {
        return array(
            'id'            => (int)(isset($row['id'])            ? $row['id']            : 0),
            'user_id'       => (int)(isset($row['user_id'])       ? $row['user_id']       : 0),
            'product_id'    => (int)(isset($row['product_id'])    ? $row['product_id']    : 0),
            'product_name'  => isset($row['product_name'])        ? $row['product_name']  : '',
            'product_price' => (float)(isset($row['product_price']) ? $row['product_price'] : 0),
            'status'        => isset($row['status'])              ? $row['status']        : 'В пути',
            'order_date'    => (int)(isset($row['order_date'])    ? $row['order_date']    : 0),
        );
    }

    public function getUserOrders($userId) {
        $rows = SupabaseClient::get('orders1', 'user_id=eq.' . (int)$userId . '&order=order_date.desc');
        $list = array();
        foreach ($rows as $row) {
            $o = $this->fromRow($row);
            if ($o) $list[] = $o;
        }
        return $list;
    }

    public function insert($order) {
        $result = SupabaseClient::post('orders1', array(
            'user_id'       => $order['user_id'],
            'product_id'    => $order['product_id'],
            'product_name'  => $order['product_name'],
            'product_price' => $order['product_price'],
            'status'        => isset($order['status'])     ? $order['status']     : 'В пути',
            'order_date'    => isset($order['order_date']) ? $order['order_date'] : time() * 1000,
        ));
        return $result ? $this->fromRow($result) : null;
    }

    public function delete($orderId) {
        return SupabaseClient::delete('orders1', 'id=eq.' . (int)$orderId);
    }
}
