<?php
/**
 * Plugin Name: Country-Based Pop-Up
 * Description: A plugin that creates a full-screen pop-up that displays different content based on the user's country
 * Version: 1.0
 * Author: OpenAI
 */

// Start the session when the `wp_loaded` action is fired
function start_session_on_wp_loaded() {
    if (!session_id()) {
        session_start();
    }
}

// Start the session when the wp_loaded action is fired
add_action('wp_loaded', 'start_session_on_wp_loaded');

// Array of country codes to show the full-screen popup for
$selected_countries = array("US", "CA", "GB");

// Function to get the user's country
function get_user_country() {
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Use the IP-API to get the user's country code
    $api_url = "http://ip-api.com/json/" . $ip_address;
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        // Handle the error
        return "";
    } else {
        $data = json_decode($response['body'], true);
        return $data['countryCode'];
    }
}

// Function to get the content for the user's country
function get_popup_content($country) {
    switch ($country) {
        case "CA":
            return "Hello from Canada!";
        case "UK":
            return "Hello from the United Kingdom!";
        default:
            return "Hello from somewhere else!";
    }
}

function country_based_popup() {
    // Get the user's country
    $user_country = get_user_country();
	global $selected_countries;

    // Get the content for the user's country
    $popup_content = get_popup_content($user_country);
	
	// Check if the user's country code is in the list of selected countries
	if (in_array($user_country, $selected_countries)) {
		
			// Check if the popup has already been shown in this session
			if (!isset($_SESSION['popup_shown']) || $_SESSION['popup_shown'] != true) {
				// Output the pop-up HTML
				echo '<div id="country-based-popup" style="position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.8);z-index:9999;">';
				echo '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background-color:#fff;padding:20px;">';
				echo $popup_content;
				echo '<br><br><button id="close-popup-button" style="background-color:#333;color:#fff;padding:10px 20px;">Close</button>';
				echo '</div>';
				echo '</div>';

				// Output the JavaScript to close the pop-up when the close button is clicked
				echo '<script>';
				echo 'document.getElementById("close-popup-button").addEventListener("click", function() {';
				echo 'var xhr = new XMLHttpRequest();';
				echo 'xhr.open("POST", "' . admin_url('admin-ajax.php') . '", true);';
				echo 'xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");';
				echo 'xhr.send("action=\'unset_popup_shown_session_variable\'");';
				echo 'document.getElementById("country-based-popup").style.display = "none";';
				echo '});';
				echo '</script>';

				// Set the session variable to indicate that the popup has been shown
				$_SESSION['popup_shown'] = true;
		}
	}
}

// Function to handle the Ajax request and unset the session variable
function unset_popup_shown_session_variable() {
    unset($_SESSION['popup_shown']);
    wp_die();
}

// Hook the popup function to the "wp_footer" action
add_action( 'wp_footer', 'country_based_popup' );

// Add the unset_popup_shown_session_variable function to the appropriate Ajax action hooks for both logged-in users and non-logged-in users
add_action('wp_ajax_unset_popup_shown_session_variable', 'unset_popup_shown_session_variable');
add_action('wp_ajax_nopriv_unset_popup_shown_session_variable', 'unset_popup_shown_session_variable');
