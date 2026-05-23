#!/usr/bin/env bash
# Build MedRep Fleet MR app APK into ../mobile_app_apk/
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FLUTTER_ROOT="${FLUTTER_ROOT:-$ROOT/tools/flutter}"
ANDROID_HOME="${ANDROID_HOME:-$ROOT/tools/android-sdk}"
API_BASE_URL="${API_BASE_URL:-http://10.0.20.170/live/api/v1}"
OUT_DIR="$ROOT/mobile_app_apk"

export JAVA_HOME="${JAVA_HOME:-$ROOT/tools/jdk-17}"
export PATH="$FLUTTER_ROOT/bin:$JAVA_HOME/bin:$ANDROID_HOME/cmdline-tools/latest/bin:$ANDROID_HOME/platform-tools:$PATH"
export ANDROID_SDK_ROOT="$ANDROID_HOME"

cd "$ROOT/mobile_app"
flutter pub get
flutter build apk --release \
  --dart-define=API_BASE_URL="$API_BASE_URL"

mkdir -p "$OUT_DIR"
APK_SRC="build/app/outputs/flutter-apk/app-release.apk"
STAMP="$(date +%Y%m%d-%H%M%S)"
APK_DST="$OUT_DIR/medrep-fleet-mr-${STAMP}.apk"
cp "$APK_SRC" "$APK_DST"
ln -sfn "$(basename "$APK_DST")" "$OUT_DIR/medrep-fleet-mr-latest.apk"

echo "Built: $APK_DST"
ls -lh "$APK_DST" "$OUT_DIR/medrep-fleet-mr-latest.apk"
