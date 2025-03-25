#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>

const char* ssid = "ilham";
const char* password = "ywin2020";
const char* serverUrl = "http://192.168.1.5/index/celengan.php";

// Data Telegram
const char* botToken = "8186039702:AAGsA-TW87U07NSi9ZRvgEKyQyQKO7Q_KA0";
const char* telegramApi = "https://api.telegram.org/bot";

// Daftar chat ID untuk beberapa akun atau grup
const char* chatIDs[] = {"1759793177", "1492632397"}; // Ganti dengan chat ID tujuan
const int chatCount = sizeof(chatIDs) / sizeof(chatIDs[0]);

const int trigPin = 5;
const int echoPin = 18;

void setup() {
    Serial.begin(115200);
    WiFi.begin(ssid, password);

    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("Terhubung ke WiFi");

    pinMode(trigPin, OUTPUT);
    pinMode(echoPin, INPUT);
}

void sendTelegramMessage(String message) {
    if (WiFi.status() == WL_CONNECTED) {
        WiFiClientSecure client;
        client.setInsecure(); // Mengabaikan sertifikat SSL

        HTTPClient http;
        for (int i = 0; i < chatCount; i++) { // Loop untuk mengirim ke semua chat ID
            String url = String(telegramApi) + botToken + "/sendMessage?chat_id=" + chatIDs[i] + "&text=" + message;
            http.begin(client, url);
            int httpResponseCode = http.GET();

            Serial.print("Telegram Response ke ");
            Serial.print(chatIDs[i]);
            Serial.print(": ");
            Serial.println(httpResponseCode);
            http.end();
        }
    }
}

void loop() {
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);

    long duration = pulseIn(echoPin, HIGH);
    float distance = duration * 0.034 / 2; // Konversi ke cm

    Serial.print("Jarak: ");
    Serial.print(distance);
    Serial.println(" cm");

    if (distance < 5) {
        Serial.println("Celengan PENUH! Kirim data ke server dan Telegram...");

        // Kirim ke server
        if (WiFi.status() == WL_CONNECTED) {
            HTTPClient http;
            http.begin(serverUrl);
            http.addHeader("Content-Type", "application/x-www-form-urlencoded");

            String postData = "status=penuh&jarak=" + String(distance);
            int httpResponseCode = http.POST(postData);

            Serial.print("Server Response: ");
            Serial.println(httpResponseCode);
            http.end();
        }

        // Kirim notifikasi ke Telegram
        sendTelegramMessage("⚠️ Celengan PENUH! Jarak: " + String(distance) + " cm");
    }

    delay(5000);
}
