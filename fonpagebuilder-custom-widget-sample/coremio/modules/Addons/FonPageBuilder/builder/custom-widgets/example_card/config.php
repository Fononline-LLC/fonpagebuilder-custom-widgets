<?php
/**
 * FonCustomWidget_example_card
 *
 * Örnek kart widget'ı için konfigürasyon sınıfı.
 *
 * KURAL: Sınıf adı mutlaka FonCustomWidget_{type} formatında olmalıdır.
 *
 * Kısıtlar:
 *  - $_(GET|POST|REQUEST|COOKIE|SERVER) süper globallerine doğrudan erişim YASAK.
 *  - exec(), system(), eval(), shell_exec() vb. YASAK.
 *  - curl_exec(), fsockopen() gibi ağ çağrıları YASAK.
 *  - file_put_contents(), unlink() vb. dosya yazma/silme işlemleri YASAK.
 *
 * PageData sınıfının tüm statik metodlarını kullanabilirsiniz.
 * Örneğin: PageData::button_types($lang), PageData::aligns($lang) vb.
 */
class FonCustomWidget_example_card
{
    /**
     * Widget konfigürasyon dizisini döndürür.
     *
     * $language parametresi: builder genel dil anahtarları + widget'a özgü
     * anahtarlar (CustomWidgetRegistry::getWidgetLang ile yüklenip birleştirilmiş).
     * Anahtarlar widget'ın lang/{lang}.php dosyasından gelir; bilinmeyenler
     * için ?? operatörüyle sabit bir İngilizce fallback kullanılır.
     *
     * @param array<string,string> $language   Builder + widget dil dizisi
     * @param array<string,mixed>  $widgetData Mevcut widget kayıtlı verisi
     * @return array<string,mixed>             Widget panel konfigürasyonu
     */
    public static function getConfig(array $language, array $widgetData): array
    {
        $widget        = $widgetData['widget'] ?? [];
        $multiLanguage = explode(',', (string)($widgetData['language'] ?? 'en'));
        $currentLang   = $widgetData['currentLang'] ?? 'en';

        // --- Çok dilli alan varsayılan değerleri ---
        $defaultTitle = !empty($widget['title'][$currentLang])
            ? $widget['title'][$currentLang]
            : ($language['field_title'] ?? 'Card Title');

        $defaultText = !empty($widget['content_text'][$currentLang])
            ? $widget['content_text'][$currentLang]
            : ($language['field_content_desc'] ?? 'Card content goes here...');

        $defaultWidgetName = !empty($widget['name_widget'][$currentLang])
            ? $widget['name_widget'][$currentLang]
            : ($language['widget_name'] ?? 'Example Card');

        // Çok dilli değerleri doldur
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
            'default' => 'https://picsum.photos/id/1/600/400', //Kendi görsellerinizi kullanabilirsiniz
            'modern'  => 'https://picsum.photos/id/2/600/400', //Kendi görsellerinizi kullanabilirsiniz
        ];

