<?php
/**
 * Handles the analytics page for WP Capture.
 *
 * @package    WP_Capture
 * @subpackage WP_Capture/includes/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WP_Capture_Admin_Analytics {

    private $plugin;
    private $main_admin; // Reference to WP_Capture_Admin

    public function __construct(WP_Capture $plugin, WP_Capture_Admin $main_admin) {
        $this->plugin = $plugin;
        $this->main_admin = $main_admin;
    }

    /**
     * Render the Analytics page.
     */
    public function display_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Submission Analytics', 'wp-capture'); ?></h1>
            <p><?php esc_html_e('View submission analytics for your forms.', 'wp-capture'); ?></p>
            <?php $this->analytics_table_callback(); ?>
        </div>
        <?php
    }

    /**
     * Callback for rendering the analytics table.
     */
    public function analytics_table_callback() {
        $analytics_data = get_option('wp_capture_analytics_data', array());

        if (empty($analytics_data)) {
            echo '<p>' . __('No submission data yet.', 'wp-capture') . '</p>';
            return;
        }

        echo '<table class="wp-list-table widefat striped fixed">';
        echo '<thead><tr>';
        echo '<th scope="col">' . __('Page/Post Title', 'wp-capture') . '</th>';
        echo '<th scope="col">' . __('Form Identifier', 'wp-capture') . '</th>';
        echo '<th scope="col">' . __('Views', 'wp-capture') . '</th>';
        echo '<th scope="col">' . __('Submissions', 'wp-capture') . '</th>';
        echo '<th scope="col">' . __('Conversion Rate', 'wp-capture') . '</th>';
        echo '<th scope="col">' . __('Last Submission', 'wp-capture') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($analytics_data as $form_id => $data) {
            $post_id = isset($data['post_id']) ? intval($data['post_id']) : 0;
            // $post_title = isset($data['post_title']) ? esc_html($data['post_title']) : __('N/A', 'wp-capture'); // Keep original logic for deleted posts
            $count = isset($data['count']) ? intval($data['count']) : 0;
            $views = isset($data['views']) ? intval($data['views']) : 0; // Initialize views
            $last_submission_timestamp = isset($data['last_submission_timestamp']) ? $data['last_submission_timestamp'] : 0;
            $form_identifier_display = esc_html(substr($form_id, 0, 12) . (strlen($form_id) > 12 ? '...' : ''));

            $title_display = __('N/A', 'wp-capture');
            if ($post_id > 0) {
                $post = get_post($post_id);
                if ($post) {
                    $post_title_val = get_the_title($post_id);
                    if (empty($post_title_val)) {
                         $title_display = '<em>' . __('(Post Deleted or No Title)', 'wp-capture') . ' (ID: ' . $post_id . ')</em>';
                    } else {
                        $title_display = '<a href="' . get_edit_post_link($post_id) . '">' . esc_html($post_title_val) . '</a>';
                    }
                } else {
                    if (!empty($data['post_title']) && $data['post_title'] !== 'N/A' && strpos($data['post_title'], 'Post ID: ') === false) {
                         $title_display = '<em>' . esc_html($data['post_title']) . ' (' . __('Post Deleted', 'wp-capture') . ' - ID: ' . $post_id . ')</em>';
                    } else {
                        $title_display = '<em>' . __('Post Deleted', 'wp-capture') . ' (ID: ' . $post_id . ')</em>';
                    }
                }
            } elseif (!empty($data['post_title']) && $data['post_title'] !== 'N/A') {
                 $title_display = '<em>' . esc_html($data['post_title']) . ' (' . __('No Associated Post', 'wp-capture') . ')</em>';
            }

            echo '<tr>';
            echo '<td>' . $title_display . '</td>'; // Already escaped or contains HTML
            echo '<td>' . $form_identifier_display . '</td>';
            echo '<td>' . $views . '</td>';
            echo '<td>' . $count . '</td>';
            echo '<td>';
            if ($views > 0) {
                $conversion_rate = ($count / $views) * 100;
                echo number_format($conversion_rate, 2) . '%';
            } else {
                echo __('N/A', 'wp-capture');
            }
            echo '</td>';
            echo '<td>';
            if ($last_submission_timestamp > 0) {
                echo sprintf(esc_html__('%1$s at %2$s', 'wp-capture'),
                    wp_date(get_option('date_format'), $last_submission_timestamp),
                    wp_date(get_option('time_format'), $last_submission_timestamp)
                );
            } else {
                echo __('N/A', 'wp-capture');
            }
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }
    
    /** 
     * Print the Section text for Analytics (if used in a settings page context, not directly used here)
     */
    // public function analytics_section_callback() {
    //     echo '<p>' . __('View submission analytics for your forms.', 'wp-capture') . '</p>';
    // }
} 