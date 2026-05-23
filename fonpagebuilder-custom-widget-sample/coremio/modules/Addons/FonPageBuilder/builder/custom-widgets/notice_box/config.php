<?php
/**
 * FonCustomWidget_notice_box
 *
 * Bildirim kutusu widget'ı için konfigürasyon sınıfı.
 *
 * KURAL: Sınıf adı mutlaka FonCustomWidget_{type} formatında olmalıdır.
 */
class FonCustomWidget_notice_box
{
    /**
     * Widget konfigürasyon dizisini döndürür.
     *
     * $language parametresi: builder genel dil anahtarları + widget'a özgü
     * anahtarlar (lang/tr.php veya lang/en.php) birleştirilmiş biçimde gelir.
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

        // select için values: ['value' => 'Görünen etiket', ...]
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
            'default' => 'https://picsum.photos/id/1/600/400', //Kendi görsellerinizi kullanabilirsiniz
            'modern'  => 'https://picsum.photos/id/2/600/400', //Kendi görsellerinizi kullanabilirsiniz
        ];

        return [
            // =============================================
            // GENEL AYARLAR
            // =============================================
            'general' => [
                'title'  => $language['widget_general_settings'] ?? 'General Settings',
                'icon'   => 'fa fa-cog',
                'fields' => [
                    // type: textLanguage — çok dilli tek satır metin
                    // values: her dil kodu için kayıtlı değerleri tutan dizi
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
            // İÇERİK
            // =============================================
            'content' => [
                'title'  => $language['widget_content_settings'] ?? 'Content',
                'icon'   => 'far fa-file-alt',
                'fields' => [
                    // Bildirim Türü
                    // type: select — açılır seçim listesi
                    // values anahtarı: ['seçenek_değeri' => 'Görünen Etiket', ...] biçiminde olmalı
                    'notice_type' => [
                        'type'    => 'select',
                        'default' => !empty($widget['notice_type']) ? $widget['notice_type'] : 'info',
                        'name'    => $language['field_notice_type'] ?? 'Notice Type',
                        'desc'    => $language['field_notice_type_desc'] ?? 'Sets the visual style of the box.',
                        'values'  => $noticeTypes,
                        'inline'  => true,
                    ],

                    // İkon göster/gizle
                    // type: bool — evet/hayır düğmesi (toggle switch)
                    // default: 'yes' (göster) veya 'no' (gizle)
                    'show_icon' => [
                        'type'    => 'bool',
                        'default' => !empty($widget['show_icon']) ? $widget['show_icon'] : 'yes',
                        'name'    => $language['field_show_icon'] ?? 'Show Icon',
                        'desc'    => $language['field_show_icon_desc'] ?? 'Show/hide the notice icon.',
                        'inline'  => true,
                    ],

                    // Başlık — depend: show_icon = yes iken görünsün
                    // type: textLanguage — çok dilli tek satır metin
                    'title' => [
                        'type'    => 'textLanguage',
                        'default' => $defaultTitle,
                        'values'  => $titleArr,
                        'name'    => $language['field_title'] ?? 'Title',
                        'desc'    => $language['field_title_desc'] ?? 'Title of the notice box.',
                        'inline'  => true,
                        // depend: [["alan_adı", "operatör", "değer"], ...]
                        // operatör "=" veya "!="
                        // 4. eleman "ml" → çok dilli alanın dolu olup olmadığını kontrol eder
                        'depend'  => [
                            ['show_icon', '=', 'yes'],
                        ],
                    ],

                    // Başlık yazı tipi
                    // type: typography — font ailesi, boyutu, kalınlığı vb. özellikleri içeren gelişmiş alan
                    'title_font'  => [
                        'type'  =>'typography',
                        'default' => !empty($widget['title_font']) ? $widget['title_font'] : PageData::defaultTypography(),
                        'name'    => $language['widget_font_attributes'],
                        'desc'    => $language['widget_font_attributes_desc'],
                        'inline'  => true,
                        'text-align' => false, // 'text-align' özelliğini devre dışı bırak
                    ],

                    // Mesaj
                    // type: textareaLanguage — çok dilli çok satır metin
                    'message' => [
                        'type'    => 'textareaLanguage',
                        'default' => $defaultMessage,
                        'values'  => $messageArr,
                        'name'    => $language['field_message'] ?? 'Message',
                        'desc'    => $language['field_message_desc'] ?? 'Notice content text.',
                        'inline'  => true,
                    ],

                    // Kapatılabilir mi?
                    // type: bool — evet/hayır toggle (varsayılan: 'no')
                    'dismissible' => [
                        'type'    => 'bool',
                        'default' => !empty($widget['dismissible']) ? $widget['dismissible'] : 'no',
                        'name'    => $language['field_dismissible'] ?? 'Dismissible',
                        'desc'    => $language['field_dismissible_desc'] ?? 'Allow the user to close this notice?',
                        'inline'  => true,
                    ],

                    // ---------------------------------------------------------------
                    // 'repeatable' ALAN ÖRNEĞİ
                    // ---------------------------------------------------------------
                    // 'type' => 'repeatable': Kullanıcının dinamik olarak eklip
                    // silebileceği yinelenen öge listesi oluşturur.
                    //
                    // 'child' anahtarı: getRepeatableItems() metodundan dönen
                    // öge dizisini (array of field arrays) alır — her öge, o
                    // satırın field tanımlarının dizisidir.
                    //
                    // Veriye erişim: $widget['actions'] — her eleman bir satırın
                    // kaydedilmiş değerleridir.
                    // ---------------------------------------------------------------
                    'actions' => [
                        'type'  => 'repeatable',
                        'name'  => $language['field_actions'] ?? 'Action Buttons',
                        'child' => self::getRepeatableItems($language, $widgetData),
                    ],
                ],
            ],

            // =============================================
            // STİL
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

                    // Sol kenarlık
                    // type: bool — evet/hayır toggle
                    'border_left' => [
                        'type'    => 'bool',
                        'default' => !empty($widget['border_left']) ? $widget['border_left'] : 'yes',
                        'name'    => $language['field_border_left'] ?? 'Left Border',
                        'desc'    => $language['field_border_left_desc'] ?? 'Show an accent border on the left side.',
                        'inline'  => true,
                    ],

                    // type: text — tek satır metin kutusu
                    // placeholder: gri ipucu metin
                    'font_size' => [
                        'type'        => 'text',
                        'default'     => !empty($widget['font_size']) ? $widget['font_size'] : '',
                        'name'        => $language['field_font_size'] ?? 'Font Size',
                        'desc'        => $language['field_font_size_desc'] ?? 'Example: 14px or 1rem. Leave empty for default.',
                        'placeholder' => '14px',
                        'inline'      => true,
                    ],

                    // ---------------------------------------------------------------
                    // 'child' ANAHTAR ÖRNEĞİ (üst seviye — "type" anahtarı YOK)
                    // ---------------------------------------------------------------
                    // 'child', bir alan adı altında birden fazla alt alanı
                    // Bootstrap row/cols içinde gruplayarak gösterir.
                    //
                    // ÜST SEVİYE ERİŞİM:
                    //   $widget['accent_color']['normal']
                    //   $widget['accent_color']['hover']
                    //
                    // 'child_col' => 'tab' opsiyonel anahtarıyla alt alanlar
                    // satır yerine sekme (tab) olarak gösterilebilir.
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
     * Tüm yinelenen öge field tanımlarını döndürür.
     *
     * Kaydedilmiş öge varsa her biri için getRepeatableItem() çağırır;
     * yoksa tek boş varsayılan öge oluşturur.
     *
     * @param array<string,mixed> $language   Builder + widget dil dizisi
     * @param array<string,mixed> $widgetData Mevcut widget kayıtlı verisi
     * @return array<int,array<string,mixed>> Her öge için field tanımları dizisi
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
     * Tek bir yinelenen ögeye ait field tanım dizisini döndürür.
     *
     * Bu metot içindeki 'child' anahtarlı alanlara FLAT (düz) biçimde
     * erişilir: $item['button_color_normal'], $item['button_color_hover']
     * (alt-çizgiyle birleştirilmiş — dizi anahtarı DEĞİL).
     *
     * @param int                 $id   Öge indeksi
     * @param array<string,mixed> $item Kaydedilmiş öge verisi
     * @param array<string,mixed> $data Yardımcı veri (language, currentLang, vb.)
     * @return array<string,mixed>      Bu ögeye ait field tanımları
     */
    protected static function getRepeatableItem(int $id, array $item, array $data): array
    {
        // Çok dilli etiket değerlerini hazırla
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
            // Düğme Etiketi (çok dilli)
            'button_label' => [
                'type'    => 'textLanguage',
                'default' => $labelDefault,
                'values'  => $labelArr,
                'name'    => $data['language']['field_button_text'] ?? 'Button Label',
                'desc'    => '',
                'inline'  => true,
                'as_title' => true, // Bu alanın değeri, repeatable öge başlığı olarak gösterilir
            ],

            // Düğme URL'si
            'button_url' => [
                'type'        => 'text',
                'default'     => !empty($item['button_url']) ? $item['button_url'] : '',
                'name'        => $data['language']['widget_link'] ?? 'URL',
                'desc'        => '',
                'placeholder' => 'https://example.com',
                'inline'      => true,
            ],

            // Düğme Stili
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
            // 'child' ANAHTAR ÖRNEĞİ — REPEATABLE İÇİNDE (FLAT ERİŞİM)
            // -----------------------------------------------------------
            // 'type' anahtarı YOK — sadece 'child' anahtarı kullanılıyor.
            //
            // FLAT (düz) ERİŞİM (repeatable içinde olduğu için):
            //   $item['button_color_normal']   ← alt-çizgiyle birleştirilmiş
            //   $item['button_color_hover']
            //
            // Üst seviyede (repeatable dışında) kullanımda ise:
            //   $widget['button_color']['normal']  ← dizi anahtarı
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
            // 'child_repeatable' ALAN ÖRNEĞİ
            // -----------------------------------------------------------
            // Repeatable bir öge içinde ayrıca yinelenen alt ögeler
            // tanımlamak için kullanılır (iç içe liste).
            //
            // 'values' anahtarı: Her sütunu tanımlayan dizi. Her eleman:
            //   'name' → data anahtarı (kaydedilecek alan adı)
            //   'lang' → formda görünen etiket
            //   'type' → field tipi ('text', 'icon', 'select', vb.)
            //
            // 'default' anahtarı: Yeni öge eklenirken kullanılacak
            //   başlangıç verileri (dizi dizisi).
            //
            // Veriye erişim: $item['button_badges'] → [['text'=>'...'], ...]
            // -----------------------------------------------------------
            'button_badges' => [
                'type'    => 'child_repeatable',
                'name'    => $data['language']['field_badges'] ?? 'Badges',
                'desc'    => $data['language']['field_badges_desc'] ?? 'Add small badge labels to this button.',
                // Kaydedilmiş veri varsa onu kullan; yoksa alan yapısına uygun
                // varsayılan satır ata — her eleman 'values' içindeki 'name'
                // anahtarlarıyla ('text', 'color') eşleşmelidir.
                'default' => !empty($item['button_badges']) ? $item['button_badges'] : [
                    [
                        'text'  => 'New',
                        'color' => 'primary',
                    ],
                ],
                // 'values' dizisi: Her satırda hangi alt alanların olduğunu tanımlar ve bunların formda nasıl görüneceğini belirler.
                // Kullanılabilecek Alan (field) 'type' değerleri: 'image', 'text', 'number', 'textLanguage', 'select', 'checkbox','color ve 'icon'
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

