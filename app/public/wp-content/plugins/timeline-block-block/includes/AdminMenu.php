<?php

if (!class_exists('TLGBAdminMenu')) {
  class TLGBAdminMenu {
    
    function __construct() {
      add_action('admin_menu', [$this, 'adminMenu']);
      add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
    }

    function adminMenu() {
      add_submenu_page(
        'tools.php',
        'Timeline Block',
        'Timeline Block',
        'manage_options',
        'timeline-block',
        [$this, 'renderPage'],
        100
      );
    }

    function renderPage() {
      ?>
       <div id="tlgbAdminDashboardWrapper"
            data-info='<?php echo esc_attr( wp_json_encode( [
                'version' => TLGB_VERSION,
                'isPremium' => esc_attr(tlgbIsPremium()),
                'adminUrl' => admin_url()
            ] ) ); ?>'>
        </div>
      <?php
    }

    function adminEnqueueScripts($hook) {
      if ('tools_page_timeline-block' === $hook) {
        wp_enqueue_style('tlgb-admin-dashboard', TLGB_DIR_URL . 'build/admin-dashboard.css', [], TLGB_VERSION);
        wp_enqueue_script('tlgb-admin-dashboard', TLGB_DIR_URL . 'build/admin-dashboard.js', ['react', 'react-dom'], TLGB_VERSION, true);
        wp_set_script_translations('tlgb-admin-help', 'timeline-block', TLGB_DIR_PATH . 'languages');
      }
    }
  }
  new TLGBAdminMenu();
}