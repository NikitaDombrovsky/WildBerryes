<?php

require_once __DIR__ . '/../SupabaseClient.php';

class ProductDao {

    private function fromRow($row) {
        return array(
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

    private function mapRows($rows) {
        $list = array();
        foreach ($rows as $row) {
            $p = $this->fromRow($row);
            if ($p) $list[] = $p;
        }
        return $list;
    }

    public function getAll() {
        return $this->mapRows(SupabaseClient::get('products1', 'order=id.asc'));
    }

    public function getById($id) {
        $rows = SupabaseClient::get('products1', 'id=eq.' . (int)$id . '&limit=1');
        return count($rows) > 0 ? $this->fromRow($rows[0]) : null;
    }

    public function search($query) {
        $q = urlencode($query);
        return $this->mapRows(SupabaseClient::get('products1',
            'or=(name.ilike.*' . $q . '*,description.ilike.*' . $q . '*)'
        ));
    }

    public function getByCategory($category) {
        return $this->mapRows(SupabaseClient::get('products1', 'category=eq.' . urlencode($category)));
    }

    public function getDistinctCategories() {
        $rows = SupabaseClient::get('products1', 'select=category&order=category.asc');
        $cats = array();
        foreach ($rows as $row) {
            $cat = isset($row['category']) ? $row['category'] : '';
            if ($cat !== '' && !in_array($cat, $cats, true)) $cats[] = $cat;
        }
        return $cats;
    }

    public function insert($product) {
        $result = SupabaseClient::post('products1', array(
            'name'        => $product['name'],
            'description' => isset($product['description']) ? $product['description'] : '',
            'price'       => $product['price'],
            'image_url'   => isset($product['image_url'])   ? $product['image_url']   : '',
            'category'    => isset($product['category'])    ? $product['category']    : '',
            'seller_id'   => $product['seller_id'],
            'seller_name' => isset($product['seller_name']) ? $product['seller_name'] : '',
        ));
        return $result ? $this->fromRow($result) : null;
    }

    public function updateImageUrl($id, $imageUrl) {
        return SupabaseClient::patch('products1', 'id=eq.' . (int)$id, array('image_url' => $imageUrl));
    }

    public function deleteBySeller($sellerId) {
        return SupabaseClient::delete('products1', 'seller_id=eq.' . (int)$sellerId);
    }
}
