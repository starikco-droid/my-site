<?php

if (!class_exists('ACHBAdminMenu')) {
  class ACHBAdminMenu
  {
    function __construct()
    {
      add_action('admin_menu', [$this, 'adminMenu']);
      add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
    }

    function adminMenu()
    {
      add_submenu_page(
        'tools.php',
        'Advance Custom HTML',
        'Advance Custom HTML',
        'manage_options',
        'advanced-custom-html',
        [$this, 'renderPage'],
        100
      );
    }

    function renderPage() {
      ?>
      <div id="bplAdminHelpPageWrapper"
      data-info='<?php echo esc_attr( wp_json_encode( [
                'version' => ACHB_VERSION,
                'isPremium' => esc_attr(achbIsPremium()),
            ] ) ); ?>'
      ></div>
      <?php
    }

    function adminEnqueueScripts($hook) {
      if ('tools_page_advanced-custom-html' === $hook) {
        wp_enqueue_style('achb-admin-help', ACHB_DIR_URL . 'build/admin-dashboard.css', [], ACHB_VERSION);
        wp_enqueue_script('achb-admin-help', ACHB_DIR_URL . 'build/admin-dashboard.js', ['react', 'react-dom'], ACHB_VERSION);
        wp_set_script_translations('achb-admin-help', 'custom-html', ACHB_DIR_PATH . 'languages');
      }
    }
    
  }
  new ACHBAdminMenu();
}