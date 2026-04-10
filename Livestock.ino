#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>

// ---------------- WIFI ----------------
const char* ssid = "rrr";
const char* password = "123456789";
const char* serverUrl = "http://192.168.137.1/livestock";

// ---------------- OLED ----------------
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_SDA 21
#define OLED_SCL 22
#define SCREEN_ADDRESS 0x3C

Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);

// ---------------- RFID ----------------
#define SS_PIN   5
#define RST_PIN  27
#define SCK_PIN  18
#define MISO_PIN 19
#define MOSI_PIN 23

MFRC522 mfrc522(SS_PIN, RST_PIN);

// ---------------- OUTPUT ----------------
#define GREEN_LED   12
#define RED_LED     13
#define BLUE_LED    25
#define BUTTON_PIN  14
#define BUZZER_PIN  26

// ---------------- VARIABLES ----------------
bool registerMode = false;
unsigned long lastScan = 0;

// ---------------- DISPLAY ----------------
void show(String l1, String l2 = "") {
  display.clearDisplay();
  display.setCursor(0, 0);
  display.println(l1);
  display.setCursor(0, 20);
  display.println(l2);
  display.display();
}

// ---------------- BUZZER ----------------
void beep(int freq, int dur) {
  tone(BUZZER_PIN, freq);
  delay(dur);
  noTone(BUZZER_PIN);
}

// ---------------- WIFI ----------------
void connectWiFi() {
  show("Connecting WiFi...");
  WiFi.begin(ssid, password);

  int retry = 0;
  while (WiFi.status() != WL_CONNECTED && retry < 20) {
    delay(500);
    retry++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    show("WiFi OK", WiFi.localIP().toString());
    beep(1500, 200);
  } else {
    show("WiFi Failed");
  }
}

// ---------------- RFID ----------------
void initRFID() {
  SPI.begin(SCK_PIN, MISO_PIN, MOSI_PIN, SS_PIN);
  mfrc522.PCD_Init();

  byte v = mfrc522.PCD_ReadRegister(mfrc522.VersionReg);

  if (v == 0x91 || v == 0x92) {
    show("RFID Ready");
    beep(2000, 200);
  } else {
    show("RFID ERROR");
    //while(true);
  }
}

// ---------------- READ TAG ----------------
String readTag() {
  if (!mfrc522.PICC_IsNewCardPresent()) return "";
  if (!mfrc522.PICC_ReadCardSerial()) return "";

  String tag = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) tag += "0";
    tag += String(mfrc522.uid.uidByte[i], HEX);
  }

  tag.toUpperCase();

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();

  return tag;
}

// ---------------- REGISTER ----------------
void registerAnimal(String tag) {
  show("Registering...", tag);

  HTTPClient http;
  http.begin(String(serverUrl) + "/create_animal.php");
  http.addHeader("Content-Type", "application/json");

  String body = "{\"tagId\":\"" + tag + "\"}";

  int code = http.POST(body);

  if (code == 200) {
    show("Registered OK");
    digitalWrite(GREEN_LED, HIGH);
    beep(2000, 300);
  } else {
    show("Register Failed");
    digitalWrite(RED_LED, HIGH);
  }

  delay(1500);
  digitalWrite(GREEN_LED, LOW);
  digitalWrite(RED_LED, LOW);
  http.end();
}

// ---------------- CHECK ----------------
void checkAnimal(String tag) {
  show("Checking...", tag);

  HTTPClient http;
  http.begin(String(serverUrl) + "/get_animal_by_tag.php?tagId=" + tag);

  int code = http.GET();

  if (code == 200) {
    DynamicJsonDocument doc(512);
    deserializeJson(doc, http.getString());

    bool success = doc["success"];
    String name = doc["name"];
    bool isSick = doc["isSick"];

    if (success) {
      show(name, isSick ? "SICK" : "HEALTHY");

      digitalWrite(isSick ? RED_LED : GREEN_LED, HIGH);
      beep(isSick ? 700 : 2000, 400);
    } else {
      show("NOT FOUND");
      digitalWrite(RED_LED, HIGH);
    }
  } else {
    show("SERVER ERROR");
  }

  delay(1500);
  digitalWrite(GREEN_LED, LOW);
  digitalWrite(RED_LED, LOW);
  http.end();
}

// ---------------- SETUP ----------------
void setup() {
  Serial.begin(115200);

  pinMode(GREEN_LED, OUTPUT);
  pinMode(RED_LED, OUTPUT);
  pinMode(BLUE_LED, OUTPUT);
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  pinMode(BUZZER_PIN, OUTPUT);

  Wire.begin(OLED_SDA, OLED_SCL);
  display.begin(SSD1306_SWITCHCAPVCC, SCREEN_ADDRESS);

  display.setTextSize(1);
  display.setTextColor(WHITE);

  show("System Starting");

  initRFID();
  connectWiFi();

  show("Scan RFID");
}

// ---------------- LOOP ----------------
void loop() {
  Serial.println(digitalRead(BUTTON_PIN));
  // BUTTON TOGGLE
  if (digitalRead(BUTTON_PIN) == LOW) {
    delay(300);
    registerMode = !registerMode;

    show(registerMode ? "REGISTER MODE" : "NORMAL MODE");
    digitalWrite(BLUE_LED, registerMode);
    delay(1000);
    show("Scan RFID");
  }

  // READ TAG
  String tag = readTag();

  if (tag != "" && millis() - lastScan > 2000) {
    lastScan = millis();

    Serial.println("TAG: " + tag);

    if (registerMode) {
      registerAnimal(tag);
    } else {
      checkAnimal(tag);
    }

    show("Scan RFID");
  }
}
