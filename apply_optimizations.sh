#!/bin/bash

echo "====================================="
echo "Applying Performance Optimizations"
echo "====================================="

# Backup existing files
echo "Creating backups..."
cp app/Http/Controllers/Api/ChapterController.php app/Http/Controllers/Api/ChapterController.php.backup_$(date +%Y%m%d_%H%M%S)
cp app/Http/Controllers/Api/SlideController.php app/Http/Controllers/Api/SlideController.php.backup_$(date +%Y%m%d_%H%M%S)

echo "Backups created successfully!"

echo "Applying optimizations..."
echo "Note: Due to file locking issues, please manually apply the changes."
echo "Optimization files have been prepared."

echo "====================================="
echo "Next Steps:"
echo "1. Run: php artisan migrate"
echo "2. Clear cache: php artisan cache:clear"
echo "3. Test the optimized endpoints"
echo "====================================="
