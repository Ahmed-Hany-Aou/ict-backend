# Fix File Upload Issue - 422 Error

## Problem
PHP upload limits are too small:
- `upload_max_filesize = 2M` (current)
- `post_max_size = 8M` (current)

Payment screenshots need at least 5MB.

## Solution: Update PHP Configuration

### For MAMP:

1. **Locate php.ini file:**
   ```
   C:\MAMP\conf\php{version}\php.ini
   ```
   For example: `C:\MAMP\conf\php8.3.1\php.ini`

2. **Edit php.ini:**
   Open the file in a text editor and find these lines:
   ```ini
   upload_max_filesize = 2M
   post_max_size = 8M
   max_file_uploads = 20
   ```

3. **Change to:**
   ```ini
   upload_max_filesize = 10M
   post_max_size = 20M
   max_file_uploads = 20
   ```

4. **Save the file**

5. **Restart MAMP:**
   - Stop MAMP servers
   - Start MAMP servers again

6. **Verify the changes:**
   ```bash
   cd "C:\MAMP\htdocs\project 28\ict-backend"
   php -i | grep -E "(upload_max_filesize|post_max_size)"
   ```

   Should show:
   ```
   post_max_size => 20M => 20M
   upload_max_filesize => 10M => 10M
   ```

## Alternative: Create .user.ini

If you can't edit php.ini, create a `.user.ini` file in the public directory:

```bash
cd "C:\MAMP\htdocs\project 28\ict-backend\public"
echo "upload_max_filesize = 10M" > .user.ini
echo "post_max_size = 20M" >> .user.ini
```

Then restart MAMP.

## Testing

After making changes:

1. Go to Filament admin panel: `http://127.0.0.1:8000/admin`
2. Navigate to "Payment Approvals" → "New Payment"
3. Try uploading a screenshot
4. Should work now! ✅

## Already Fixed in Laravel Config

✅ Storage symlink created
✅ Livewire temp upload configured (5MB max)
✅ Filament FileUpload component configured
✅ Directories created with proper permissions

Only PHP limits need to be increased now.
