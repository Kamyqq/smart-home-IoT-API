# Smart Home IoT API

A complete, production-ready REST API built with **Laravel 13** for managing a Smart Home ecosystem. It provides secure communication between physical IoT devices (like Arduino microcontrollers) and the server, processing real-time telemetry data (temperature, humidity, light levels) and triggering automated actions and alerts.

---

## Key Features

- **Custom Device Authentication:** Secure API endpoints protected by a custom `AuthenticateDevice` middleware using `X-API-KEY` headers.
- **Real-time Data Processing:** Accepts telemetry data via optimized POST endpoints and stores it efficiently.
- **Automated Alert System:** Uses Laravel Events (`FireAlertEvent`, `HighHumidityEvent`) to trigger notifications when sensor thresholds are exceeded.
- **Anti-Spam Protection:** Integrates Laravel Cache to prevent notification flooding (e.g., limiting fire alerts to one per 40 minutes).
- **Service Pattern Architecture:** Clean controllers with complex business logic extracted into a dedicated `MeasurementService`.
- **Optimized Database Queries:** Prevents N+1 query problems by using constrained eager loading (`$query->limit()`) when fetching historical chart data.
- **Hardware Integration:** Includes a production-ready C++ (Arduino) client script demonstrating real-world HTTP communication with the API.
- **High Test Coverage:** Comprehensive test suite written in Pest PHP covering Feature and Unit tests, including Event mocking and API validation.

---

## Technology Stack

| Layer | Technology                                     |
|---|------------------------------------------------|
| Backend Framework | Laravel 13 (PHP 8.2+)                          |
| Testing | Pest PHP                                       |
| Database | SQLite / MySQL (configurable)                  |
| Queue Driver | Database (Laravel Queues)                      |
| Hardware Client | C++ (Arduino UNO R4 WiFi / WiFi S3)            |
| Sensors | DHT11 (Temp & Humidity), Photoresistor (Light) |

---

## Setup & Installation

Follow these steps to run the API locally and connect your physical Arduino device.

### 1. Clone the repository

```bash
git clone https://github.com/Kamyqq/smart-home-IoT-API.git
cd smart-home-IoT-API
```

### 2. Install PHP dependencies

If you don't have PHP installed locally, use Docker:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

### 3. Configure the environment file

```bash
cp .env.example .env
```

Open `.env` and make sure the following values are set correctly:

```dotenv
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite

QUEUE_CONNECTION=database
CACHE_STORE=database
```

> **Important:** The `QUEUE_CONNECTION` must be set to `database` (it is the default in `.env.example`). The alert system relies on queued jobs — if the queue worker is not running, no notifications will be sent.

### 4. Start the Sail environment

```bash
./vendor/bin/sail up -d
```

> **Note:** Laravel Sail binds to port **8000** by default in this project. The API will be available at `http://localhost:8000`.

### 5. Generate the application key and run migrations

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

**What the seeder creates:**

- A test **User**, **House**, **Room**, and **Device**
- A **Bearer Token** — copy this into the `api-test.http` file to test authenticated user endpoints (e.g., fetching chart data)
- An **`X-API-KEY`** — copy this into the Arduino `.ino` file (`arduino/smart_home_client.ino`) to authorize the microcontroller

### 6. Start the queue worker

The alert system dispatches jobs to a queue. **You must start the queue worker** in a separate terminal, otherwise alerts (fire, humidity) will never be processed:

```bash
./vendor/bin/sail artisan queue:work
```

> To keep it running in the background persistently, consider using `--daemon` or a process supervisor like Supervisor. For local development, keeping this terminal open is sufficient.

---

## Network & Firewall Setup (Windows)

To allow the Arduino (or any external device on your local network) to reach your server, you need to:

1. Change your Wi-Fi network profile from **Public** to **Private** in Windows network settings.
2. Open an **inbound firewall rule** for **port 8000** in Windows Defender Firewall.

> Laravel Sail uses Docker, which binds to `0.0.0.0` automatically — your server is already reachable on your local network. You do **not** need to run `php artisan serve`.

---

## Arduino Hardware Client

The `arduino/` directory contains `smart_home_client.ino`, designed to run on a WiFi-enabled microcontroller.

### Before uploading the sketch, edit these three values in the `.ino` file:

```cpp
// Your WiFi network name
const char* ssid = "YOUR_WIFI_SSID";

// Your PC's local IP address (e.g. 192.168.1.100)
const char* server = "YOUR_LOCAL_IP";

// The X-API-KEY generated by the database seeder
const char* apiKey = "YOUR_API_KEY_FROM_SEEDER";
```

> To find your PC's local IP address on Windows, run `ipconfig` in Command Prompt and look for the **IPv4 Address** of your active Wi-Fi adapter.

### How the client works:

1. Connects to the local WiFi network.
2. Reads data from the DHT11 sensor and analog light sensor.
3. Constructs a JSON payload.
4. Sends an authenticated HTTP POST request to `http://<server>:8000/api/...` every 30 seconds.
5. Polls the server via HTTP GET to receive commands (e.g., "Turn on the light") and adjusts physical GPIO pins accordingly.

---

## Testing

The test suite uses Pest PHP and covers:

- **Security:** API Key rejection, invalid tokens, and unauthorized access to other users' rooms (HTTP 401 & 403).
- **Validation:** Form Request rejection of malformed telemetry data (HTTP 422).
- **Business Logic:** Event facade mocking to verify that critical alerts (`FireAlertEvent`, `HighHumidityEvent`) are dispatched correctly.
- **Rate Limiting & Anti-Spam:** Cache implementation verification to guarantee that overlapping emergency events are throttled.

Run the full test suite:

```bash
./vendor/bin/sail pest
```

---

## API Testing (HTTP Client)

The repository includes an `api-test.http` file compatible with tools like JetBrains HTTP Client or the VS Code REST Client extension.

After running the seeder, paste the generated **Bearer Token** into the file and use it to test the authenticated user-facing endpoints (such as fetching chart data for a room).

---

## Quick-Start Checklist

| Step | Command / Action |
|---|---|
| Install dependencies | `composer install` (via Docker if needed) |
| Copy env file | `cp .env.example .env` |
| Set `APP_URL` | `http://localhost:8000` in `.env` |
| Set `QUEUE_CONNECTION` | `database` in `.env` ✅ (default) |
| Start Sail | `./vendor/bin/sail up -d` |
| Run migrations & seed | `./vendor/bin/sail artisan migrate --seed` |
| **Start queue worker** | `./vendor/bin/sail artisan queue:work` ⚠️ |
| Copy API key to Arduino | Paste `X-API-KEY` from seeder output into `smart_home_client.ino` |
| Set Arduino WiFi & IP | Edit `ssid`, `server`, `apiKey` in the `.ino` file |
| Open firewall (Windows) | Allow inbound TCP on port 8000 |
