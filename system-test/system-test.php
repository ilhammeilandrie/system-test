<?php
/**
 * ================================================================
 * SYSTEM TEST - WHITE BOX TESTING (NewsAPI.org REST Client)
 * ================================================================
 * Author      : [Ilham Meilandrie Richardo]
 * Date        : [Rabu 22 Oktober 2025]
 * Description : Pengujian logika internal (white box) menggunakan
 *               cURL, try-catch, dan validasi struktur JSON.
 * ================================================================
 */

// =============== FUNCTION: FETCH DATA (CURL VERSION) ===============
function getNews($country, $apiKey) {
    $url = "https://newsapi.org/v2/top-headlines?country=us&category=sport&apiKey={$apiKey}";

    $headers = [
        "User-Agent: SystemTestApp/1.0", // wajib! sesuai permintaan NewsAPI
        "Accept: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception("Koneksi gagal ke endpoint NewsAPI: " . curl_error($ch));
    }
    curl_close($ch);

    $data = json_decode($response, true);

    if (!isset($data['status']) || $data['status'] != 'ok') {
        throw new Exception("Response API tidak valid: " . json_encode($data));
    }

    return $data;
}

// =============== FUNCTION: VALIDATE STRUCTURE ===============
function validateArticleStructure($article) {
    $required = ['title', 'description', 'url'];
    foreach ($required as $field) {
        if (!isset($article[$field]) || empty($article[$field])) {
            throw new Exception("Field {$field} kosong atau tidak ditemukan.");
        }
    }
    return true;
}

// =============== WHITE BOX TEST CASES ===============
function runTests() {
    $apiKeyValid = "7f18165a975d4bb58d55a139b535c835";
    $apiKeyInvalid = "INVALID_KEY";

    echo "===== WHITE BOX TESTING - SYSTEM TEST (cURL Version) =====\n\n";

    /**
     * TEST 1: API Key Valid
     * Tujuan: Memastikan koneksi ke NewsAPI berhasil jika API key benar.
     * Hasil benar (PASS): Data berhasil diambil, status "ok".
     * Hasil salah (FAIL): API menolak key atau tidak ada koneksi.
     */
    try {
        $result = getNews("us", $apiKeyValid);
        echo "[WBT_API_001] Connection Test: PASS ✅ (Koneksi berhasil dengan API key valid)\n";
    } catch (Exception $e) {
        echo "[WBT_API_001] Connection Test: FAIL ❌ - {$e->getMessage()}\n";
    }

    /**
     * TEST 2: API Key Salah
     * Tujuan: Menguji penanganan error jika API key tidak valid.
     * Hasil benar (PASS): Script mendeteksi error dan tidak crash.
     * Hasil salah (FAIL): Script tetap berjalan tanpa menangkap error.
     */
    try {
        $result = getNews("us", $apiKeyInvalid);
        echo "[WBT_API_002] Invalid Key Handling: FAIL ❌ (Seharusnya error ditangkap)\n";
    } catch (Exception $e) {
        echo "[WBT_API_002] Invalid Key Handling: PASS ✅ - Error caught successfully\n";
    }

    /**
     * TEST 3: Struktur JSON
     * Tujuan: Memastikan struktur data artikel memiliki field wajib.
     * Hasil benar (PASS): Field title, description, dan url lengkap.
     * Hasil salah (FAIL): Salah satu field kosong / tidak ada.
     */
    try {
        $data = getNews("us", $apiKeyValid);
        $firstArticle = $data['articles'][0] ?? [];
        validateArticleStructure($firstArticle);
        echo "[WBT_API_003] JSON Structure Validation: PASS ✅ (Struktur artikel lengkap)\n";
    } catch (Exception $e) {
        echo "[WBT_API_003] JSON Structure Validation: FAIL ❌ - {$e->getMessage()}\n";
    }

    /**
     * TEST 4: Respons Waktu Cepat
     * Tujuan: Mengecek performa — apakah API merespons dalam waktu < 2 detik.
     * Hasil benar (PASS): Waktu respon < 2 detik.
     * Hasil salah (FAIL): Waktu respon >= 2 detik.
     */
    try {
        $start = microtime(true);
        $result = getNews("us", $apiKeyValid);
        $duration = microtime(true) - $start;
        if ($duration < 2) {
            echo "[WBT_API_004] Response Time Test: PASS ✅ ({$duration}s)\n";
        } else {
            echo "[WBT_API_004] Response Time Test: FAIL ❌ (Lama: {$duration}s)\n";
        }
    } catch (Exception $e) {
        echo "[WBT_API_004] Response Time Test: FAIL ❌ - {$e->getMessage()}\n";
    }

    /**
     * TEST 5: Artikel Tidak Kosong
     * Tujuan: Memastikan NewsAPI mengembalikan daftar artikel, bukan array kosong.
     * Hasil benar (PASS): Ada minimal 1 artikel.
     * Hasil salah (FAIL): Data kosong atau tidak ada artikel.
     */
    try {
        $data = getNews("us", $apiKeyValid);
        if (!empty($data['articles'])) {
            echo "[WBT_API_005] Article Count Test: PASS ✅ (".count($data['articles'])." artikel ditemukan)\n";
        } else {
            echo "[WBT_API_005] Article Count Test: FAIL ❌ (Tidak ada artikel dikembalikan)\n";
        }
    } catch (Exception $e) {
        echo "[WBT_API_005] Article Count Test: FAIL ❌ - {$e->getMessage()}\n";
    }

    echo "\n===== TESTING COMPLETE =====\n";
}

// Jalankan semua test
runTests();
?>