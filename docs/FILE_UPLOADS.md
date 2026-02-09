# File Uploads

Complete guide to uploading images, videos, and files to Etsy listings using the SDK.

## Table of Contents

- [Overview](#overview)
- [Image Uploads](#image-uploads)
- [Video Uploads](#video-uploads)
- [File Uploads](#file-uploads)
- [Upload Methods](#upload-methods)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## Overview

Etsy listings support three types of file uploads:

1. **Images**: Product photos (required for listings)
2. **Videos**: Product demonstration videos
3. **Files**: Downloadable files (for digital products)

The SDK provides **basic** support for file uploads with automatic handling of multipart form-data requests.

### Quick Example

```php
use Etsy\Resources\Listing;

$listing = Listing::get($listingId);

// Upload an image
$listing->uploadImage('./photos/product.jpg', [
    'rank' => 1,
    'alt_text' => 'Product front view'
]);

// Upload a video
$listing->uploadVideo('./videos/demo.mp4', 'product-demo.mp4');

// Upload a downloadable file
$listing->uploadFile('./downloads/template.pdf', 'Template.pdf');
```

## Image Uploads

### Overview

- **Maximum**: 10 images per listing
- **Required**: At least 1 image per active listing
- **Formats**: JPG, PNG, GIF
- **Recommended Size**: 2000x2000 pixels minimum
- **File Size**: Up to 10MB per image
- **Rank**: Display order (1-10)

### Upload Methods

#### Using Listing Instance Method

```php
$listing = Listing::get($listingId);

$image = $listing->uploadImage('./path/to/image.jpg', [
    'rank' => 1,
    'alt_text' => 'Product description',
    'overwrite' => false,
    'is_watermarked' => false
]);
```

**Parameters**:
- **$image** (string|resource): File path or file resource
- **$options** (array):
  - `rank` (int): Display order (1-10), default: lowest available
  - `alt_text` (string): Alt text for accessibility
  - `overwrite` (bool): Replace image at same rank, default: false
  - `is_watermarked` (bool): Whether image is watermarked

#### Using ListingImage Static Method

```php
use Etsy\Resources\ListingImage;

$image = ListingImage::create($shopId, $listingId, [
    'image' => './path/to/image.jpg',
    'rank' => 1,
    'alt_text' => 'Product front view'
]);
```

### File Path Upload

The simplest method - provide a file path:

```php
$listing->uploadImage('./photos/product-1.jpg', [
    'rank' => 1
]);
```

The SDK automatically:
1. Opens the file
2. Reads the contents
3. Sends as multipart/form-data
4. Closes the file

### File Resource Upload

For more control, provide a file resource:

```php
$fileHandle = fopen('./photos/product-1.jpg', 'r');

$image = ListingImage::create($shopId, $listingId, [
    'image' => [
        'contents' => $fileHandle
    ],
    'rank' => 1
]);

fclose($fileHandle);
```

### External URL Upload

Upload from external URL:

```php
$listing->uploadImage('https://example.com/image.jpg', [
    'rank' => 1
]);
```

**Note**: The SDK will attempt to open the URL and read the contents. This may not work for all URLs depending on server configuration and URL accessibility.

### Managing Image Rank

Images are displayed in order of their rank (1-10):

```php
// Upload images in specific order
$listing->uploadImage('./photos/front.jpg', ['rank' => 1]);
$listing->uploadImage('./photos/back.jpg', ['rank' => 2]);
$listing->uploadImage('./photos/detail.jpg', ['rank' => 3]);
```

**Overwrite Option**:
```php
// Replace existing image at rank 1
$listing->uploadImage('./photos/new-front.jpg', [
    'rank' => 1,
    'overwrite' => true
]);
```

### Linking Existing Images

Instead of uploading, link an existing image to a listing:

```php
// Link existing image
$listing->linkImage($existingImageId, [
    'rank' => 1
]);
```

### Getting Listing Images

```php
// Get all images
$images = $listing->images();

foreach ($images->data as $image) {
    echo "Rank {$image->rank}: {$image->url_570xN}\n";
}

// Get specific image
$image = $listing->image($imageId);

// Delete image
$listing->deleteImage($imageId);

// Or use static method
ListingImage::delete($shopId, $listingId, $imageId);
```

### Image Properties

After upload, the ListingImage resource contains:

```php
$image->listing_image_id;  // Image ID
$image->listing_id;         // Listing ID
$image->rank;               // Display order
$image->alt_text;           // Alt text
$image->is_watermarked;     // Watermarked flag
$image->url_75x75;          // Thumbnail URL
$image->url_170x135;        // Small URL
$image->url_570xN;          // Medium URL
$image->url_fullxfull;      // Full size URL
$image->full_height;        // Full image height
$image->full_width;         // Full image width
```

### Complete Example

```php
use Etsy\Resources\Listing;
use Etsy\Resources\ListingImage;

$listing = Listing::get($listingId);

// Upload multiple images
$images = [
    './photos/product-front.jpg',
    './photos/product-back.jpg',
    './photos/product-detail-1.jpg',
    './photos/product-detail-2.jpg'
];

foreach ($images as $index => $imagePath) {
    $rank = $index + 1;
    
    $image = $listing->uploadImage($imagePath, [
        'rank' => $rank,
        'alt_text' => "Product view {$rank}"
    ]);
    
    echo "Uploaded image {$rank}: {$image->url_570xN}\n";
    
    // Rate limiting
    usleep(500000);  // 0.5 second delay
}

// Get all images
$allImages = $listing->images();
echo "Total images: " . $allImages->count() . "\n";

// Update first image
$firstImage = $allImages->first();
if ($firstImage) {
    // Images don't have direct update, must delete and re-upload if needed
}
```

## Video Uploads

### Overview

- **Maximum**: 1 video per listing (Etsy limitation as of API v3)
- **Formats**: MP4, MOV
- **Maximum Duration**: 5 minutes
- **File Size**: Up to 100MB
- **Required**: Video name/filename

### Upload Methods

#### Using Listing Instance Method

```php
$listing = Listing::get($listingId);

$video = $listing->uploadVideo('./videos/product-demo.mp4', 'product-demo.mp4');
```

**Parameters**:
- **$video** (string|resource): File path or file resource
- **$name** (string): Filename (required)

#### Using ListingVideo Static Method

```php
use Etsy\Resources\ListingVideo;

$video = ListingVideo::create($shopId, $listingId, [
    'video' => './videos/product-demo.mp4',
    'name' => 'product-demo.mp4'
]);
```

### File Path Upload

```php
$listing->uploadVideo('./videos/demo.mp4', 'demo.mp4');
```

### File Resource Upload

```php
$fileHandle = fopen('./videos/demo.mp4', 'r');

$video = ListingVideo::create($shopId, $listingId, [
    'video' => [
        'contents' => $fileHandle
    ],
    'name' => 'demo.mp4'
]);

fclose($fileHandle);
```

### Linking Existing Videos

Link an already uploaded video to a listing:

```php
// Link existing video
$listing->linkVideo($existingVideoId);

// Or use static method
ListingVideo::create($shopId, $listingId, [
    'video_id' => $existingVideoId
]);
```

### Getting Listing Videos

```php
// Get all videos
$videos = $listing->videos();

foreach ($videos->data as $video) {
    echo "Video: {$video->video_id}\n";
}

// Get specific video
$video = $listing->video($videoId);

// Delete video
ListingVideo::delete($shopId, $listingId, $videoId);
```

### Video Properties

```php
$video->video_id;       // Video ID
$video->listing_id;     // Listing ID
$video->video_state;    // Processing state
$video->name;           // Video filename
```

### Complete Example

```php
use Etsy\Resources\Listing;
use Etsy\Resources\ListingVideo;

$listing = Listing::get($listingId);

// Upload video
try {
    $video = $listing->uploadVideo('./videos/product-demo.mp4', 'product-demo.mp4');
    echo "Video uploaded successfully\n";
    echo "Video ID: {$video->video_id}\n";
    echo "State: {$video->video_state}\n";  // May be 'processing'
} catch (\Exception $e) {
    echo "Video upload failed: " . $e->getMessage() . "\n";
}

// Check video status later
$videos = $listing->videos();
if ($videos->count() > 0) {
    $video = $videos->first();
    echo "Video state: {$video->video_state}\n";
}

// Delete video if needed
if (isset($video)) {
    $deleted = ListingVideo::delete($shopId, $listingId, $video->video_id);
    if ($deleted) {
        echo "Video deleted\n";
    }
}
```

## File Uploads

### Overview

For digital products and downloadable content.

- **File Types**: PDF, ZIP, and other document types
- **File Size**: Check Etsy's current limits
- **Required**: File and filename

### Upload Methods

#### Using Listing Instance Method

```php
$listing = Listing::get($listingId);

$file = $listing->uploadFile('./downloads/template.pdf', 'Template.pdf', [
    'rank' => 1
]);
```

**Parameters**:
- **$file** (string|resource): File path or file resource
- **$name** (string): Filename (required)
- **$options** (array):
  - `rank` (int): Display order

#### Using ListingFile Static Method

```php
use Etsy\Resources\ListingFile;

$file = ListingFile::create($shopId, $listingId, [
    'file' => './downloads/template.pdf',
    'name' => 'Template.pdf',
    'rank' => 1
]);
```

### File Path Upload

```php
$listing->uploadFile('./downloads/guide.pdf', 'User Guide.pdf');
```

### File Resource Upload

```php
$fileHandle = fopen('./downloads/guide.pdf', 'r');

$file = ListingFile::create($shopId, $listingId, [
    'file' => [
        'contents' => $fileHandle
    ],
    'name' => 'User Guide.pdf'
]);

fclose($fileHandle);
```

### Linking Existing Files

Link an already uploaded file:

```php
$listing->linkFile($existingFileId, $rank = 1);

// Or use static method
ListingFile::create($shopId, $listingId, [
    'listing_file_id' => $existingFileId,
    'rank' => 1
]);
```

### Getting Listing Files

```php
// Get all files
$files = $listing->files();

foreach ($files->data as $file) {
    echo "File: {$file->filename}\n";
}

// Get specific file
$file = $listing->file($fileId);

// Delete file
$listing->deleteFile($fileId);

// Or use static method
ListingFile::delete($shopId, $listingId, $fileId);
```

### File Properties

```php
$file->listing_file_id;     // File ID
$file->listing_id;          // Listing ID
$file->rank;                // Display order
$file->filename;            // Filename
$file->filesize;            // File size in bytes
$file->size_bytes;          // File size in bytes
$file->filetype;            // MIME type
$file->create_timestamp;    // Upload timestamp
```

### Complete Example

```php
use Etsy\Resources\Listing;
use Etsy\Resources\ListingFile;

$listing = Listing::get($listingId);

// Upload multiple files
$files = [
    ['path' => './downloads/guide.pdf', 'name' => 'User Guide.pdf'],
    ['path' => './downloads/template.psd', 'name' => 'Template.psd'],
    ['path' => './downloads/fonts.zip', 'name' => 'Fonts.zip']
];

foreach ($files as $index => $fileData) {
    $rank = $index + 1;
    
    $file = $listing->uploadFile($fileData['path'], $fileData['name'], [
        'rank' => $rank
    ]);
    
    $sizeKB = round($file->filesize / 1024, 2);
    echo "Uploaded {$file->filename} ({$sizeKB} KB) at rank {$rank}\n";
    
    // Rate limiting
    usleep(500000);
}

// Get all files
$allFiles = $listing->files();
echo "Total files: " . $allFiles->count() . "\n";

// Delete a file
if ($allFiles->count() > 0) {
    $firstFile = $allFiles->first();
    $deleted = $listing->deleteFile($firstFile->listing_file_id);
    if ($deleted) {
        echo "Deleted {$firstFile->filename}\n";
    }
}
```

## Upload Methods

### Method 1: File Path (Recommended)

Simplest and most common:

```php
$listing->uploadImage('./photos/product.jpg', ['rank' => 1]);
$listing->uploadVideo('./videos/demo.mp4', 'demo.mp4');
$listing->uploadFile('./downloads/guide.pdf', 'Guide.pdf');
```

**Pros**:
- Simple and clean
- SDK handles file operations
- Automatic cleanup

**Cons**:
- File must exist on server
- Less control over read process

### Method 2: File Resource

For advanced control:

```php
$handle = fopen('./photos/product.jpg', 'r');

$image = ListingImage::create($shopId, $listingId, [
    'image' => ['contents' => $handle],
    'rank' => 1
]);

fclose($handle);
```

**Pros**:
- Full control over file handling
- Can manipulate file before upload
- Can read from streams

**Cons**:
- More verbose
- Must manage file handles
- Must close resources

### Method 3: External URL

Upload from remote URLs:

```php
$listing->uploadImage('https://example.com/image.jpg', ['rank' => 1]);
```

**Pros**:
- No local file needed
- Can upload from CDN or remote server

**Cons**:
- May fail depending on URL accessibility
- Network dependent
- Less reliable than local files

### Method 4: In-Memory Content

Upload dynamically generated content:

```php
// Generate or manipulate image
$imageData = file_get_contents('./original.jpg');
// ... perform operations on $imageData ...

// Create temporary file
$tempFile = tmpfile();
fwrite($tempFile, $imageData);
rewind($tempFile);

$image = ListingImage::create($shopId, $listingId, [
    'image' => ['contents' => $tempFile],
    'rank' => 1
]);

fclose($tempFile);  // Automatically deleted
```

## Best Practices

### 1. Optimize Images Before Upload

```php
// Use image processing library (e.g., Intervention Image, GD)
function optimizeImage($sourcePath, $outputPath) {
    $image = imagecreatefromjpeg($sourcePath);
    
    // Resize if too large
    $maxWidth = 2000;
    $width = imagesx($image);
    $height = imagesy($image);
    
    if ($width > $maxWidth) {
        $newHeight = ($height / $width) * $maxWidth;
        $resized = imagescale($image, $maxWidth, $newHeight);
        imagejpeg($resized, $outputPath, 85);  // 85% quality
        imagedestroy($resized);
    } else {
        imagejpeg($image, $outputPath, 85);
    }
    
    imagedestroy($image);
}

// Use optimized image
optimizeImage('./original.jpg', './optimized.jpg');
$listing->uploadImage('./optimized.jpg', ['rank' => 1]);
unlink('./optimized.jpg');
```

### 2. Add Alt Text for Accessibility

```php
$listing->uploadImage('./photos/mug-blue.jpg', [
    'rank' => 1,
    'alt_text' => 'Handmade ceramic mug in ocean blue with gold rim'
]);
```

### 3. Implement Rate Limiting

```php
$images = glob('./photos/*.jpg');

foreach ($images as $index => $imagePath) {
    $listing->uploadImage($imagePath, ['rank' => $index + 1]);
    
    // Delay between uploads
    usleep(500000);  // 0.5 second
}
```

### 4. Handle Errors Gracefully

```php
try {
    $image = $listing->uploadImage('./photos/product.jpg', [
        'rank' => 1
    ]);
    echo "Image uploaded: {$image->url_570xN}\n";
} catch (\Etsy\Exception\RequestException $e) {
    error_log("Image upload failed: " . $e->getMessage());
    // Fallback or retry logic
}
```

### 5. Verify File Exists

```php
$imagePath = './photos/product.jpg';

if (!file_exists($imagePath)) {
    throw new Exception("Image file not found: {$imagePath}");
}

if (!is_readable($imagePath)) {
    throw new Exception("Image file not readable: {$imagePath}");
}

$listing->uploadImage($imagePath, ['rank' => 1]);
```

### 6. Check File Size

```php
$maxSize = 10 * 1024 * 1024;  // 10MB

$fileSize = filesize('./photos/product.jpg');

if ($fileSize > $maxSize) {
    throw new Exception("Image too large: " . round($fileSize / 1024 / 1024, 2) . " MB");
}

$listing->uploadImage('./photos/product.jpg', ['rank' => 1]);
```

### 7. Organize Upload Order

```php
$imagePriority = [
    'front view' => './photos/front.jpg',
    'back view' => './photos/back.jpg',
    'detail 1' => './photos/detail1.jpg',
    'detail 2' => './photos/detail2.jpg',
    'lifestyle' => './photos/lifestyle.jpg'
];

$rank = 1;
foreach ($imagePriority as $description => $path) {
    if (file_exists($path)) {
        $listing->uploadImage($path, [
            'rank' => $rank++,
            'alt_text' => $description
        ]);
    }
}
```

### 8. Backup Before Replacing

```php
// Get existing images
$existingImages = $listing->images();

// Backup URLs
$backup = [];
foreach ($existingImages->data as $image) {
    $backup[] = [
        'rank' => $image->rank,
        'url' => $image->url_fullxfull
    ];
}

// Upload new images
foreach ($newImages as $index => $imagePath) {
    $listing->uploadImage($imagePath, [
        'rank' => $index + 1,
        'overwrite' => true
    ]);
}

// Store backup in case rollback is needed
file_put_contents('image_backup.json', json_encode($backup));
```

### 9. Progress Tracking for Bulk Uploads

```php
function bulkUploadImages($listing, $imagePaths) {
    $total = count($imagePaths);
    $uploaded = 0;
    $failed = 0;
    
    echo "Uploading {$total} images...\n";
    
    foreach ($imagePaths as $index => $path) {
        try {
            $listing->uploadImage($path, ['rank' => $index + 1]);
            $uploaded++;
            
            $percent = round(($uploaded / $total) * 100);
            echo "Progress: {$percent}% ({$uploaded}/{$total})\n";
            
            usleep(500000);
        } catch (\Exception $e) {
            $failed++;
            error_log("Failed to upload {$path}: " . $e->getMessage());
        }
    }
    
    echo "Complete: {$uploaded} uploaded, {$failed} failed\n";
    return ['uploaded' => $uploaded, 'failed' => $failed];
}
```

## Troubleshooting

### Issue: "File not found" Error

**Cause**: File path is incorrect or file doesn't exist.

**Solution**:
```php
$path = './photos/product.jpg';

if (!file_exists($path)) {
    echo "File not found: {$path}\n";
    echo "Current directory: " . getcwd() . "\n";
    echo "Absolute path: " . realpath($path) . "\n";
}

// Use absolute paths
$absolutePath = __DIR__ . '/photos/product.jpg';
$listing->uploadImage($absolutePath, ['rank' => 1]);
```

### Issue: "Permission denied" Error

**Cause**: PHP doesn't have read permission on the file.

**Solution**:
```bash
# Check permissions
ls -la photos/product.jpg

# Fix permissions
chmod 644 photos/product.jpg
```

```php
if (!is_readable($path)) {
    throw new Exception("Cannot read file: {$path}");
}
```

### Issue: Upload Takes Too Long / Times Out

**Cause**: File is too large or connection is slow.

**Solution**:
1. Optimize/resize images before upload
2. Increase PHP timeouts:

```php
set_time_limit(300);  // 5 minutes
ini_set('max_execution_time', 300);
```

3. Check file size:

```php
$maxSize = 10 * 1024 * 1024;  // 10MB
$fileSize = filesize($path);

if ($fileSize > $maxSize) {
    // Optimize or reject
}
```

### Issue: "Invalid file type" Error

**Cause**: File format not supported by Etsy.

**Solution**:
```php
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $path);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    throw new Exception("Unsupported file type: {$mimeType}");
}
```

### Issue: Images Upload But Don't Display

**Cause**: Image processing delay on Etsy's side.

**Solution**:
Wait a few seconds after upload before checking:

```php
$image = $listing->uploadImage($path, ['rank' => 1]);

// Wait for processing
sleep(2);

// Verify
$images = $listing->images();
```

### Issue: "Maximum images exceeded" Error

**Cause**: Trying to upload more than 10 images.

**Solution**:
```php
$existingImages = $listing->images();

if ($existingImages->count() >= 10) {
    echo "Maximum images (10) already uploaded\n";
    
    // Delete old images first
    foreach ($existingImages->data as $image) {
        if ($image->rank > 5) {
            ListingImage::delete($shopId, $listingId, $image->listing_image_id);
        }
    }
}
```

### Issue: Memory Exhaustion on Large Files

**Cause**: PHP memory limit too low.

**Solution**:
```php
// Increase memory limit
ini_set('memory_limit', '256M');

// Or use streaming/chunked uploads (advanced)
```

### Issue: URL Upload Fails

**Cause**: URL not accessible or SSL issues.

**Solution**:
```php
// Download to temp file first
$imageData = file_get_contents('https://example.com/image.jpg');

if ($imageData === false) {
    throw new Exception("Failed to download image from URL");
}

$tempFile = tmpfile();
fwrite($tempFile, $imageData);
rewind($tempFile);

$image = ListingImage::create($shopId, $listingId, [
    'image' => ['contents' => $tempFile],
    'rank' => 1
]);

fclose($tempFile);
```

## Summary

Key points for file uploads:

1. **Images**: Maximum 10, at least 1 required, rank 1-10
2. **Videos**: Maximum 1, up to 5 minutes, MP4/MOV format
3. **Files**: For digital products, various formats supported
4. **Methods**: File path (recommended), file resource, external URL
5. **Optimization**: Resize and compress before upload
6. **Alt Text**: Always provide for accessibility
7. **Rate Limiting**: Add delays between uploads
8. **Error Handling**: Always wrap in try-catch
9. **Verification**: Check file exists and is readable
10. **Cleanup**: Close file handles and delete temp files

For more information, see:
- [API Reference - ListingImage](API_REFERENCE.md#listingimage)
- [API Reference - ListingVideo](API_REFERENCE.md#listingvideo)
- [API Reference - ListingFile](API_REFERENCE.md#listingfile)
- [Examples](EXAMPLES.md) - Real-world upload scenarios
