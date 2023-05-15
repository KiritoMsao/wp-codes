<?php
/*
**************************************************************************

Plugin Name:  WP Learn Plugin
Description:  WP Learn Plugin is a useful plugin.
Plugin URI:   http://abdelilahelhaddad.xyz/projects/
Version:      1.0.0
Author:       Abdelilah Elhaddad
Author URI:   http://abdelilahelhaddad.xyz/
Text Domain:  wplearnpg
Domain Path:  /languages
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html

**************************************************************************
*/

class WordCountPlugin
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
        add_action('init', array($this, 'languages'));
    }

    function languages()
    {
        load_plugin_textdomain('wplearnpg', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function settings()
    {
        add_settings_section('wcp_first_section', null, null, 'word-count-settings-page');

        //Display Location Field
        add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));

        //Headline Field
        add_settings_field('wcp_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

        //Word Count
        add_settings_field('wcp_wordcount', 'Word Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_wordcount'));
        register_setting('wordcountplugin', 'wcp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        //Character Count
        add_settings_field('wcp_charactercount', 'Character Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_charactercount'));
        register_setting('wordcountplugin', 'wcp_charactercount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        //Read Time
        add_settings_field('wcp_readtime', 'Read time', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_readtime'));
        register_setting('wordcountplugin', 'wcp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    }

    function ifWrap($content)
    {
        if (is_main_query() and is_single() and (get_option('wcp_wordcount', '1') or get_option('wcp_charactercount', '1') or get_option('wcp_readtime', '1'))) {
            return $this->createHTML($content);
        }
        return $content;
    }

    function createHTML($content)
    {
        $html = '<h3>' . esc_html(get_option('wcp_headline', 'Post Statistics')) . '</h3><p>';

        if (get_option('wcp_wordcount', '1') or get_option('wcp_realtime', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if (get_option('wcp_wordcount', '1')) {
            $html .= esc_html__('This post has', 'wplearnpg') . ' ' . $wordCount . ' ' . esc_html__('words', 'wplearnpg') . '.<br>';
        }

        if (get_option('wcp_charactercount', '1')) {
            $html .= esc_html__('This post has', 'wplearnpg') . ' ' . strlen(strip_tags($content)) . ' ' . esc_html__('characters', 'wplearnpg') . '.<br>';
        }

        if (get_option('wcp_realtime', '1')) {
            $readTime = round($wordCount / 225);
            if ($readTime <= 1) {
                $readTime = 1;
            }
            $html .= esc_html__('This post will take about', 'wplearnpg') . ' ' . $readTime . ' ' . esc_html__('minute(s) to read', 'wplearnpg') . '.<br>';
        }

        $html .= '</p>';

        if (get_option('wcp_location', '0') == '0') {
            return $html . $content;
        }
        return $content . $html;
    }

    function sanitizeLocation($input)
    {
        if ($input != '0' and $input != '1') {
            add_settings_error('wcp_location', 'wcp_location_error', 'Display Location must be either Start or End');
            return get_option('wcp_location');
        }
        return $input;
    }

    function checkboxHTML($args)
    { ?>
<input type="checkbox" name="<?php echo $args['theName'] ?>" value="1"
    <?php checked(get_option($args['theName']), '1') ?>>
<?php }

    function headlineHTML()
    { ?>
<input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')) ?>">
<?php }

    function locationHTML()
    { ?>
<select name="wcp_location">
    <option value="0" <?php selected(get_option('wcp_location'), '0') ?>>Post Start</option>
    <option value="1" <?php selected(get_option('wcp_location'), '1') ?>>Post End</option>
</select>
<?php }

    function adminPage()
    {
        add_options_page(
            'Word Count Settings',
            __('Word Count', 'wplearnpg'),
            'manage_options',
            'word-count-settings-page',
            array($this, 'pluginBody')
        );
    }

    function pluginBody()
    { ?>
<div class="wrap">
    <h1>Word Count Settings</h1>
    <form action="options.php" method="POST">
        <?php
                settings_fields('wordcountplugin');
                do_settings_sections('word-count-settings-page');
                submit_button();
                ?>
    </form>
</div>
<?php }
}

$wordCountPlugin = new WordCountPlugin();