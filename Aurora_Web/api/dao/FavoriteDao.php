<?php

require_once __DIR__ . '/../SupabaseClient.php';

class FavoriteDao {

    public function getUserFavoriteProducts($userId) {
        $favRows = SupabaseClient::get('favorites1', 'user_id=eq.' . (int)$userId . '&select=product_id');
        if (count($favRows) === 0) return array();

        $ids = array();
        foreach ($favRows as $row) {
            if (isset($row['product_id'])) $ids[] = (int)$row['product_id'];
        }

        $prodRows = SupabaseClient::get('products1', 'id=in.(' . implode(',', $ids) . ')');
        $list = array();
        foreach ($prodRows as $row) {
            $list[] = array(
                'id'          => (int)(isset($row['id'])          ? $row['id']          : 0),
                'name'        => isset($row['name'])              ? $row['name']        : '',
                'description' => isset($row['description'])       ? $row['description'] : '',
                'price'       => (float)(isset($row['price'])     ? $row['price']       : 0),
                'image_url'   => isset($row['image_url'])         ? $row['image_url']   : '',
                'category'    => isset($row['category'])          ? $row['category']    : '',
                'seller_id'   => (int)(isset($row['seller_id'])   ? $row['seller_id']   : 0),
                'seller_name' => isset($row['seller_name'])       ? $row['seller_name'] : '',
            );
        }
        return $list;
    }

    public function findFavorite($userId, $productId) {
        $rows = SupabaseClient::get('favorites1',
            'user_id=eq.' . (int)$userId . '&product_id=eq.' . (int)$productId . '&limit=1'
        );
        if (count($rows) === 0) return null;
        return array(
            'id'         => (int)(isset($rows[0]['id']) ? $rows[0]['id'] : 0),
            'user_id'    => (int)$userId,
            'product_id' => (int)$productId,
        );
    }

    public function insert($userId, $productId) {
        $result = SupabaseClient::post('favorites1', array(
            'user_id'    => (int)$userId,
            'product_id' => (int)$productId,
        ));
        return $result !== null;
    }

    public function delete($userId, $productId) {
        return SupabaseClient::delete('favorites1',
            'user_id=eq.' . (int)$userId . '&product_id=eq.' . (int)$productId
        );
    }
}
