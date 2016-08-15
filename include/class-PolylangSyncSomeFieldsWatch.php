<?php


if (!class_exists('PolylangSyncSomeFieldsWatch')) :
    class PolylangSyncSomeFieldsWatch
    {
        /**
         *    Holding the singleton instance
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
         *    Prevent from creating more instances
         */
        private function __clone()
        {
        }

        /**
         */
        private function __construct()
        {
            add_filter('pll_copy_post_metas', array(&$this, 'filter_keys'), 20, 3);
            add_action('acf/create_field_options', array(&$this, 'action_acf_create_field_options'), 10, 1);
            add_action('acf/create_field', array(&$this, 'action_acf_create_field'), 10, 1);
            add_action('init', array(&$this, 'register_strings'));
        }

        public function action_acf_create_field($field)
        {
            if (get_post_type() != 'post') {
                return; // FIXME
            }
            $sync = isset($field['lang_sync']) ? $field['lang_sync'] : 1;
            if ($sync) {
                echo '<small>Synced between languages</small>';
            }
        }
        /**
         * Register Field Strings so they can be translated
         *
         * @param $field
         * @param $post_id
         */
        public function register_strings()
        {
            if (!PLL_ADMIN) {
                return;
            }
            $return = array();
            $return = apply_filters('acf/get_field_groups', $return);
            foreach($return as $group) {
                $lang = pll_get_post_language($group['id']);
                if ($lang != 'en') {
                    continue;
                }
                pll_register_string('group_' . $group['id'] . '_title', $group['title']);

                $fields = array();
                $fields = apply_filters('acf/field_group/get_fields', $fields, $group['id']);

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
            foreach($keys as $index=>$key) {
                $field = get_field_object($key);
                if (!$field) { // no ACF field
                    continue;
                }
                $sync = isset($field['lang_sync']) ? $field['lang_sync'] : 1;
                if (!$sync) {
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
            ?>
            <tr class="foo" data-field_name="<?php echo $field['key']; ?>">
                <td class="label"><label>Sync Field between Languages</label></td>
                <td>
                    <?php
                    do_action('acf/create_field', array(
                        'type' => 'radio',
                        'name' => 'fields[' . $field['key'] . '][lang_sync]',
                        'value' => isset($field['lang_sync']) ? $field['lang_sync'] : 1,
                        'choices' => array(
                            1 => __("Yes", 'acf'),
                            0 => __("No", 'acf'),
                        ),
                        'layout' => 'horizontal',
                    ));
                    ?>
                </td>
            </tr>
            <?php
        }
    }

endif;

