<?php
/**
 * FonCustomWidget_example_card
 *
 * Configuration class for the example card widget.
 *
 * RULE: The class name must be in the FonCustomWidget_{type} format.
 *
 * Restrictions:
 *  - Direct access to $_(GET|POST|REQUEST|COOKIE|SERVER) superglobals is PROHIBITED.
 *  - exec(), system(), eval(), shell_exec(), etc. are PROHIBITED.
 *  - Network calls such as curl_exec() and fsockopen() are PROHIBITED.
 *  - File write/delete operations such as file_put_contents() and unlink() are PROHIBITED.
 *
 * You may use all static methods of the PageData class.
 * For example: PageData::button_types($lang), PageData::aligns($lang), etc.
 */
class FonCustomWidget_example_card
{
    /**
     * Returns the widget configuration array.
     *
     * $language parameter: builder-wide language keys + widget-specific
     * keys (loaded and merged via CustomWidgetRegistry::getWidgetLang).
     * Keys come from the widget's lang/{lang}.php file; for unknown keys,
     * a fixed English fallback is used via the ?? operator.
     *
     * @param array<string,string> $language   Builder + widget language array
     * @param array<string,mixed>  $widgetData Existing saved widget data
     * @return array<string,mixed>             Widget panel configuration
     */
    public static function getConfig(array $language, array $widgetData): array
    {
        $widget        = $widgetData['widget'] ?? [];
        $multiLanguage = explode(',', (string)($widgetData['language'] ?? 'en'));
        $currentLang   = $widgetData['currentLang'] ?? 'en';

        // --- Multilingual field default values ---
        $defaultTitle = !empty($widget['title'][$currentLang])
            ? $widget['title'][$currentLang]
            : ($language['field_title'] ?? 'Card Title');

        $defaultText = !empty($widget['content_text'][$currentLang])
            ? $widget['content_text'][$currentLang]
            : ($language['field_content_desc'] ?? 'Card content goes here...');

        $defaultWidgetName = !empty($widget['name_widget'][$currentLang])
            ? $widget['name_widget'][$currentLang]
            : ($language['widget_name'] ?? 'Example Card');

        // Populate multilingual values
        $titleArr = [];
        $textArr  = [];
        $nameArr  = [];

        foreach ($multiLanguage as $langCode) {
            $titleArr[$langCode] = !empty($widget['title'][$langCode])
                ? $widget['title'][$langCode]
                : $defaultTitle;

            $textArr[$langCode] = !empty($widget['content_text'][$langCode])
                ? $widget['content_text'][$langCode]
                : $defaultText;

            $nameArr[$langCode] = !empty($widget['name_widget'][$langCode])
                ? $widget['name_widget'][$langCode]
                : $defaultWidgetName;
        }

        $styleOptions = [
            'default' => $language['widget_style_default'] ?? 'Default Style',
            'modern'  => $language['widget_style_modern'] ?? 'Modern Style',
        ];
        $stylePreviews = [
            'default' => 'https://picsum.photos/id/1/600/400', //You can use your own images.
            'modern'  => 'https://picsum.photos/id/2/600/400', //You can use your own images.
        ];

        return [
            // =============================================
            // GENERAL SETTINGS PANEL
            // =============================================
            'general' => [
                'title'  => $language['widget_general_settings'] ?? 'General Settings',
                'icon'   => 'fa fa-cog',
                'fields' => [
                    // Widget Name (the name shown in the builder list)
                    // type: textLanguage — multilingual single-line text
                    // values: array of values populated by language code
                    'name_widget' => [
                        'type'    => 'textLanguage',
                        'default' => $defaultWidgetName,
                        'values'  => $nameArr,
                        'name'    => $language['field_widget_name'] ?? 'Widget Name',
                        'desc'    => $language['widget_name_widget_desc'] ?? '',
                        'inline'  => true,
                    ],
                ],
            ],

            // =============================================
            // CONTENT PANEL
            // =============================================
            'content' => [
                'title'  => $language['widget_content_settings'] ?? 'Content',
                'icon'   => 'far fa-file-alt',
                'fields' => [
                    // Card Title
                    // type: textLanguage — multilingual single-line text
                    'title' => [
                        'type'    => 'textLanguage',
                        'default' => $defaultTitle,
                        'values'  => $titleArr,
                        'name'    => $language['field_title'] ?? 'Card Title',
                        'desc'    => '',
                        'inline'  => true,
                    ],

                    // Card Content Text
                    // type: textareaLanguage — multilingual multi-line text
                    'content_text' => [
                        'type'    => 'textareaLanguage',
                        'default' => $defaultText,
                        'values'  => $textArr,
                        'name'    => $language['field_content_text'] ?? 'Content Text',
                        'desc'    => $language['field_content_desc'] ?? 'Enter the card description text.',
                        'inline'  => true,
                    ],

                    // Icon (FontAwesome class)
                    // type: icon — FonPageBuilder's integrated FontAwesome icon picker
                    // default: FontAwesome class string (e.g.: 'fas fa-star')
                    'icon_class' => [
                        'type'    => 'icon',
                        'default' => !empty($widget['icon_class']) ? $widget['icon_class'] : 'fas fa-star',
                        'name'    => $language['field_icon'] ?? 'Icon',
                        'desc'    => $language['field_icon_desc'] ?? 'Select an icon for the card.',
                        'inline'  => true,
                    ],

                    // Card Color
                    // type: select — dropdown list
                    // values: key-value array in the form ['option_value' => 'Display Text', ...]
                    'card_color' => [
                        'type'    => 'select',
                        'default' => !empty($widget['card_color']) ? $widget['card_color'] : 'primary',
                        'name'    => $language['field_card_color'] ?? 'Card Color',
                        'desc'    => $language['field_card_color_desc'] ?? 'Sets the card\'s theme color.',
                        'values'  => [
                            'primary'   => $language['color_primary']   ?? 'Primary',
                            'secondary' => $language['color_secondary'] ?? 'Secondary',
                            'success'   => $language['color_success']   ?? 'Success',
                            'warning'   => $language['color_warning']   ?? 'Warning',
                            'danger'    => $language['color_danger']    ?? 'Danger',
                            'info'      => $language['color_info']      ?? 'Info',
                            'dark'      => $language['color_dark']      ?? 'Dark',
                            'light'     => $language['color_light']     ?? 'Light',
                        ],
                        'inline'  => true,
                    ],

                    // Alignment
                    // type: select — options via the values key
                    // depend: visibility rule for the bound field
                    //   format: [["field_name", "operator", "value"], ...]
                    //   operators: "=" or "!="
                    'text_align' => [
                        'type'    => 'select',
                        'default' => !empty($widget['text_align']) ? $widget['text_align'] : 'center',
                        'name'    => $language['widget_align'] ?? 'Alignment',
                        'desc'    => '',
                        'values'  => [
                            'left'   => $language['align_left']   ?? 'Left',
                            'center' => $language['align_center'] ?? 'Center',
                            'right'  => $language['align_right']  ?? 'Right',
                        ],
                        'inline'  => true,
                        // Conditional visibility: Only show alignment if card_color is not empty
                        'depend'  => [
                            ['card_color', '!=', ''],
                        ],
                    ],
                ],
            ],

            // =============================================
            // LINK PANEL
            // =============================================
            'link' => [
                'title'  => $language['tab_link'] ?? 'Link',
                'icon'   => 'fas fa-link',
                'fields' => [
                    // type: text — single-line text input
                    // placeholder: gray hint text
                    'button_text' => [
                        'type'        => 'text',
                        'default'     => !empty($widget['button_text']) ? $widget['button_text'] : '',
                        'name'        => $language['field_button_text'] ?? 'Button Text',
                        'desc'        => $language['field_button_text_desc'] ?? 'Leave empty to hide the button.',
                        'inline'      => true,
                        'placeholder' => 'More Info',
                    ],
                    'button_url' => [
                        'type'        => 'text',
                        'default'     => !empty($widget['button_url']) ? $widget['button_url'] : '',
                        'name'        => $language['field_button_url'] ?? 'Link URL',
                        'desc'        => $language['field_button_url_desc'] ?? 'Example: /products or https://example.com',
                        'inline'      => true,
                        'placeholder' => 'https://example.com',
                        // Show the URL field only when button_text is filled
                        'depend'      => [
                            ['button_text', '!=', ''],
                        ],
                    ],
                    'button_target' => [
                        'type'    => 'select',
                        'default' => !empty($widget['button_target']) ? $widget['button_target'] : '_self',
                        'name'    => $language['widget_target'] ?? 'Target',
                        'desc'    => '',
                        'values'  => [
                            '_self'  => $language['target_self']  ?? 'Same Window',
                            '_blank' => $language['target_blank'] ?? 'New Window',
                        ],
                        'inline'  => true,
                        'depend'  => [
                            ['button_text', '!=', ''],
                        ],
                    ],
                ],
            ],

            // =============================================
            // STYLE PANEL
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

                    // -----------------------------------------------------------------
                    // 'child' KEY EXAMPLE (top level — NO "type" key)
                    // -----------------------------------------------------------------
                    // 'child' is used to group multiple sub-fields under a single field name
                    // in one block. They are displayed side by side within Bootstrap rows/columns.
                    // 
                    //
                    // TOP-LEVEL ACCESS: $widget['card_text_color']['normal']
                    //                     $widget['card_text_color']['hover']
                    //
                    // NOTE: Each sub-field inside 'child' must have its own 'type', 'default',
                    //      'name', and 'desc' keys.
                    // -----------------------------------------------------------------
                    'card_text_color' => [
                        'name' => $language['field_card_text_color'] ?? 'Card Text Color',
                        'desc' => $language['field_card_text_color_desc'] ?? 'Normal and hover state text colors.',
                        'inline'=> true,
                        'child' => [
                            'normal' => [
                                'type'    => 'color',
                                'default' => !empty($widget['card_text_color']['normal'])
                                    ? $widget['card_text_color']['normal']
                                    : '#333333',
                                'name' => $language['widget_normal_state'] ?? 'Normal',
                                'desc' => '',
                            ],
                            'hover' => [
                                'type'    => 'color',
                                'default' => !empty($widget['card_text_color']['hover'])
                                    ? $widget['card_text_color']['hover']
                                    : '#007a7a',
                                'name' => $language['widget_hover_state'] ?? 'Hover',
                                'desc' => '',
                            ],
                        ],
                    ],

                    // 'child' + 'child_col' => 'tab' — displays sub-fields as tabs
                    'card_bg_color' => [
                        'name'      => $language['field_bg_color'] ?? 'Card Background',
                        'desc'      => '',
                        'inline'=> true,
                        'child_col' => 'tab',
                        'child'     => [
                            'normal' => [
                                'type'    => 'color',
                                'default' => !empty($widget['card_bg_color']['normal'])
                                    ? $widget['card_bg_color']['normal']
                                    : '#ffffff',
                                'name' => $language['widget_normal_state'] ?? 'Normal',
                                'desc' => '',
                            ],
                            'hover' => [
                                'type'    => 'color',
                                'default' => !empty($widget['card_bg_color']['hover'])
                                    ? $widget['card_bg_color']['hover']
                                    : '#f0f9f9',
                                'name' => $language['widget_hover_state'] ?? 'Hover',
                                'desc' => '',
                            ],
                        ],
                    ],

                    // type: slider — range slider
                    // min, max, and step are required
                    'border_radius' => [
                        'type'    => 'slider',
                        'default' => !empty($widget['border_radius']) ? $widget['border_radius'] : '8',
                        'name'    => $language['field_border_radius'] ?? 'Border Radius',
                        'desc'    => '',
                        'min'     => '0',
                        'max'     => '50',
                        'step'    => '1',
                        'inline'  => true,
                        // addon: additional text on the left/right side of the field (optional)
                        'addon'   => ['right' => 'px'],
                    ],

                    // type: number — numeric input
                    // min, max, and step are required; addon is optional
                    'opacity' => [
                        'type'    => 'number',
                        'default' => !empty($widget['opacity']) ? $widget['opacity'] : '100',
                        'name'    => $language['field_opacity'] ?? 'Opacity',
                        'desc'    => '',
                        'min'     => '0',
                        'max'     => '100',
                        'step'    => '1',
                        'addon'   => ['right' => '%'],
                        'inline'  => true,
                    ],

                    // type: color — color picker
                    'bg_color' => [
                        'type'    => 'color',
                        'default' => !empty($widget['bg_color']) ? $widget['bg_color'] : '#ffffff',
                        'name'    => $language['field_bg_color'] ?? 'Background Color',
                        'desc'    => '',
                        'inline'  => true,
                    ],

                    // type: text — usage example in responsive mode
                    // When responsive: true, separate values can be entered for each screen size (xl/lg/md/sm/xs).
                    'padding' => [
                        'type'        => 'text',
                        'default'     => !empty($widget['padding']) ? $widget['padding'] : ["xl" => "20px", "lg" => "", "md" => "", "sm" => "", "xs" => ""],
                        'name'        => $language['field_padding'] ?? 'Padding',
                        'desc'        => $language['field_padding_desc'] ?? 'Example: 20px or 10px 20px',
                        'placeholder' => '0px',
                        'responsive'  => true,
                        'inline'      => true,
                    ],
                ],
            ],
        ];
    }
}

