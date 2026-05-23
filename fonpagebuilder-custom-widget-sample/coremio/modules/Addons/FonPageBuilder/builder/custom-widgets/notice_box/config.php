<?php
/**
 * FonCustomWidget_notice_box
 *
 * Configuration class for the notice box widget.
 *
 * RULE: The class name must be in the FonCustomWidget_{type} format.
 */
class FonCustomWidget_notice_box
{
    /**
     * Returns the widget configuration array.
     *
     * $language parameter: builder-wide language keys + widget-specific
     * keys (lang/tr.php or lang/en.php) are merged and provided together.
     *
     * @param array<string,string> $language   Builder + widget language array
     * @param array<string,mixed>  $widgetData Existing saved widget data
     * @return array<string,mixed>             Widget panel konfigürasyonu
     */
    public static function getConfig(array $language, array $widgetData): array
    {
        $widget        = $widgetData['widget'] ?? [];
        $multiLanguage = explode(',', (string)($widgetData['language'] ?? 'en'));
        $currentLang   = $widgetData['currentLang'] ?? 'en';

        $defaultTitle = !empty($widget['title'][$currentLang])
            ? $widget['title'][$currentLang]
            : ($language['field_title'] ?? 'Title');

        $defaultMessage = !empty($widget['message'][$currentLang])
            ? $widget['message'][$currentLang]
            : ($language['field_message_desc'] ?? 'Enter your notice message here.');

        $defaultWidgetName = !empty($widget['name_widget'][$currentLang])
            ? $widget['name_widget'][$currentLang]
            : ($language['widget_name'] ?? 'Notice Box');

        $titleArr   = [];
        $messageArr = [];
        $nameArr    = [];

        foreach ($multiLanguage as $langCode) {
            $titleArr[$langCode] = !empty($widget['title'][$langCode])
                ? $widget['title'][$langCode]
                : $defaultTitle;

            $messageArr[$langCode] = !empty($widget['message'][$langCode])
                ? $widget['message'][$langCode]
                : $defaultMessage;

            $nameArr[$langCode] = !empty($widget['name_widget'][$langCode])
                ? $widget['name_widget'][$langCode]
                : $defaultWidgetName;
        }

        // For select, values: ['value' => 'Display label', ...]
        $noticeTypes = [
            'info'    => $language['type_info']    ?? 'Info',
            'success' => $language['type_success'] ?? 'Success',
            'warning' => $language['type_warning'] ?? 'Warning',
            'danger'  => $language['type_danger']  ?? 'Danger / Error',
            'primary' => $language['type_primary'] ?? 'Primary',
            'dark'    => $language['type_dark']    ?? 'Dark',
        ];

        $styleOptions = [
            'default' => $language['widget_style_default'] ?? 'Default Style',
            'modern'  => $language['widget_style_modern'] ?? 'Modern Style',
        ];
        $stylePreviews = [
            'default' => 'https://picsum.photos/id/1/600/400', // You can use your own images.
            'modern'  => 'https://picsum.photos/id/2/600/400', // You can use your own images.
        ];

        return [
            // =============================================
            // GENERAL SETTINGS
            // =============================================
            'general' => [
                'title'  => $language['widget_general_settings'] ?? 'General Settings',
                'icon'   => 'fa fa-cog',
                'fields' => [
                    // type: textLanguage — multilingual single-line text
                    // values: array that stores saved values for each language code
                    'name_widget' => [
                        'type'    => 'textLanguage',
                        'default' => $defaultWidgetName,
                        'values'  => $nameArr,
                        'name'    => $language['field_widget_name'] ?? 'Widget Name',
                        'desc'    => '',
                        'inline'  => true,
                    ],
                ],
            ],

            // =============================================
            // CONTENT
            // =============================================
            'content' => [
                'title'  => $language['widget_content_settings'] ?? 'Content',
                'icon'   => 'far fa-file-alt',
                'fields' => [
                    // Notice Type
                    // type: select — dropdown selection list
                    // The values key must be in the form ['option_value' => 'Display Label', ...].
                    'notice_type' => [
                        'type'    => 'select',
                        'default' => !empty($widget['notice_type']) ? $widget['notice_type'] : 'info',
                        'name'    => $language['field_notice_type'] ?? 'Notice Type',
                        'desc'    => $language['field_notice_type_desc'] ?? 'Sets the visual style of the box.',
                        'values'  => $noticeTypes,
                        'inline'  => true,
                    ],

                    // Show/Hide Icon
                    // type: bool — yes/no toggle button (toggle switch)
                    // default: 'yes' (show) or 'no' (hide)
                    'show_icon' => [
                        'type'    => 'bool',
                        'default' => !empty($widget['show_icon']) ? $widget['show_icon'] : 'yes',
                        'name'    => $language['field_show_icon'] ?? 'Show Icon',
                        'desc'    => $language['field_show_icon_desc'] ?? 'Show/hide the notice icon.',
                        'inline'  => true,
                    ],

                    // Title — depend: show when show_icon = yes
                    // type: textLanguage — multilingual single-line text
                    'title' => [
                        'type'    => 'textLanguage',
                        'default' => $defaultTitle,
                        'values'  => $titleArr,
                        'name'    => $language['field_title'] ?? 'Title',
                        'desc'    => $language['field_title_desc'] ?? 'Title of the notice box.',
                        'inline'  => true,
                        // depend: [["field_name", "operator", "value"], ...]
                        // operator "=" or "!="
                        // 4th element "ml" → checks whether the multilingual field has a value
                        'depend'  => [
                            ['show_icon', '=', 'yes'],
                        ],
                    ],

                    // Title font
                    // type: typography — advanced field containing font family, size, weight, and similar properties
                    'title_font'  => [
                        'type'  =>'typography',
                        'default' => !empty($widget['title_font']) ? $widget['title_font'] : PageData::defaultTypography(),
                        'name'    => $language['widget_font_attributes'],
                        'desc'    => $language['widget_font_attributes_desc'],
                        'inline'  => true,
                        'text-align' => false, // disable the 'text-align' property
                    ],

                    // Message
                    // type: textareaLanguage — multilingual multi-line text
                    'message' => [
                        'type'    => 'textareaLanguage',
                        'default' => $defaultMessage,
                        'values'  => $messageArr,
                        'name'    => $language['field_message'] ?? 'Message',
                        'desc'    => $language['field_message_desc'] ?? 'Notice content text.',
                        'inline'  => true,
                    ],

                    // Dismissible?
                    // type: bool — yes/no toggle (default: 'no')
                    'dismissible' => [
                        'type'    => 'bool',
                        'default' => !empty($widget['dismissible']) ? $widget['dismissible'] : 'no',
                        'name'    => $language['field_dismissible'] ?? 'Dismissible',
                        'desc'    => $language['field_dismissible_desc'] ?? 'Allow the user to close this notice?',
                        'inline'  => true,
                    ],

                    // ---------------------------------------------------------------
                    // 'repeatable' FIELD SAMPLE
                    // ---------------------------------------------------------------
                    // 'type' => 'repeatable': Allows the user to dynamically add
                    // and remove repeated items.
                    //
                    // 'child' key: accepts the array returned by getRepeatableItems()
                    // array of field arrays — each item represents the field definitions for that row
                    // of that row.
                    //
                    // Data access: $widget['actions'] — each element is the saved values for one row.
                    // 
                    // ---------------------------------------------------------------
                    'actions' => [
                        'type'  => 'repeatable',
                        'name'  => $language['field_actions'] ?? 'Action Buttons',
                        'child' => self::getRepeatableItems($language, $widgetData),
                    ],
                ],
            ],

            // =============================================
            // STYLE
            // =============================================
            'style' => [
                'title'  => $language['widget_style_settings'] ?? 'Style',
                'icon'   => 'fas fa-palette',
                'fields' => [
                    'widget_style' => [
                        'type'     => 'style_preview',
                        'default'  => !empty($widget['widget_style']) ? $widget['widget_style'] : 'default',
                        'name'     => $language['widget_style'] ?? 'Widget Style',
                        'desc'     => $language['widget_style_desc'] ?? 'Choose a predefined style template.',
                        'values'   => $styleOptions,
                        'previews' => $stylePreviews,
                        'inline'   => true,
                    ],

                    // Left border
                    // type: bool — yes/no toggle
                    'border_left' => [
                        'type'    => 'bool',
                        'default' => !empty($widget['border_left']) ? $widget['border_left'] : 'yes',
                        'name'    => $language['field_border_left'] ?? 'Left Border',
                        'desc'    => $language['field_border_left_desc'] ?? 'Show an accent border on the left side.',
                        'inline'  => true,
                    ],

                    // type: text — single-line text input
                    // placeholder: gray hint text
                    'font_size' => [
                        'type'        => 'text',
                        'default'     => !empty($widget['font_size']) ? $widget['font_size'] : '',
                        'name'        => $language['field_font_size'] ?? 'Font Size',
                        'desc'        => $language['field_font_size_desc'] ?? 'Example: 14px or 1rem. Leave empty for default.',
                        'placeholder' => '14px',
                        'inline'      => true,
                    ],

                    // ---------------------------------------------------------------
                    // 'child' KEY EXAMPLE (top level — NO "type" key)
                    // ---------------------------------------------------------------
                    // 'child' groups multiple sub-fields under a single field name
                    // and displays them grouped within Bootstrap rows/columns.
                    //
                    // TOP-LEVEL ACCESS:
                    //   $widget['accent_color']['normal']
                    //   $widget['accent_color']['hover']
                    //
                    // With the optional 'child_col' => 'tab' key, sub-fields
                    // can be shown as tabs instead of rows.
                    // ---------------------------------------------------------------
                    'accent_color' => [
                        'name'  => $language['field_accent_color'] ?? 'Border Accent Color',
                        'desc'  => $language['field_accent_color_desc'] ?? 'Border Normal and hover accent colors.',
                        'inline'=> true,
                        'child' => [
                            'normal' => [
                                'type'    => 'color',
                                'default' => !empty($widget['accent_color']['normal'])
                                    ? $widget['accent_color']['normal']
                                    : '#007a7a',
                                'name' => $language['widget_normal_state'] ?? 'Normal',
                                'desc' => '',
                            ],
                            'hover' => [
                                'type'    => 'color',
                                'default' => !empty($widget['accent_color']['hover'])
                                    ? $widget['accent_color']['hover']
                                    : '#ff9c01',
                                'name' => $language['widget_hover_state'] ?? 'Hover',
                                'desc' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns the field definitions for all repeated items.
     *
     * If saved items exist, it calls getRepeatableItem() for each one;
     * otherwise it creates one empty default item.
     *
     * @param array<string,mixed> $language   Builder + widget language array
     * @param array<string,mixed> $widgetData Existing saved widget data
     * @return array<int,array<string,mixed>> An array of field definitions for each item
     */
    protected static function getRepeatableItems(array $language, array $widgetData): array
    {
        $widget = $widgetData['widget'] ?? [];
        $data   = [
            'language'      => $language,
            'currentLang'   => $widgetData['currentLang']  ?? 'en',
            'multilanguage' => explode(',', (string)($widgetData['language'] ?? 'en')),
        ];

        $items = [];

        if (!empty($widget['actions']) && is_array($widget['actions'])) {
            foreach ($widget['actions'] as $itemId => $itemData) {
                if ($itemData) {
                    $items[] = self::getRepeatableItem((int) $itemId, $itemData, $data);
                }
            }
        } else {
            $items[] = self::getRepeatableItem(0, [], $data);
        }

        return $items;
    }

    /**
     * Returns the field definition array for a single repeated item.
     *
     * Fields with the 'child' key inside this method are accessed in FLAT (plain) form
     * accessed as: $item['button_color_normal'], $item['button_color_hover']
     * (underscore-separated — NOT an array key).
     *
     * @param int                 $id   Item Index
     * @param array<string,mixed> $item Saved item data
     * @param array<string,mixed> $data Helper data (language, currentLang, etc.)
     * @return array<string,mixed>      Field definitions for this item
     */
    protected static function getRepeatableItem(int $id, array $item, array $data): array
    {
        // Prepare multilingual label values
        $labelDefault = !empty($item['button_label'][$data['currentLang']])
            ? $item['button_label'][$data['currentLang']]
            : ($data['language']['field_button_text'] ?? 'Button');

        $labelArr = [];
        foreach ($data['multilanguage'] as $lang) {
            $labelArr[$lang] = !empty($item['button_label'][$lang])
                ? $item['button_label'][$lang]
                : $labelDefault;
        }

        return [
            // Button Label (multilingual)
            'button_label' => [
                'type'    => 'textLanguage',
                'default' => $labelDefault,
                'values'  => $labelArr,
                'name'    => $data['language']['field_button_text'] ?? 'Button Label',
                'desc'    => '',
                'inline'  => true,
                'as_title' => true, // The value of this field is used as the repeatable item title
            ],

            // Button URL
            'button_url' => [
                'type'        => 'text',
                'default'     => !empty($item['button_url']) ? $item['button_url'] : '',
                'name'        => $data['language']['widget_link'] ?? 'URL',
                'desc'        => '',
                'placeholder' => 'https://example.com',
                'inline'      => true,
            ],

            // Button Style
            'button_style' => [
                'type'    => 'select',
                'default' => !empty($item['button_style']) ? $item['button_style'] : 'primary',
                'name'    => $data['language']['widget_button_style'] ?? 'Button Style',
                'desc'    => '',
                'values'  => [
                    'primary'   => $data['language']['color_primary']   ?? 'Primary',
                    'secondary' => $data['language']['color_secondary'] ?? 'Secondary',
                    'success'   => $data['language']['color_success']   ?? 'Success',
                    'danger'    => $data['language']['color_danger']    ?? 'Danger',
                ],
                'inline' => true,
            ],

            // -----------------------------------------------------------
            // 'child' KEY EXAMPLE — INSIDE REPEATABLE (FLAT ACCESS)
            // -----------------------------------------------------------
            // No 'type' key — only the 'child' key is used.
            //
            // FLAT access (because it is inside repeatable):
            //   $item['button_color_normal']   ← concatenated with underscore
            //   $item['button_color_hover']
            //
            // When used at the top level (outside repeatable):
            //   $widget['button_color']['normal']  ← array key (nested array)
            // -----------------------------------------------------------
            'button_color' => [
                'name' => $data['language']['field_button_color'] ?? 'Button Color',
                'desc' => '',
                'child' => [
                    'normal' => [
                        'type'    => 'color',
                        'default' => !empty($item['button_color_normal'])
                            ? $item['button_color_normal']
                            : '#007a7a',
                        'name' => $data['language']['widget_normal_state'] ?? 'Normal',
                        'desc' => '',
                    ],
                    'hover' => [
                        'type'    => 'color',
                        'default' => !empty($item['button_color_hover'])
                            ? $item['button_color_hover']
                            : '#ff9c01',
                        'name' => $data['language']['widget_hover_state'] ?? 'Hover',
                        'desc' => '',
                    ],
                ],
                'inline'=> true,
            ],

            // -----------------------------------------------------------
            // 'child_repeatable' FIELD EXAMPLE
            // -----------------------------------------------------------
            // Inside a repeatable item, used to define additional repeated sub-items
            // (nested list).
            //
            // 'values' key: Array defining each column. Each item:
            //   'name' → data key (field name to save)
            //   'lang' → label shown in the form
            //   'type' → field type ('text', 'icon', 'select', etc.)
            //
            // 'default' key: initial data used when a new item is added
            //   (array of arrays).
            //
            // Data access: $item['button_badges'] → [['text'=>'...'], ...]
            // -----------------------------------------------------------
            'button_badges' => [
                'type'    => 'child_repeatable',
                'name'    => $data['language']['field_badges'] ?? 'Badges',
                'desc'    => $data['language']['field_badges_desc'] ?? 'Add small badge labels to this button.',
                // If saved data exists, use it; otherwise assign default rows
                // matching the field structure — each element must exactly match
                // the 'name' keys ('text', 'color') defined in 'values'.
                'default' => !empty($item['button_badges']) ? $item['button_badges'] : [
                    [
                        'text'  => 'New',
                        'color' => 'primary',
                    ],
                ],
                // The 'values' array: Defines which sub-fields exist in each row and determines how they appear in the form.
                // Available field 'type' values: 'image', 'text', 'number', 'textLanguage', 'select', 'checkbox', 'color', and 'icon'
                'values'  => [
                    [
                        'name' => 'text',
                        'lang' => $data['language']['widget_text'] ?? 'Badge Text',
                        'type' => 'text',
                    ],
                    [
                        'name' => 'color',
                        'lang' => $data['language']['text_color'] ?? 'Color',
                        'type' => 'select',
                        'options' => [
                            'primary' => $data['language']['color_primary']   ?? 'Primary',
                            'success' => $data['language']['color_success']   ?? 'Success',
                            'danger'  => $data['language']['color_danger']    ?? 'Danger',
                            'warning' => $data['language']['color_warning']   ?? 'Warning',
                        ],
                    ],
                ],
                'inline' => true,
            ],
        ];
    }
}

