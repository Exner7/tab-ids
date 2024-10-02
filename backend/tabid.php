<?php

require_once('./guard.php');

const MAX_RELOAD_RATE = 3; # don't allow more than 3 reloads per 1 second.
const MAX_NUMBER_OF_TABS = 9; # maximum number of open tabs - not used yet.
const MAX_TAB_DURATION_SECONDS = 60 * 60 * 1; # maximum number of seconds that a tab can be stored in session.

session_start();

// Initialize the session tabs if not set.
if (!array_key_exists('tabs', $_SESSION)) {
    $_SESSION['tabs'] = [];
}

$tabID = $_POST['tab_id']; # extract the tab ID from the request data
$tabs = $_SESSION['tabs']; # extract the tabs from the session

if (array_key_exists($tabID, $tabs)) { # known tab 📘
    $tabData = validateTabData($tabs[$tabID]);
    $tabData = updateTabData($tabData);

    $_SESSION['tabs'][$tabID] = $tabData;

    http_response_code(200);
    echo json_encode(['tab_id' => $tabID]);
} else { # new tab ✨
    [$authTabID, $tabData] = getNewTab();

    $tabs[$authTabID] = $tabData;
    $_SESSION['tabs'] = $tabs;

    http_response_code(200);
    echo json_encode(['tab_id' => $authTabID]);
}

// Helper functions... 👇

function getNewTab(): array
{
    $tabData = [];

    $tabData['user_id']               = $_SESSION['user_id'] ?? null;
    $tabData['tab_ip']                = $_SERVER['REMOTE_ADDR'];
    $tabData['number_of_reloads']     = 0;
    $tabData['init_interaction_time'] = time();
    $tabData['prev_interaction_time'] = time();
    $tabData['last_interaction_time'] = time();

    // Other useful tab parameters...

    $authTabID = null;

    // Generate a new unique tab ID.
    do {
        $authTabID = base64_encode(random_bytes(6));
    } while (array_key_exists($authTabID, $_SESSION['tabs']));

    return [$authTabID, $tabData];
}

function validateTabData($tabData): array
{
    // The request IP should match the tab IP stored in the session.
    if ($tabData['tab_ip'] !== $_SERVER['REMOTE_ADDR']) {
        throw new \Exception('tampered tab IP address.');
    }

    $duration = $tabData['last_interaction_time'] - $tabData['init_interaction_time'];

    // Prune the tab after a maximum allowed duration of time.
    // For example, if a tab is open for more than 1 hour.
    if (MAX_TAB_DURATION_SECONDS < $duration) {
        unset($tabData);
        throw new \Exception('Allowed duration exceeded.');
    }

    $reloadRate = $tabData['number_of_reloads'] / $duration;

    if (0 < $tabData['number_of_reloads']) {
        if (MAX_RELOAD_RATE < $reloadRate) {
            throw new \Exception('Allowed throttle exceeded.');
        }
    }

    // Other validity checks...

    return $tabData;
}

function updateTabData($tabData): array
{
    $tabData['user_id']               = $_SESSION['user_id'] ?? null;
    $tabData['tab_ip']                = $_SESSION['tab_ip'] = $_SERVER['REMOTE_ADDR'];
    $tabData['number_of_reloads']    += 1;
    $tabData['prev_interaction_time'] = $tabData['last_interaction_time']; 
    $tabData['last_interaction_time'] = time();

    // Other updates...

    return $tabData;
}

/**
 * In order to delete a tab from the session,
 * we need to be able to differentiate between when the tab is closed or reloaded.
 * I haven't found a way to do this directly, but there are some protocol-ideas for doing this.
 * For example here's a stackoverflow link for this issue:
 * 
 * https://stackoverflow.com/questions/568977/identifying-between-refresh-and-close-browser-actions
 */