#!/bin/bash

# Payment Upload Test Script
# Usage: Replace YOUR_TOKEN_HERE with your actual auth token

echo "🧪 Testing Payment Upload API"
echo "========================================"
echo ""

# Configuration
BASE_URL="http://127.0.0.1:8000"
TOKEN="YOUR_TOKEN_HERE"  # ⚠️ Replace with your actual token!

# Create a test image
echo "1️⃣  Creating test image..."
echo -n "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==" | base64 -d > test_screenshot.png
echo "✅ Test image created ($(stat -f%z test_screenshot.png) bytes)"
echo ""

# Submit payment
echo "2️⃣  Submitting payment with screenshot..."
echo ""

curl -X POST "$BASE_URL/api/payments/submit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -F "payment_reference=TEST_$(date +%s)" \
  -F "amount=199.00" \
  -F "screenshot=@test_screenshot.png" \
  -v

echo ""
echo ""
echo "3️⃣  Cleaning up..."
rm test_screenshot.png
echo "✅ Test image removed"
echo ""

echo "📝 Notes:"
echo "  - Field name MUST be 'screenshot' (not 'file')"
echo "  - Must use -F flag (multipart/form-data)"
echo "  - Image must be valid image file"
echo ""
echo "👉 Check result in admin panel: $BASE_URL/admin/payments"
