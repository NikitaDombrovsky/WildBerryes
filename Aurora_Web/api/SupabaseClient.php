<?php

class SupabaseClient {

    private static $BASE        = 'https://varjvazplnphxbtiinex.supabase.co';
    private static $KEY         = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZhcmp2YXpwbG5waHhidGlpbmV4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzM1MTc3NzcsImV4cCI6MjA4OTA5Mzc3N30.QgJfx-XsVzA9lAoKgTROIixnet1QICCzQF8tWM274O0';

    private static function restUrl() {
        return self::$BASE . '/rest/v1/';
    }

    private static function storageUrl() {
        return self::$BASE . '/storage/v1/object/';
    }

    private static function request($method, $url, $body, $extraHeaders) {
        $headers = array_merge(array(
            'apikey: '               . self::$KEY,
            'Authorization: Bearer ' . self::$KEY,
            'Content-Type: application/json',
        ), $extraHeaders);

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
        ));
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return array($code, $response ? $response : '');
    }

    public static function get($table, $query) {
        $url    = self::restUrl() . $table . ($query ? '?' . $query : '');
        $result = self::request('GET', $url, null, array());
        $data   = json_decode($result[1], true);
        return is_array($data) ? $data : array();
    }

    public static function post($table, $data) {
        $url    = self::restUrl() . $table;
        $result = self::request('POST', $url, json_encode($data), array('Prefer: return=representation'));
        if ($result[0] < 200 || $result[0] >= 300) return null;
        $rows = json_decode($result[1], true);
        return (is_array($rows) && count($rows) > 0) ? $rows[0] : null;
    }

    public static function patch($table, $query, $data) {
        $url    = self::restUrl() . $table . '?' . $query;
        $result = self::request('PATCH', $url, json_encode($data), array());
        return $result[0] >= 200 && $result[0] < 300;
    }

    public static function delete($table, $query) {
        $url    = self::restUrl() . $table . '?' . $query;
        $result = self::request('DELETE', $url, null, array());
        return $result[0] >= 200 && $result[0] < 300;
    }

    public static function uploadFile($bucket, $path, $tmpPath, $mime) {
        $url   = self::storageUrl() . $bucket . '/' . $path;
        $bytes = file_get_contents($tmpPath);
        if ($bytes === false) return null;

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $bytes,
            CURLOPT_HTTPHEADER     => array(
                'apikey: '               . self::$KEY,
                'Authorization: Bearer ' . self::$KEY,
                'Content-Type: '         . $mime,
                'x-upsert: true',
            ),
            CURLOPT_TIMEOUT => 30,
        ));
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) return null;
        return self::$BASE . '/storage/v1/object/public/' . $bucket . '/' . $path;
    }
}
