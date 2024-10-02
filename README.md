The PoC manages Tab IDs across multiple open tabs in a browser.

Each tab generates or retrieves a Tab ID and
interacts with the backend to confirm or receive an authentic Tab ID.

The session on the backend tracks data about each tab, and
handles limits such as reload rates and the tab's lifespan.

## Directory Structure

-   `index.html`: The main HTML page and entry point for the app.

-   `backend/`: Contains the backend logic in PHP for handling tab IDs.

-   `resources/`: Contains CSS for styling, JavaScript for frontend logic, and external libraries like jQuery.

```
TAB-IDS
│   index.html (Frontend HTML page)
│
├───backend
│       guard.php (Backend (request) validation)
│       tabid.php (Backend Tab ID logic)
│
└───resources
    ├───css
    │       style.css (Frontend CSS)
    ├───js
    │       script.js (Frontend JS for tab management)
    └───lib
            jquery-3.7.1.js (jQuery library)

```

## Frontend Details

### `index.html`

It uses jQuery to manage the logic of assigning Tab IDs to each browser tab. Here’s a breakdown of its functionality:

-   **Hidden Input Field**:

    The tab_id input field stores the tab's current Tab ID.

-   **jQuery**:

    It manages the interaction between the frontend and backend, especially with AJAX requests.

-   **New Tab Button**:

    Clicking this button opens a new browser tab, which will also request a new Tab ID from the backend.

```html
<input type="hidden" id="tab_id" name="tab_id" />

<h1>[🧪Tab]</h1>

<p>Tab-ID: <span id="tab_id_demo"></span></p>

<button id="new_tab_button" type="button">✨New Tab</button>
```

### `script.js`

The frontend JavaScript initializes the **Tab ID** for the current tab.
It retrieves an existing tab ID from the `sessionStorage`, or,
if the Tab ID is absent or invalid, it requests one from the backend
(`backend/tabid.php`).

1.  **Session Storage Management**:

    -   When a tab opens, it checks for an existing Tab ID in `sessionStorage`.

    -   If no valid Tab ID exists, it assigns one from the server using AJAX.

    -   The Tab ID is displayed in the page using the `#tab_id_demo` span.

2.  **AJAX Request**:

    -   Sends the Tab ID to the backend
        to either authenticate or retrieve a new one.

    -   The server responds with an authTabID,
        which is then stored in both the hidden input and sessionStorage.

3.  **New Tab Handling**:

    -   The New Tab button opens a new tab,
        which follows the same process to retrieve a unique Tab ID.

### Key Points:

-   The front-end manages Tab IDs through sessionStorage.

-   It makes AJAX requests to ensure that
    each tab has a unique ID, provided by the backend.

## Backend Details

The backend is responsible for validating, issuing, and managing Tab IDs. PHP scripts handle the requests sent by the frontend.

### `guard.php`

This file acts as a safeguard for ensuring
that incoming requests meet basic security requirements:

1. It checks that requests are made via **POST**.

2. Validates that the request data contains a `tab_id`
   in the expected format (string).

If these conditions aren’t met,
it returns appropriate HTTP error codes (405, 400, or 422).

```php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit;
}
if (!array_key_exists('tab_id', $_POST)) {
    http_response_code(400);
    exit;
}
if (!is_string($_POST['tab_id'])) {
    http_response_code(422);
    exit;
}
```

### `tabid.php`

This is the main logic for handling Tab IDs.
Its key responsibilities:

1.  **Session Management**:

    -   Starts a PHP session and initializes a `tabs` array
        in `$_SESSION` if it doesn't exist

    -   The array stores data for each tab, indexed by the Tab ID.

2.  **Tab ID Validation**:

    -   If the tab already exists in the session,
        it validates the Tab ID's data
        (IP address, reload rate, etc.)
        using the validateTabData() function.

    -   If the Tab ID is valid, it returns the Tab ID in the response.

3.  **New Tab Creation**:

    -   If the Tab ID is not found in the session,
        a new Tab ID is generated using getNewTab().

    -   The new Tab ID is returned to the client and stored in the session.

4.  **Session-based Limits**:

    -   It enforces limits such as the maximum tab duration (e.g., 1 hour)
        and reload rates (e.g., no more than 3 reloads per second).

    -   If any limit is exceeded,
        the tab's data is invalidated and not returned.

### Key Points:

The backend verifies existing Tab IDs and
generates new ones for new tabs.
Session data tracks each tab, including the user's IP,
reload counts, and timestamps.

### Functions Breakdown:

-   `validateTabData()`:

    Ensures that the tab is valid (e.g., the tab IP hasn’t changed, and the tab hasn't exceeded the maximum allowed duration or reload rate).

-   `updateTabData()`:

    Updates the session data for the tab, including incrementing the reload count and updating timestamps.

-   `getNewTab()`:

    Generates a new Tab ID and initializes default session values
    (IP, time, etc.).

## Interaction Flow (Frontend & Backend Communication)

1.  **Frontend**:

    When a user opens a tab,
    the JavaScript checks if a Tab ID is stored in sessionStorage.

2.  **AJAX Request**:

    The frontend sends the Tab ID to the backend.

3.  **Backend**:

    If the Tab ID exists and is valid, the backend returns it.
    If the Tab ID is new, the backend generates a new one and sends it back.

4.  **Frontend**:

    The Tab ID is updated in both the hidden input and `sessionStorage`.