        return [
            // =============================================
            // GENEL AYARLAR PANELI
            // =============================================
            'general' => [
                'title'  => $language['widget_general_settings'] ?? 'General Settings',
                'icon'   => 'fa fa-cog',
                'fields' => [
                    // Widget Adı (builder listesinde görünen isim)
                    // type: textLanguage — çok dilli tek satır metin
                    // values: dil koduna göre dolu değerler dizisi
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
            // İÇERİK PANELI
            // =============================================
            'content' => [
                'title'  => $language['widget_content_settings'] ?? 'Content',
                'icon'   => 'far fa-file-alt',
                'fields' => [
                    // Kart Başlığı
                    // type: textLanguage — çok dilli tek satır metin
                    'title' => [
                        'type'    => 'textLanguage',
                        'default' => $defaultTitle,
                        'values'  => $titleArr,
                        'name'    => $language['field_title'] ?? 'Card Title',
                        'desc'    => '',
                        'inline'  => true,
                    ],

                    // Kart İçerik Metni
                    // type: textareaLanguage — çok dilli çok satır metin
                    'content_text' => [
                        'type'    => 'textareaLanguage',
                        'default' => $defaultText,
                        'values'  => $textArr,
                        'name'    => $language['field_content_text'] ?? 'Content Text',
                        'desc'    => $language['field_content_desc'] ?? 'Enter the card description text.',
                        'inline'  => true,
                    ],

                    // İkon (FontAwesome class)
                    // type: icon — FonPageBuilder'ın entegre FontAwesome ikon seçicisi
                    // default: FontAwesome class string (örn: 'fas fa-star')
                    'icon_class' => [
                        'type'    => 'icon',
                        'default' => !empty($widget['icon_class']) ? $widget['icon_class'] : 'fas fa-star',
                        'name'    => $language['field_icon'] ?? 'Icon',
                        'desc'    => $language['field_icon_desc'] ?? 'Select an icon for the card.',
                        'inline'  => true,
                    ],

                    // Kart Rengi
                    // type: select — açılır liste
                    // values: ['option_value' => 'Görünen Metin', ...] şeklinde anahtar-değer dizisi
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

                    // Hizalama
                    // type: select — values anahtarıyla seçenekler
                    // depend: hangi alana bağlı olarak gösterileceği kuralı
                    //   format: [["alan_adı", "operatör", "değer"], ...]
                    //   operatörler: "=" veya "!="
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
                        // Hizalamayı yalnızca ikon gösterilirken aktif et (depend örneği)
                        'depend'  => [
                            ['card_color', '!=', ''],
                        ],
                    ],
                ],
            ],

            // =============================================
            // BAĞLANTI PANELI
            // =============================================
            'link' => [
                'title'  => $language['tab_link'] ?? 'Link',
                'icon'   => 'fas fa-link',
                'fields' => [
                    // type: text — tek satır metin kutusu
                    // placeholder: gri ipucu metin
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
                        // URL alanı yalnızca button_text doluysa görünsün
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
            // STİL PANELI
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
                    // 'child' ANAHTAR ÖRNEĞİ (üst seviye — "type" anahtarı YOK)
                    // -----------------------------------------------------------------
                    // 'child', bir alan adı altında birden fazla alt alanı (sub-field)
                    // tek blokta gruplamak için kullanılır. Bootstrap row/cols içinde
                    // yan yana gösterilir.
                    //
                    // ÜST SEVİYE ERİŞİM: $widget['card_text_color']['normal']
                    //                     $widget['card_text_color']['hover']
                    //
                    // NOT: 'child' içindeki her alt alan kendi 'type', 'default',
                    //      'name', 'desc' anahtarlarını taşımalıdır.
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

                    // 'child' + 'child_col' => 'tab' — alt alanları sekme (tab) olarak gösterir
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

                    // type: slider — kaydırıcı
                    // min, max, step zorunludur
                    'border_radius' => [
                        'type'    => 'slider',
                        'default' => !empty($widget['border_radius']) ? $widget['border_radius'] : '8',
                        'name'    => $language['field_border_radius'] ?? 'Border Radius',
                        'desc'    => '',
                        'min'     => '0',
                        'max'     => '50',
                        'step'    => '1',
                        'inline'  => true,
                        // addon: alanın sağına/soluna ek metin (opsiyonel)
                        'addon'   => ['right' => 'px'],
                    ],

                    // type: number — sayı girişi
                    // min, max, step zorunlu; addon opsiyonel
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

                    // type: color — renk seçici
                    'bg_color' => [
                        'type'    => 'color',
                        'default' => !empty($widget['bg_color']) ? $widget['bg_color'] : '#ffffff',
                        'name'    => $language['field_bg_color'] ?? 'Background Color',
                        'desc'    => '',
                        'inline'  => true,
                    ],

                    // type: text — responsive modunda kullanım örneği
                    // responsive: true olunca her ekran boyutu (xl/lg/md/sm/xs) için ayrı değer girilebilir
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

