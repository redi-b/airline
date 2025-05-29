<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function set_success($message)
{
    $_SESSION['success_messages'][] = $message;
}

function set_error($message)
{
    $_SESSION['error_messages'][] = $message;
}

function set_info($message)
{
    $_SESSION['info_messages'][] = $message;
}

function set_warning($message)
{
    $_SESSION['warning_messages'][] = $message;
}

function show_messages()
{
    $message_types = [
        'success' => 'success_messages',
        'error' => 'error_messages',
        'info' => 'info_messages',
        'warning' => 'warning_messages'
    ];
    $hasMessages = false;

    foreach ($message_types as $type => $session_key) {
        if (!empty($_SESSION[$session_key])) {
            $hasMessages = true;
            break;
        }
    }

    if ($hasMessages) {
        echo '<div id="toast-container">';

        foreach ($message_types as $type => $session_key) {
            if (!empty($_SESSION[$session_key])) {
                foreach ($_SESSION[$session_key] as $index => $msg) {
                    echo '<div id="toast-' . $type . '-' . $index . '" class="toast ' . $type . '" role="alert">
                            ' . htmlspecialchars($msg) . '
                            <button class="close-btn">
                                <img src="' . BASE_URL . 'assets/icons/close.svg" alt="Close" class="icon-close">
                            </button>
                          </div>';
                }
                unset($_SESSION[$session_key]);
            }
        }

        echo '</div>';
    }
}

function save_session_messages()
{
    $messageTypes = [
        'success_messages',
        'error_messages',
        'info_messages',
        'warning_messages'
    ];

    $savedMessages = [];

    foreach ($messageTypes as $type) {
        if (!empty($_SESSION[$type])) {
            $savedMessages[$type] = $_SESSION[$type];
        }
    }

    return $savedMessages;
}

function restore_session_messages($savedMessages)
{
    if (!is_array($savedMessages)) {
        return;
    }

    foreach ($savedMessages as $type => $messages) {
        // Merge with existing messages if needed
        if (!isset($_SESSION[$type])) {
            $_SESSION[$type] = [];
        }

        $_SESSION[$type] = array_merge($_SESSION[$type], $messages);
    }
}

?>