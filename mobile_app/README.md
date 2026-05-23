# MedRep Fleet — Mobile App (Flutter)

Field employee app for **Medical Representatives** (MRs). Connects to the Laravel backend via REST API (`/api/v1`).

## Prerequisites

- [Flutter SDK](https://docs.flutter.dev/get-started/install) 3.16+
- Android Studio / Xcode for device emulators
- Laravel backend running (`php artisan serve` from project root)

## First-time setup

```bash
cd /var/www/html/fleet_test/mobile_app

# Generate android/ and ios/ platform folders (if missing)
flutter create . --project-name medrep_fleet

flutter pub get
```

## API base URL

Edit `lib/config/api_config.dart` or pass at build time:

| Environment | URL |
|-------------|-----|
| Android emulator | `http://10.0.2.2:8000/api/v1` |
| iOS simulator | `http://127.0.0.1:8000/api/v1` |
| Physical device | `http://YOUR_PC_LAN_IP:8000/api/v1` |

```bash
flutter run --dart-define=API_BASE_URL=http://192.168.1.100:8000/api/v1
```

## Run the app

```bash
flutter run
```

## Login

Use an MR (representative) account from your company — **company admins cannot use this app**.

## Features

- **Today** — dashboard, clock in/out, live tracking status, reminders
- **Live GPS** — latitude, longitude, speed, accuracy sent to server every 30s (60s in background)
- **Background tracking** — continues when app is minimized (position stream + timer)
- **Route history** — map with distance, stops, visit markers
- **Movement log** — full GPS ping history for the day
- **Geo-fencing** — auto check-in/out when entering/leaving customer zones (150m default)
- **Visits** — start visit, GPS check-in/out, photos, notes
- **Customers** — list, search, add doctors/chemists/etc.
- **Expenses** — submit with receipt photo
- **Alerts** — follow-ups, targets, meetings, doctor revisits

## Backend API

The Laravel app exposes token auth at:

- `POST /api/v1/login`
- `GET /api/v1/dashboard`
- Visits, customers, expenses, tracking, notifications — see `routes/api.php`

Requires **Laravel Sanctum** (installed in parent project).

## Permissions

Add to `android/app/src/main/AndroidManifest.xml` (after `flutter create`):

```xml
<uses-permission android:name="android.permission.INTERNET"/>
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION"/>
<uses-permission android:name="android.permission.CAMERA"/>
```

iOS: `NSLocationWhenInUseUsageDescription`, `NSCameraUsageDescription` in `Info.plist`.
