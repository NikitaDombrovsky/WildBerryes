package com.example.bulbulyator;

import android.content.Context;
import android.content.SharedPreferences;
import android.util.Log;

import java.io.ByteArrayOutputStream;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;

public class SeedProducts {

    private static final String PREFS = "SeedPrefs";
    private static final String KEY   = "seeded_v10";
    private static final String TAG   = "SeedProducts";

    // {name, description, price, unsplash_url, category}
    private static final Object[][] ITEMS = {
        // Электроника
        {"Айфон 15 Pro", "Смартфон Apple с чипом A17 Pro, 256 ГБ", 99990,
            "https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&q=80", "Электроника"},
        {"Samsung Galaxy S24", "Флагманский Android-смартфон, 128 ГБ", 79990,
            "https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=600&q=80", "Электроника"},
        {"Ноутбук ASUS ROG", "Игровой ноутбук, RTX 4060, 16 ГБ RAM", 89990,
            "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&q=80", "Электроника"},
        {"Наушники Sony WH-1000XM5", "Беспроводные с шумоподавлением", 24990,
            "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&q=80", "Электроника"},
        {"iPad Pro 12.9", "Планшет Apple M2, 256 ГБ, Wi-Fi", 74990,
            "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&q=80", "Электроника"},
        {"Умные часы Apple Watch 9", "GPS, датчик ЧСС, 45 мм", 34990,
            "https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600&q=80", "Электроника"},
        // Автотовары
        {"Видеорегистратор 4K", "Широкоугольная камера с ночным режимом", 4990,
            "https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=600&q=80", "Автотовары"},
        {"Автомобильный пылесос", "Беспроводной, 120 Вт, с насадками", 2490,
            "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80", "Автотовары"},
        {"Зимние шины R17", "Комплект 4 шт, Michelin X-Ice", 24990,
            "https://images.unsplash.com/photo-1580273916550-e323be2ae537?w=600&q=80", "Автотовары"},
        {"Автомобильный компрессор", "Цифровой, 150 PSI, с LED-фонарём", 3490,
            "https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=600&q=80", "Автотовары"},
        // Одежда
        {"Кроссовки Nike Air Max", "Мужские, размер 42, белые", 8990,
            "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&q=80", "Одежда"},
        {"Куртка зимняя", "Пуховик мужской, размер L, чёрный", 12990,
            "https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&q=80", "Одежда"},
        {"Джинсы Levi's 501", "Классические прямые, размер 32/32", 5990,
            "https://images.unsplash.com/photo-1542272604-787c3835535d?w=600&q=80", "Одежда"},
        {"Платье летнее", "Женское, цветочный принт, размер M", 3490,
            "https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=600&q=80", "Одежда"},
        // Дом и сад
        {"Кофемашина DeLonghi", "Автоматическая, с капучинатором", 39990,
            "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600&q=80", "Дом и сад"},
        {"Робот-пылесос", "Умный, с картой помещения, Wi-Fi", 29990,
            "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80", "Дом и сад"},
        {"Диван угловой", "Раскладной, ткань, серый, 250x150 см", 49990,
            "https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600&q=80", "Дом и сад"},
        {"Настольная лампа LED", "Диммируемая, 3 режима, USB-зарядка", 1990,
            "https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600&q=80", "Дом и сад"},
        // Спорт
        {"Велосипед горный", "21 скорость, алюминиевая рама, 26\"", 19990,
            "https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=600&q=80", "Спорт"},
        {"Гантели разборные", "Комплект 2x20 кг, с подставкой", 7990,
            "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&q=80", "Спорт"},
    };

    public static void seedIfNeeded(Context ctx) {
        SharedPreferences prefs = ctx.getSharedPreferences(PREFS, Context.MODE_PRIVATE);
        if (prefs.getBoolean(KEY, false)) return;

        new Thread(() -> {
            try {
                SupabaseProductDao dao = SupabaseDb.getInstance().productDao();
                dao.deleteBySeller(0);

                for (Object[] item : ITEMS) {
                    String name        = (String) item[0];
                    String description = (String) item[1];
                    double price       = ((Number) item[2]).doubleValue();
                    String imageSource = (String) item[3];
                    String category    = (String) item[4];

                    // Скачиваем картинку и загружаем в Supabase Storage
                    String storedUrl = uploadImageToStorage(name, imageSource);
                    // Если загрузка не удалась — используем оригинальный URL
                    if (storedUrl == null) storedUrl = imageSource;

                    Product p = new Product();
                    p.name        = name;
                    p.description = description;
                    p.price       = price;
                    p.imageUrl    = storedUrl;
                    p.category    = category;
                    p.sellerId    = 0;
                    p.sellerName  = "Магазин";
                    dao.insert(p);

                    Log.d(TAG, "Seeded: " + name + " -> " + storedUrl);
                }

                prefs.edit().putBoolean(KEY, true).apply();
                Log.d(TAG, "Seed complete");
            } catch (Exception e) {
                Log.e(TAG, "Seed error", e);
            }
        }).start();
    }

    /** Скачивает картинку по URL и загружает в Supabase Storage bucket "products" */
    private static String uploadImageToStorage(String productName, String imageUrl) {
        try {
            // Скачиваем байты
            HttpURLConnection conn = (HttpURLConnection) new URL(imageUrl).openConnection();
            conn.setConnectTimeout(15000);
            conn.setReadTimeout(15000);
            conn.setRequestProperty("User-Agent", "Mozilla/5.0");
            conn.connect();
            if (conn.getResponseCode() != 200) return null;

            InputStream is = conn.getInputStream();
            ByteArrayOutputStream bos = new ByteArrayOutputStream();
            byte[] buf = new byte[8192];
            int n;
            while ((n = is.read(buf)) != -1) bos.write(buf, 0, n);
            is.close();
            conn.disconnect();
            byte[] bytes = bos.toByteArray();

            // Формируем путь в storage
            String safeName = productName.replaceAll("[^a-zA-Z0-9а-яА-Я]", "_").toLowerCase();
            String path = "seed/" + safeName + "_" + System.currentTimeMillis() + ".jpg";

            return SupabaseClient.uploadBytes("products", path, bytes, "image/jpeg");
        } catch (Exception e) {
            Log.e(TAG, "uploadImageToStorage error for " + productName, e);
            return null;
        }
    }
}
