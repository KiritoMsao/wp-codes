<?php
/*
**************************************************************************

Plugin Name:  WP Words Filter Plugin
Description:  WP Words Filter Plugin is a useful plugin.
Plugin URI:   https://abdelilahelhaddad.xyz/projects/
Version:      1.0.0
Author:       Abdelilah Elhaddad
Author URI:   http://abdelilahelhaddad.xyz/
Text Domain:  wplearnwf
Domain Path:  /languages
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html

**************************************************************************
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WordsFilterPlugin
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'wordsFilter'));
        add_action('admin_init', array($this, 'wordsFilterSettings'));
        if (get_option('words_to_filter_plugin')) add_filter('the_content', array($this, 'wordsFilterLogic'));
    }

    function wordsFilterSettings()
    {
        add_settings_section('replacement-section', null, null, 'replacement-settings-page');

        //Replacement Text Field
        register_setting('replacement-fields', 'replacementText');
        add_settings_field('replacement-text', 'Replacement Text', array($this, 'replacementHTML'), 'replacement-settings-page', 'replacement-section');
    }

    function replacementHTML()
    { ?>
        <input type="text" name="replacementText" value="<?php echo esc_attr(get_option('replacementText', '***')) ?>">
        <p class="description">Leave it blank if you want to remove the filtered words.</p>
        <?php }

    function wordsFilterLogic($content)
    {
        $filteredWords = explode(',', get_option('words_to_filter_plugin'));
        $filteredWordsTrimmed = array_map('trim', $filteredWords);
        return str_ireplace($filteredWordsTrimmed, esc_html(get_option('replacementText', '***')), $content);
    }

    function wordsFilter()
    {
        $mainPageHook = add_menu_page('Words To Filter', 'Words Filter', 'manage_options', 'words-filter-page', array($this, 'wordFilterPage'), 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMCAyMEMxNS41MjI5IDIwIDIwIDE1LjUyMjkgMjAgMTBDMjAgNC40NzcxNCAxNS41MjI5IDAgMTAgMEM0LjQ3NzE0IDAgMCA0LjQ3NzE0IDAgMTBDMCAxNS41MjI5IDQuNDc3MTQgMjAgMTAgMjBaTTExLjk5IDcuNDQ2NjZMMTAuMDc4MSAxLjU2MjVMOC4xNjYyNiA3LjQ0NjY2SDEuOTc5MjhMNi45ODQ2NSAxMS4wODMzTDUuMDcyNzUgMTYuOTY3NEwxMC4wNzgxIDEzLjMzMDhMMTUuMDgzNSAxNi45Njc0TDEzLjE3MTYgMTEuMDgzM0wxOC4xNzcgNy40NDY2NkgxMS45OVoiIGZpbGw9IiNGRkRGOEQiLz4KPC9zdmc+Cg==', 100);
        add_submenu_page('words-filter-page', 'Words To Filter', 'Words List', 'manage_options', 'words-filter-page', array($this, 'wordFilterPage'));
        add_submenu_page('words-filter-page', 'Words Filter Options', 'Options', 'manage_options', 'words-filter-options-subpage', array($this, 'wordFilterOptionsSubPage'));
        add_action("load-{$mainPageHook}", array($this, "mainPageAssets"));
    }

    function mainPageAssets()
    {
        wp_enqueue_style('filterAdminCss', plugin_dir_url(__FILE__) . 'style.css');
    }

    function handleForm()
    {
        if (wp_verify_nonce($_POST['filterWordsNonce'], 'saveFilterWords') and current_user_can('manage_options')) {
            update_option('words_to_filter_plugin', sanitize_text_field($_POST['words_to_filter'])); ?>
            <div class="updated">
                <p>Words Saved</p>
            </div> <?php
                } else { ?>
            <div class="error">
                <p>Sorry, you do not have permission to perform that action.</p>
            </div>
        <?php }
            }

            function wordFilterPage()
            { ?>
        <div class="wrap">
            <h1>Words Filter</h1>
            <?php if ($_POST['submitfiltereddata'] == "true") {
                    $this->handleForm();
                } ?>
            <form method="POST">
                <input type="hidden" name="submitfiltereddata" value="true">
                <?php wp_nonce_field('saveFilterWords', 'filterWordsNonce') ?>
                <label for="words_to_filter">
                    <p>Enter comma between words to separate them</p>
                </label>
                <div class="word-filter__flex-container">
                    <textarea name="words_to_filter" id="words_to_filter" placeholder="bad, mean, racist"><?php echo esc_textarea(get_option('words_to_filter_plugin')) ?></textarea>
                </div>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </form>
        </div>
    <?php }

            function wordFilterOptionsSubPage()
            { ?>
        <div class="wrap">
            <h1>Word Filter Options</h1>
            <form action="options.php" method="post">
                <?php
                settings_errors();
                settings_fields('replacement-fields');
                do_settings_sections('replacement-settings-page');
                submit_button();
                ?>
            </form>
        </div>
<?php }
        }

        $wordsFilterPlugin = new WordsFilterPlugin();
