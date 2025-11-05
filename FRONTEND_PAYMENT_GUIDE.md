# 📤 Frontend Payment Upload - Implementation Guide

## ⚠️ Common Mistakes

Your error `{"success": false, "message": "Validation failed", "errors": {"screenshot": ["The screenshot failed to upload."]}}` is caused by:

1. ❌ **Wrong field name** - Using `file` instead of `screenshot`
2. ❌ **Missing enctype** - Not using `multipart/form-data`
3. ❌ **Wrong Content-Type** - Sending as JSON instead of FormData

---

## ✅ Correct Implementation

### React + Axios Example

```jsx
import { useState } from 'react';
import axios from 'axios';

function PaymentUploadForm() {
  const [paymentReference, setPaymentReference] = useState('');
  const [amount, setAmount] = useState('199.00');
  const [screenshot, setScreenshot] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleFileChange = (e) => {
    const file = e.target.files[0];

    // Validate file size (max 10MB)
    if (file && file.size > 10 * 1024 * 1024) {
      setError('File size must not exceed 10MB');
      return;
    }

    // Validate file type
    if (file && !file.type.startsWith('image/')) {
      setError('Please select an image file');
      return;
    }

    setScreenshot(file);
    setError(null);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      // ✅ IMPORTANT: Create FormData object
      const formData = new FormData();

      // ✅ CRITICAL: Field name MUST be "screenshot" (not "file")
      formData.append('payment_reference', paymentReference);
      formData.append('amount', amount);
      formData.append('screenshot', screenshot); // ⚠️ Must be "screenshot"!

      // ✅ IMPORTANT: Don't set Content-Type header manually!
      // Axios will automatically set it to multipart/form-data
      const response = await axios.post(
        'http://127.0.0.1:8000/api/payments/submit',
        formData,
        {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json',
            // ❌ DON'T DO THIS: 'Content-Type': 'application/json'
            // ❌ DON'T DO THIS: 'Content-Type': 'multipart/form-data'
            // ✅ Let Axios set Content-Type automatically with boundary
          }
        }
      );

      if (response.data.success) {
        alert('Payment submitted successfully!');
        // Reset form
        setPaymentReference('');
        setScreenshot(null);
      }
    } catch (err) {
      console.error('Payment upload error:', err);

      if (err.response?.data?.errors) {
        // Display validation errors
        const errors = Object.values(err.response.data.errors).flat();
        setError(errors.join(', '));
      } else {
        setError(err.response?.data?.message || 'Failed to submit payment');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <div>
        <label>Payment Reference:</label>
        <input
          type="text"
          value={paymentReference}
          onChange={(e) => setPaymentReference(e.target.value)}
          required
          placeholder="e.g., BANK_12345678"
        />
      </div>

      <div>
        <label>Amount:</label>
        <input
          type="number"
          step="0.01"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
          required
          placeholder="199.00"
        />
      </div>

      <div>
        <label>Payment Screenshot:</label>
        <input
          type="file"
          accept="image/*"
          onChange={handleFileChange}
          required
        />
        {screenshot && (
          <p>Selected: {screenshot.name} ({(screenshot.size / 1024 / 1024).toFixed(2)} MB)</p>
        )}
      </div>

      {error && <div style={{color: 'red'}}>{error}</div>}

      <button type="submit" disabled={loading || !screenshot}>
        {loading ? 'Uploading...' : 'Submit Payment'}
      </button>
    </form>
  );
}

export default PaymentUploadForm;
```

---

### Vanilla JavaScript + Fetch Example

```html
<!DOCTYPE html>
<html>
<body>
  <form id="paymentForm">
    <input type="text" id="reference" placeholder="Payment Reference" required>
    <input type="number" id="amount" placeholder="Amount" value="199.00" required>

    <!-- ⚠️ IMPORTANT: name MUST be "screenshot" -->
    <input type="file" id="screenshot" name="screenshot" accept="image/*" required>

    <button type="submit">Submit Payment</button>
  </form>

  <div id="result"></div>

  <script>
    document.getElementById('paymentForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      // ✅ IMPORTANT: Use FormData
      const formData = new FormData();

      // ✅ CRITICAL: Field name MUST be "screenshot"
      formData.append('payment_reference', document.getElementById('reference').value);
      formData.append('amount', document.getElementById('amount').value);
      formData.append('screenshot', document.getElementById('screenshot').files[0]);

      try {
        const response = await fetch('http://127.0.0.1:8000/api/payments/submit', {
          method: 'POST',
          headers: {
            'Authorization': 'Bearer YOUR_TOKEN_HERE',
            'Accept': 'application/json'
            // ❌ DON'T set 'Content-Type' - fetch does it automatically
          },
          body: formData // ⚠️ Send FormData directly, not JSON
        });

        const data = await response.json();

        if (data.success) {
          document.getElementById('result').innerHTML =
            '<p style="color: green;">✅ Payment submitted!</p>';
        } else {
          document.getElementById('result').innerHTML =
            '<p style="color: red;">❌ ' + data.message + '</p>';
        }
      } catch (error) {
        console.error('Error:', error);
        document.getElementById('result').innerHTML =
          '<p style="color: red;">❌ Network error</p>';
      }
    });
  </script>
</body>
</html>
```

