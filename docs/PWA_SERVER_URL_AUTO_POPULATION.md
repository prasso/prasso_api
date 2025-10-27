# PWA Server URL Auto-Population

## Overview

When creating a new app, the PWA Server URL field is automatically populated with the next available port number. This eliminates the need for users to manually choose ports and prevents port conflicts.

## How It Works

### Port Detection Algorithm

1. **Scan Database**: Query all apps with `pwa_server_url` already set
2. **Extract Ports**: Parse each URL to extract the port number
3. **Find Next Available**: Start from port 3001 and find the first unused port
4. **Return URL**: Return the full server URL with the next available port

### Port Parsing

The algorithm handles multiple URL formats:

**Standard Format:**
```
http://localhost:3001
```
Extracts port: `3001`

**Alternative Format:**
```
http://localhost:3001/path
```
Extracts port: `3001`

**Host:Port Format:**
```
localhost:3001
```
Extracts port: `3001`

### Default Starting Port

- **Base URL**: `http://localhost:`
- **Starting Port**: `3001`
- **Increment**: `+1` for each used port

### Port Sequence

```
App 1: http://localhost:3001
App 2: http://localhost:3002
App 3: http://localhost:3003
App 4: http://localhost:3004
...
```

## Implementation

### Livewire Component (`AppInfoForm.php`)

#### `mount()` Method
- Initializes site_id from existing app
- Stores previous PWA server URL for change detection
- **Auto-populates PWA Server URL** if not already set

```php
if (!$this->teamapp->pwa_server_url) {
    $this->teamapp->pwa_server_url = $this->getNextAvailableServerUrl();
}
```

#### `getNextAvailableServerUrl()` Method
- Queries all apps with `pwa_server_url` set
- Parses each URL to extract port numbers
- Finds the next available port starting from 3001
- Returns the full server URL

**Algorithm:**
```php
1. Get all pwa_server_url values from database
2. For each URL:
   - Parse URL using parse_url()
   - Extract port from 'port' key or 'host' key
   - Add to usedPorts array
3. Start with port 3001
4. While port is in usedPorts:
   - Increment port
5. Return http://localhost:{nextPort}
```

### Blade View (`app-info-form.blade.php`)

**Changed from:**
```blade
wire:model.defer="teamapp.pwa_server_url"
```

**Changed to:**
```blade
wire:model="teamapp.pwa_server_url"
```

**Reason**: `wire:model` (without `.defer`) displays the prefilled value immediately, while `.defer` only saves on form submission.

**Updated Helper Text:**
```
Internal URL where the Node.js server runs. Auto-populated with next available port. Example: http://localhost:3001
```

## User Experience

### Creating a New App

1. **User opens App Editor** → Form loads
2. **PWA Server URL is auto-populated** → Shows next available port (e.g., `http://localhost:3001`)
3. **User can modify if needed** → Can change port manually
4. **User enters other fields** → App Name, Page Title, etc.
5. **User saves** → App is created with the configured port

### Editing an Existing App

1. **User opens App Editor** → Form loads with existing data
2. **PWA Server URL displays existing value** → No auto-population (already set)
3. **User can modify if needed** → Can change to different port
4. **User saves** → App is updated with new port

### Multiple Apps

**Scenario: Creating 3 apps in sequence**

```
App 1 created:
  - Auto-populated: http://localhost:3001
  - User saves

App 2 created:
  - Auto-populated: http://localhost:3002 (3001 is now used)
  - User saves

App 3 created:
  - Auto-populated: http://localhost:3003 (3001 and 3002 are used)
  - User saves
```

## Database Queries

### Query Used

```php
Apps::whereNotNull('pwa_server_url')
    ->pluck('pwa_server_url')
    ->toArray()
```

**What it does:**
- Selects all apps where `pwa_server_url` is not null
- Returns only the `pwa_server_url` values
- Converts to array for processing

**Performance:**
- Efficient query (only fetches needed column)
- Runs only on form mount (not on every change)
- Minimal database impact

## Edge Cases Handled

### No Existing Apps
```
Result: http://localhost:3001
```

### All Ports Up to 3010 Used
```
Used: 3001, 3002, 3003, ..., 3010
Result: http://localhost:3011
```

### Non-Sequential Ports
```
Used: 3001, 3003, 3005
Result: http://localhost:3002 (next available)
```

### Mixed URL Formats
```
Used: 
  - http://localhost:3001
  - localhost:3002
  - http://localhost:3003/app
Result: http://localhost:3004
```

## Manual Override

Users can manually change the auto-populated port:

1. **Auto-populated value**: `http://localhost:3001`
2. **User changes to**: `http://localhost:5000`
3. **Form saves with**: `http://localhost:5000`

The auto-population only happens on initial form load for new apps.

## Validation

The PWA Server URL is validated:

```php
'teamapp.pwa_server_url' => 'nullable|url|max:2048'
```

- Must be a valid URL format
- Maximum length: 2048 characters
- Optional field (nullable)

## Benefits

✅ **No Port Conflicts** - Automatic detection prevents port collisions  
✅ **User-Friendly** - Users don't need to manually choose ports  
✅ **Predictable** - Ports are assigned sequentially starting from 3001  
✅ **Flexible** - Users can override if needed  
✅ **Efficient** - Single database query on form load  
✅ **Scalable** - Works with any number of apps  

## Troubleshooting

### Port Already in Use

**Problem**: Auto-populated port is already running on the system

**Solution**: 
1. User can manually change the port in the form
2. Or stop the process using that port
3. Or use a different port range

### Auto-Population Not Working

**Problem**: PWA Server URL not prefilled

**Possible Causes**:
- App already has `pwa_server_url` set (won't override)
- Database query failed
- Livewire component not mounted properly

**Solution**:
- Check if app already has a value
- Verify database connection
- Clear browser cache and reload

## Future Enhancements

- Allow configuration of starting port (currently hardcoded to 3001)
- Add option to use different hosts (not just localhost)
- Add port availability check (verify port is actually free)
- Add UI indicator showing which ports are in use
- Add bulk port assignment for multiple apps
- Add port conflict warning if port is already running
