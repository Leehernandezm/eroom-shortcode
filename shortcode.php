function list_user_meetings_shortcode($atts) {
    if (!is_user_logged_in()) {
        return 'Please log in to view your meetings.';
    }

    // Extract attributes with defaults
    $attributes = shortcode_atts(array(
        'include_past' => 'no',  // By default, do not include past events
    ), $atts);

    $user_id = get_current_user_id();
    $args = array(
        'post_type'   => array('stm-zoom', 'stm-zoom-webinar'),
        'author'      => $user_id,
        'posts_per_page' => -1,
        'orderby'     => 'meta_value',
        'meta_key'    => 'stm_date',
        'order'       => 'ASC',
        'meta_query'  => array(
            array(
                'key'     => 'stm_date',
                'value'   => current_time('timestamp') * 1000,  // Convert current timestamp to milliseconds
                'compare' => '>',
                'type'    => 'NUMERIC'
            )
        )
    );

    $meetings_query = new WP_Query($args);
    if ($meetings_query->have_posts()) {
        $output = '<ul>';
        while ($meetings_query->have_posts()) {
            $meetings_query->the_post();
            $full_title = get_the_title(); // Get the full title
            $title_parts = explode(' – ', $full_title); // Split the title at " – "
            $title = isset($title_parts[1]) ? $title_parts[1] : $full_title; // Use the second part if available, else the full title

            $timestamp_ms = get_post_meta(get_the_ID(), 'stm_date', true); // Get the timestamp in milliseconds
            $timestamp = $timestamp_ms / 1000; // Convert milliseconds to seconds
            $event_date = date('Y-m-d', $timestamp); // Format the date
            $event_time = get_post_meta(get_the_ID(), 'stm_time', true); // Get the specific event time
            
            // Convert 24-hour time to 12-hour time with AM/PM
            $time_24hr = DateTime::createFromFormat('H:i', $event_time);
            $time_12hr = $time_24hr ? $time_24hr->format('h:i A') : 'Time format error';

            $output .= '<li>' . esc_html($title) . ' - Event Time: ' . esc_html($event_date) . ' at ' . esc_html($time_12hr) . '</li>';
        }
        $output .= '</ul>';
        wp_reset_postdata();
        return $output;
    } else {
        return 'No meetings found.';
    }
}
add_shortcode('list_user_meetings', 'list_user_meetings_shortcode');
