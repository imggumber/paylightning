<?php
/**** 
 * 
 * Paylightning transaction records
 * Note: Do not modify it without prior experience or without the supervision of any developer.
 * 
**/
function pay_light_transaction_submenu() {
    add_menu_page(
        'PayLightning Transactions',   
        'PayLightning Transactions',
		'read',
        'paylightning-transactions',   
        'display_speed_transactions', // Callback function
        'dashicons-chart-line',  // Icon
        25                      
    );
}
add_action('admin_menu', 'pay_light_transaction_submenu');

function display_speed_transactions() {
    // Handle form submission and storing the API key into the database
    if (isset($_POST['save_speed_api_key'])) {
        update_option('speed_api_key', sanitize_text_field($_POST['speed_api_key']));
        echo "<div class='updated'><p>API Key Updated Successfully!</p></div>";
    }

    // Get the stored API Key
    $api_key = get_option('speed_api_key', '');

    ?>
    <div class="wrap">
        <h1>Paylightning Transactions Records</h1>

        <!-- API Key Form -->
		<?php
			if (current_user_can('manage_options')) { ?>
				<form method="post">
					<table class="form-table">
						<tr>
							<th><label for="speed_api_key">API Key</label></th>
							<td>
								<input type="text" name="speed_api_key" id="speed_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
							</td>
						</tr>
					</table>
					<p><input type="submit" name="save_speed_api_key" class="button-primary" value="Save API Key"></p>
				</form> <?php
			}
		?>
        
        <hr>

        <?php display_speed_transactions_table($api_key); ?>
    </div>
    <?php
}

function display_speed_transactions_table($api_key) {
    if (empty($api_key)) {
        echo "<p style='color:red;'>Please enter an API key above.</p>";
        return;
    }

    // Enqueue DataTables CSS & JS
    wp_enqueue_style('datatables-css', get_stylesheet_directory_uri() . '/paylightning/assets/css/datatable.min.css');
    wp_enqueue_style('custom-datatables-css', get_stylesheet_directory_uri() . '/paylightning/assets/css/custom.css');
    wp_enqueue_script('datatables-js', get_stylesheet_directory_uri() . '/paylightning/assets/js/datatable.min.js', array('jquery'), null, true);
    wp_enqueue_script('datatables-init', get_stylesheet_directory_uri() . '/paylightning/assets/js/custom.js' , null, true);

    // Speed API Endpoint
    $url = "https://paylightning.io/transactions";

    // Setup cURL request
    $args = array(
        'method'    => 'GET',
        'headers'   => array(
            'Authorization' => 'Basic ' . base64_encode("$api_key:"),
            'Accept'        => 'application/json',
        ),
    );

    // Fetch API response
    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        echo "<div class='error'>Error fetching data: " . $response->get_error_message() . "</div>";
        return;
    }

    

    // Decode JSON response
    if (isset($response['body'])) {
        $data = json_decode(($response['body']), true);
    } else {
        echo "<p>No transactions found.</p>";
        return;
    }

     
    $trsanctions = "";
    if (isset($data['transactions']) && ! empty($data['transactions'])) {
        $trsanctions = $data['transactions'];
    } else {
        echo "<p>No transactions found.</p>";
        return;
    }
   
    // Display the transactions in a table
    echo "<table id='speed-transactions-table' class='wp-list-table widefat fixed striped'>";
    echo "<thead>
            <tr>
                <th>#</th>
                <th>Game ID</th>
                <th>Payment ID</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Currency</th>
                <th>Transaction ID</th>
                <th>Created Date</th>
                <th>Modified Date</th>
            </tr>
          </thead>";
    echo "<tbody>";

    foreach ($trsanctions as $key => $transaction) {
        echo "<tr>";
        echo "<td>" . $key + 1 . "</td>";
        echo "<td>" . esc_html($transaction['game_id']) . "</td>";
        echo "<td>" . esc_html($transaction['payment_id']) . "</td>";
        echo "<td>" . esc_html($transaction['amount']) . "</td>";
        echo "<td>" . esc_html($transaction['status']) . "</td>";
        echo "<td>" . esc_html(ucwords($transaction['currency'])) . "</td>";
        echo "<td>" . esc_html($transaction['transaction_id']) . "</td>";
        echo "<td>" . date('M d, Y H:i:s',  strtotime($transaction['created_at'])) . "</td>";
        echo "<td>" . date('M d, Y H:i:s',  strtotime($transaction['updated_at'])) . "</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
}
