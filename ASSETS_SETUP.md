# CnxEvents Module - Asset Setup

## FullCalendar Library Setup

The calendar view requires FullCalendar v5 library. Since FreeScout doesn't allow external CDN links, you need to download and install it locally.

### Steps to Install FullCalendar:

1. Download FullCalendar v5.11.0 (or later v5.x):
   - Go to: https://github.com/fullcalendar/fullcalendar/releases
   - Download the release archive

2. Extract the following files from the download:
   - `packages/core/main.min.js` → Copy to `public/modules/cnxevents/js/fullcalendar.min.js`
   - `packages/core/main.min.css` → Copy to `public/modules/cnxevents/css/fullcalendar.min.css`
   
   OR use the bundled version:
   - `dist/index.global.min.js` → Copy to `public/modules/cnxevents/js/fullcalendar.min.js`
   - `dist/index.global.min.css` → Copy to `public/modules/cnxevents/css/fullcalendar.min.css`

3. Alternatively, use NPM to install (if you have Node.js):
   ```bash
   cd Modules/CnxEvents/Resources/assets
   npm install @fullcalendar/core @fullcalendar/daygrid @fullcalendar/timegrid @fullcalendar/interaction
   # Then copy the built files to public/modules/cnxevents/
   ```

4. Or use the CDN-downloaded files (quick method):
   ```bash
   # Download using PowerShell
   cd public/modules/cnxevents
   
   # Create directories
   mkdir -p js css
   
   # Download JS
   Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js" -OutFile "js/fullcalendar.min.js"
   
   # Download CSS
   Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" -OutFile "css/fullcalendar.min.css"
   ```

### Verify Installation:

After copying the files, verify they exist at:
- `public/modules/cnxevents/js/fullcalendar.min.js`
- `public/modules/cnxevents/css/fullcalendar.min.css`

The calendar should now work without any external dependencies.

## Publishing Module Assets

After making changes to files in `Resources/assets/`, run:

```bash
php artisan module:publish CnxEvents
```

Or manually copy files from `Resources/assets/` to `public/modules/cnxevents/`.