---

## 🔍 Debugging Checklist

### 1️⃣ Check Field Name
```javascript
// ❌ WRONG
formData.append('file', screenshot);
formData.append('image', screenshot);
formData.append('payment_screenshot', screenshot);

// ✅ CORRECT
formData.append('screenshot', screenshot);
```

### 2️⃣ Check Content-Type
```javascript
// ❌ WRONG - Don't set Content-Type manually
headers: {
  'Content-Type': 'application/json' // ❌ This breaks file upload!
}

// ❌ WRONG - Don't set Content-Type for multipart/form-data
headers: {
  'Content-Type': 'multipart/form-data' // ❌ Missing boundary!
}

// ✅ CORRECT - Let the browser/axios set it automatically
headers: {
  'Authorization': 'Bearer YOUR_TOKEN',
  'Accept': 'application/json'
  // ✅ No Content-Type - browser adds it with boundary
}
```

### 3️⃣ Check Request Body
```javascript
// ❌ WRONG - Sending JSON
body: JSON.stringify({ screenshot: file })

// ❌ WRONG - Sending file directly
body: screenshot

// ✅ CORRECT - Sending FormData
const formData = new FormData();
formData.append('screenshot', screenshot);
body: formData
```

---

## 🧪 Testing Your Frontend

### Open Browser Console (F12) and check:

1. **Network Tab:**
   ```
   Request URL: http://127.0.0.1:8000/api/payments/submit
   Request Method: POST
   Content-Type: multipart/form-data; boundary=----WebKitFormBoundary...

   Form Data:
   ✅ payment_reference: TEST123
   ✅ amount: 199.00
   ✅ screenshot: [file]
   ```

2. **Check File Is Attached:**
   - Look for `screenshot: (binary)` or filename in form data
   - File size should be visible

3. **Check Response:**
   ```json
   {
     "success": true,
     "message": "Payment submitted successfully. Waiting for admin approval.",
     "payment": { ... }
   }
   ```

---

## 🐛 Common Errors & Solutions

### Error: "The screenshot failed to upload"
**Cause:** Field name is wrong or file wasn't sent
**Solution:**
```javascript
// Make sure field name is exactly "screenshot"
formData.append('screenshot', file); // ⚠️ Not 'file', not 'image'
```

### Error: "Validation failed"
**Cause:** Request isn't multipart/form-data
**Solution:**
```javascript
// Use FormData, don't send JSON
const formData = new FormData();
formData.append('screenshot', file);
// Send formData, not JSON.stringify()
```

### Error: "The uploaded file exceeds the upload_max_filesize"
**Cause:** PHP upload limits too small
**Solution:** See `FIX_UPLOAD_ISSUE.md` for PHP configuration

### Error: "screenshot must be an image file"
**Cause:** File isn't a valid image
**Solution:**
```javascript
// Validate file type before upload
if (!file.type.startsWith('image/')) {
  alert('Please select an image file');
  return;
}
```

---

## 📋 Backend Requirements Summary

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `payment_reference` | string | ✅ Yes | Max 255 chars |
| `amount` | number | ✅ Yes | Min 0 |
| `screenshot` | file | ⚠️ Optional | Image, max 10MB |

**Field name must be exactly:** `screenshot` (lowercase, no plural)

---

## 🎯 Quick Test with cURL

Test your backend works before fixing frontend:

```bash
# Create test image
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==" | base64 -d > test.png

# Test upload (replace YOUR_TOKEN)
curl -X POST http://127.0.0.1:8000/api/payments/submit \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "payment_reference=TEST123" \
  -F "amount=199.00" \
  -F "screenshot=@test.png"

# Clean up
rm test.png
```

If this works, your **backend is fine** - fix your **frontend**.

---

## ✅ Final Checklist

Before submitting form:

- [ ] Using `FormData()` object
- [ ] Field name is `screenshot` (not `file` or `image`)
- [ ] Not setting `Content-Type` header manually
- [ ] Not sending as JSON
- [ ] File is actual File object from input
- [ ] Authorization header is present
- [ ] Endpoint is `/api/payments/submit`
- [ ] Method is POST

---

## 📞 Still Not Working?

1. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check browser Network tab:**
   - Is `screenshot` in the form data?
   - Is Content-Type `multipart/form-data`?
   - What's the actual error response?

3. **Test backend with cURL first:**
   - If cURL works → Frontend issue
   - If cURL fails → Backend issue

4. **Enable debug logging:**
   ```javascript
   // In your frontend code:
   console.log('FormData entries:', [...formData.entries()]);
   console.log('File:', screenshot);
   ```

---

**Remember:** The field name MUST be `screenshot`, and you MUST use `multipart/form-data` (FormData)!

🎉 **Good luck!**
