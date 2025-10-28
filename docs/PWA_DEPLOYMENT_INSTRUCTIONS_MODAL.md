# PWA Deployment Instructions Modal

## Overview

When a user enters or updates the PWA Server URL in the App Editor form, a modal automatically displays with step-by-step deployment instructions. This helps users understand how to set up and deploy their Node.js application.

## Implementation

### 1. Livewire Component (`AppInfoForm.php`)

**New Properties:**
- `show_deployment_instructions`: Boolean to control modal visibility
- `previous_pwa_server_url`: Tracks the previous value to detect changes

**New Methods:**

#### `updated($property, $value)`
- Watches for changes to `teamapp.pwa_server_url`
- Shows modal when PWA Server URL is set (not empty and different from previous)
- Automatically triggered by Livewire when the field changes

#### `closeDeploymentInstructions()`
- Closes the deployment instructions modal
- Called when user clicks "Got it!" or the X button

### 2. Blade View (`app-info-form.blade.php`)

**Modal Features:**
- Fixed overlay with semi-transparent background
- Centered, scrollable modal window
- Blue header with title and close button
- Three-step deployment guide
- Important notes section
- Footer with "Got it!" button

**Modal Content:**

**Step 1: Configure Your App to Use the Server URL**
- Explains that app must listen on the configured server URL
- Shows how to set PORT environment variable for:
  - **React (Create React App)**: `PORT={port} npm start`
  - **Next.js**: Build then `PORT={port} npm start`
  - **.env.local**: Set `PORT` and `HOST` variables
- Dynamically extracts port from configured `pwa_server_url`
- Shows exact commands with the configured port number

**Step 2: Start Your Node.js Server**
- Instructions to navigate to app directory
- Command to start the server
- Shows the configured server URL

**Step 3: Verify Server is Running**
- Instructions to test server connectivity
- curl command with the configured server URL
- Helps verify the server is accessible

**Step 4: Access Your App**
- Shows the public-facing PWA App URL
- Explains that Prasso proxies requests
- Shows which port the server is running on

**Important Notes:**
- Keep Node.js server running
- Use PM2 or systemd in production
- Server must handle all HTTP methods
- DNS setup is automatic for faxt.com domains

## User Flow

1. **User opens App Editor**
   - Form loads with current app data
   - `previous_pwa_server_url` is set to current value

2. **User enters PWA Server URL**
   - Example: `http://localhost:3001`
   - Livewire detects change via `updated()` method

3. **Modal Automatically Appears**
   - Shows deployment instructions
   - Displays the configured URLs
   - Provides step-by-step guidance

4. **User Reviews Instructions**
   - Reads the three deployment steps
   - Reviews important notes
   - Understands what to do next

5. **User Closes Modal**
   - Clicks "Got it!" or X button
   - Modal closes
   - User can continue editing or save form

## Styling

**Modal Design:**
- Modern, clean interface
- Blue accent color for headers
- Yellow warning box for important notes
- Monospace font for code examples
- Responsive design (works on mobile)

**Color Scheme:**
- Header: Blue (#2563EB)
- Text: Gray (#374151)
- Borders: Blue (#3B82F6)
- Warning: Yellow (#FBBF24)
- Buttons: Blue with hover effect

## Responsive Behavior

- Modal is centered on screen
- Max width: 42rem (2xl)
- Scrollable if content exceeds viewport height
- Margin on mobile (mx-4)
- Works on all screen sizes

## Accessibility

- Close button with SVG icon
- Semantic HTML structure
- Clear, readable text
- High contrast colors
- Keyboard accessible (can close with button)

## Code Examples in Modal

**Step 1 Example:**
```bash
cd /path/to/your/app
npm start
```

**Step 2 Example:**
```bash
curl http://localhost:3001/
```

**Step 3 Display:**
Shows the actual configured URLs from the form

## Important Notes Displayed

1. Keep your Node.js server running at all times
2. Use PM2 or systemd to manage the process in production
3. Your server must handle all HTTP methods (GET, POST, PUT, DELETE)
4. DNS setup is automatic for faxt.com domains

## Future Enhancements

- Add copy-to-clipboard buttons for commands
- Add link to full deployment documentation
- Add option to skip modal for future entries
- Add video tutorial link
- Add troubleshooting section
- Add PM2 setup instructions
- Add systemd service file template

## Testing

To test the modal:

1. Open App Editor
2. Create a new app or edit existing one
3. Enter a PWA Server URL (e.g., `http://localhost:3001`)
4. Modal should automatically appear
5. Verify all content displays correctly
6. Test close button (X and "Got it!")
7. Verify modal doesn't appear on subsequent saves (only on new entry)

## Notes

- Modal only shows when PWA Server URL is newly set
- Modal doesn't show on form load (only on user input)
- Modal doesn't show if URL is cleared
- Modal doesn't show if URL is unchanged
- User can still edit form while modal is open (modal is overlay)
- Modal closes without saving form (user must click Save button)
