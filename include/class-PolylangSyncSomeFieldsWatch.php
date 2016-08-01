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

        }

        /**
         * Handle language sync option
         *
         * @action 'pll_copy_post_metas'
         */
        public function filter_keys($keys, $sync, $from, $to, $lang)
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

