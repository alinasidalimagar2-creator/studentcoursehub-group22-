# Frontpage - Public Pages

This folder has all the pages that visitors see before they log in.

## Files

- **home.php** - Homepage with welcome message and navigation
- **index.php** - Shows all programmes with filters
- **view_details.php** - Shows full details of a single programme
- **header.php** - Navigation menu (included on all pages)
- **fetch_programmes.php** - Gets programmes from database (for AJAX)
- **register_interest.php** - Lets students register interest in a programme
- **style.css** - All the styling

## What Each Page Does

### home.php
This is the main page users see. It has:
- Welcome banner
- Links to login pages
- Button to browse programmes

### index.php
Shows all available programmes. Users can:
- Filter by level
- Search by keyword
- View details of each programme
- Register interest (if logged in)

### view_details.php
Shows full information about one programme including:
- Programme description
- List of modules
- Entry requirements
- Career options

### header.php
The navigation menu that appears on every page. Has links to:
- Home
- Programmes
- Student/User Login
- Staff Login
- Admin Login
- Sign Up

### fetch_programmes.php
Returns programme data as JSON. Used for dynamic loading with AJAX.

### register_interest.php
Handles the form when a student wants to register interest in a programme. Checks if they're logged in first.

## Database Tables Used

- programmes - All programme info
- levels - Programme levels (4, 5, 6, etc.)
- modules - Modules for each programme
- users - Student accounts
- interestedstudents - Tracks which students registered interest

## How to Test

1. Go to home.php - should show the homepage
2. Click "Browse Programmes" - goes to index.php
3. Try the filters - should work
4. Click "View Details" on any programme
5. If logged in, click "Register Interest"

## Notes

- All pages need config.php for the database
- header.php is included in every page
- Students must be logged in to register interest
- Everything uses prepared statements to prevent SQL injection

## Screenshots

Check the /Screenshots folder for images of the pages.
