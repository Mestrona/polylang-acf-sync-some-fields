<?php

if (!class_exists('PolylangSyncSomeFieldsWatch')) :
    class PolylangSyncSomeFieldsWatch
    {
        /**
         * Holding the singleton instance
         */
        private static $_instance = null;

        /**
         * @return PolylangPostClonerWatchMeta
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Prevent from creating more instances
         */
        private function __clone()
        {
        }


        private function __construct()
        {
            add_filter('pll_copy_post_metas', array(&$this, 'filter_keys'), 20, 3);
            add_action('acf/render_field_settings', array(&$this, 'action_acf_create_field_options'), 10, 1);
            add_action('acf/render_field', array(&$this, 'action_acf_create_field'), 10, 1);
            add_action('init', array(&$this, 'register_strings'));

            add_action('admin_enqueue_scripts', 'load_assets');
            function load_assets() {
                wp_register_style('pll_acf_sync_some_fields', plugins_url('/../css/admin.css',__FILE__ ));
                wp_enqueue_style('pll_acf_sync_some_fields');
            }

        }

        /**
         * Display label if set to sync on editing screen
         * @param $field
         */

        public function action_acf_create_field($field)
        {
            if (get_post_type() === 'acf-field-group') {
                return;
            }

            $sync = isset($field['lang_sync']) ? $field['lang_sync'] : 1;
            if ($sync) {
                echo '<div class="langSyncIcon">&nbsp;</div>';
            }
        }
        /**
         * Register Field Strings so they can be translated
         * @param $field
         * @param $post_id
         */

        public function register_strings()
        {
            if (!PLL_ADMIN) {
                return;
            }
            $return = acf_get_field_groups();

            foreach($return as $group) {

                $lang = pll_get_post_language($group['ID']);
                if ($lang != 'de') {
                    continue;
                }
                pll_register_string('group_' . $group['ID'] . '_title', $group['title']);

                $fields = acf_get_fields($group['ID']);
                foreach($fields as $field) {

                    pll_register_string($field['key'] . '_label', $field['label']);
                    if (!isset($field['choices'])) {
                        continue;
                    }
                    foreach($field['choices'] as $key=>$choiceValue) {
                        pll_register_string($field['key'] . '_label_choice_' . $key, $choiceValue);
                    }
                }
            }
        }

        /**
         * Handle language sync option
         *
         * @action 'pll_copy_post_metas'
         */
        public function filter_keys($keys)
        {
            /**
             * Show overlay for saving post first, if new post is created
             */

            $url = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

            if (strpos($url,'from_post') !== false && strpos($url,'new_lang') !== false) {
                include(__DIR__ . '/../js/saveFirstOverlay.php');
                return $keys;
            }

            /**
             * Post ID needed for get_post_meta if nested ACF
             */
            global $post;


            /**
             * Used for Debuging - Shows all Keys
             */


//            echo '<div class="field" style="margin: 50px 0; width: 80%; margin-left: 20%;">';
//            echo "<h2>Passed PLL Keys</h2>";
//
//            foreach($keys as $index => $item) {
//
//                $fieldToSync = false;
//
//                $acfSystemField = get_post_meta( $post->ID, "_".$item, true);
//
//                if(get_field_object($acfSystemField)) {
//                    echo "<span style='color: green;'>".$item."<br></span>";
//                } else {
//                    /**
//                     * Check if this is a nested ACF Field by getting the field ID as a Substring from the System Field
//                     */
//                    $acfSystemField = get_post_meta( $post->ID, "_".$item, true);
//                    $needle = "_field";
//                    $startPos = strpos($acfSystemField, $needle);
//                    $fieldId = substr($acfSystemField , $startPos + 1);
//                    if(get_field_object($fieldId)) {
//                        // Is nested ACF field
//                        echo "<span style='color: green;'>".$item."<br></span>";
//                    } else {
//                        /**
//                         * Check if this is a ACF System Field by getting the field ID
//                         */
//                        $acfSystemField = get_post_meta( $post->ID, "_".$item, true);
//                        if(get_field_object($acfSystemField)) {
//                            // Is nested ACF field
//                            echo "<span style='color: green;'>".$item."<br></span>";
//                        } else {
//
//                            // Not an ACF Field
//                            echo "<span style='color: red;'>" . $item . "<br></span>";
//                        }
//                    }
//                }
//
//                if (!$fieldToSync) {
//                    unset($keys[$index]);
//                }
//            }
//
//            echo '</div>';





            foreach($keys as $index => $item) {

                $fieldToSync = false;

                $acfSystemField = get_post_meta( $post->ID, "_".$item, true);

                if(get_field_object($acfSystemField)) {
                    if(get_field_object($acfSystemField)["lang_sync"]) {
                        $fieldToSync = true;
                    }
                } else {
                    /**
                     * Check if this is a nested ACF Field by getting the field ID as a Substring from the System Field
                     */
                    $acfSystemField = get_post_meta( $post->ID, "_".$item, true);
                    $needle = "_field";
                    $startPos = strpos($acfSystemField, $needle);
                    $fieldId = substr($acfSystemField , $startPos + 1);
                    if(get_field_object($fieldId)) {
                        // Is nested ACF field
                        if(get_field_object($fieldId)["lang_sync"]) {
                            $fieldToSync = true;
                        }
                    } else {
                        // Not an ACF Field
                        continue;
                    }
                }

                if (!$fieldToSync) {
                    unset($keys[$index]);
                }
            }

            return $keys;

        }

        /**
         * Add option to ACF fields about sync
         *
         * @param $field
         */
        public function action_acf_create_field_options($field)
        {
            //var_dump($field);
            acf_render_field_setting( $field, array(
                'label'         => __('Sync between all languages?'),
                'instructions'  => '',
                'type' => 'true_false',
                'ui' => 1,
                'name' => 'lang_sync',
                'value' => isset($field['lang_sync']) ? $field['lang_sync'] : 0,
            ), true);

        }
    }

endif;

