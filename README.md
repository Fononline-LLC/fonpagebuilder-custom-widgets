# FonPageBuilder — Custom Widget System

This document explains how to add **your own custom widgets** to FonPageBuilder and
how to use all supported **field types**.

---

## 🖥️ Frontend Render Flow

After widget configuration is defined on the builder side (`manifest.json`, `config.php`, `lang/*`), 
the frontend render is performed by the theme.

```
templates/website/{theme_name}/builder/
├── custom-widgets/
│   ├── example_card/
│   │   ├── renderer.php            ← Required: render class
│   └── notice_box/
│       ├── renderer.php            ← Required: render class
```

### Renderer class convention

- File: `templates/website/{theme_name}/builder/custom-widgets/{type}/renderer.php`
- Class name: `FonCustomWidgetRenderer_{type}`
- Required static method: `render(object $theme, object $content, string $moduleId, array $pageData): string`

The `$theme` object can be actively used inside the renderer:

- Asset registration (optional): `$theme->registerWidgetAsset($assetOrArray)`
- Inline CSS registration (optional): `$theme->registerWidgetStyle($page, $css)`
- Inline JS registration (optional): `$theme->registerWidgetScript('footer', $page, $js)`
- `textLanguage`, `textAreaLanguage` Multilingual field type value resolution: `$theme::getFieldMultilingualText($field, $lang, $default)`
- `typography` field type CSS Render Function: `$theme::parseFontProperties($field)`
- `responsive` field CSS Render Function: `$theme::parseResponsiveItems($responsiveItems, $widgetId)`

### Using External Libraries (Assets) in Custom Widgets

If your custom widget needs an external CSS or JS library (e.g.: *lightgallery, Swiper, Select2, etc.*), 
you can ensure these libraries are loaded **only when that widget is used on a page**. 
This keeps your site free from unnecessary code bloat and prevents performance loss.

The system automatically transfers asset registrations from the renderer to the $hoptions global array.


#### Implementation Steps (lightgallery Example)

If you want to use a gallery library like lightgallery in your custom widget, follow these 3 steps:

###### Step 1: Register Asset in Widget
In the `addWidgetAssets` method of your widget's renderer.php file, notify the system of your library's unique identifier (slug). For example: `$requiredAssets = 'lightgallery';` If you want to add multiple assets, you can define them in an array. For example: `$requiredAssets = ['lightgallery', 'swiper', 'select2']`, etc.

```php
private static function addWidgetAssets(
        object $theme
    ): void {
    $requiredAssets = ['lightgallery']; // You can assign the assets you want to add to this variable. For example: ['swiper', 'magnific', 'select2'] etc.

    if (!empty($requiredAssets) && is_callable([$theme, 'registerWidgetAsset'])) {
        $theme->registerWidgetAsset($requiredAssets);
    }

}
```
###### Step 2: Upload Library Files to Theme
Upload the source files of the library you will use to the css and js directories under your theme folder:

- `templates/website/{theme_name}/css/lightgallery.css`
- `templates/website/{theme_name}/js/lightgallery.js`

###### Step 3: Register Files with Asset Manager
The system allows you to check the key you requested with registerWidgetAsset in css_assets.php and js_assets.php files.

- Open the builder/custom_assets/css_assets.php file and link the library's style files:

```php
<?php defined('CORE_FOLDER') OR exit('You can not get in here!'); ?>

<?php if (isset($hoptions) && in_array("lightgallery", $hoptions, true)): ?>
    <link rel="stylesheet" href="<?php echo $tadress; ?>css/lightgallery.css">
<?php endif; ?>

```
- Open the builder/custom_assets/js_assets.php file and link the library's script files:

```php
<?php defined('CORE_FOLDER') OR exit('You can not get in here!'); ?>

<?php if (isset($hoptions) && in_array("lightgallery", $hoptions, true)): ?>
    <script src="<?php echo $tadress; ?>js/lightgallery.js"></script>   
<?php endif; ?>

```
> 💡 **Alternative and General Usage Note**
>
> If you don't want the library or custom code (CSS/JS) you will add to be modular and prefer it to be **loaded on all pages of your site**, you can include this code directly in the following general files:
> - `templates/website/{theme_name}/css/custom.css`
> - `templates/website/{theme_name}/js/custom.js`
>
> **Important Distinction:** While this method consolidates code management into a single file, remember that the relevant CSS and JS files will be loaded on every page load (whether needed or not) and cached by the browser. For performance-focused custom widget development, the `registerWidgetAsset` method (page-specific loading) is always best practice.
> 
---

### Fallback behavior

`callWidget()` in `theme.php` first looks for the built-in method (`{type}_widget`).
If the method doesn't exist, it automatically loads and calls the renderer file above.
If the renderer is not found, it safely returns an empty string.

### `widget_style` behavior

- If you use `style_preview` in `config.php`, the field name **must** be `widget_style`.
- For **each style key** defined in `widget_style.values`, your widget class must have a `renderWidgetStyle{StyleName}` function. Ex: renderWidgetStyleDefault, renderWidgetStyleModern

---

## 📁 Admin Directory Structure
All configuration files for your custom widgets are located in the `builder/custom-widgets/` folder inside coremio/modules/Addons/FonPageBuilder.

```
builder/
├── custom-widgets/              ← All your custom widgets here
│   ├── example_card/            ← Sample widget (type = "example_card")
│   │   ├── manifest.json        ← Required: Widget metadata
│   │   ├── config.php           ← Required: Widget configuration class
│   │   └── lang/
│   │       ├── en.php           ← Required: English language file
│   │       └── tr.php           ← Turkish language file
│   └── notice_box/              ← Another sample widget
│       ├── manifest.json
│       ├── config.php
│       └── lang/
│           ├── en.php
│           └── tr.php
├── CustomWidgetRegistry.php     ← Automatic discovery and security layer (DO NOT MODIFY)
└── PageData.php                 ← Built-in widget list (DO NOT MODIFY)
```

---

## 🚀 Admin - Creating a New Widget

### Step 1: Create a directory
Create a folder with the same name as your widget type under coremio/modules/Addons/FonPageBuilder/builder/custom-widgets/.

```
builder/custom-widgets/{widget_type}/
```

**Rule:** `{widget_type}` can only contain lowercase letters, numbers, and underscores.
Length: 3–40 characters. Must start with a lowercase letter.
**Example:** `my_pricing_table`, `faq_box`, `social_links`

---

### Step 2: Create `manifest.json` file


```json
{
    "type": "my_pricing_table",
    "name": "Pricing Table",
    "group": "content",
    "icon": "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" ...>...</svg>",
    "show": [],
    "version": "1.0.0",
    "author": "Your Name"
}
```

| Field | Description | Required |
|-------|-------------|----------|
| `type` | Widget type (must match directory name) | ✅ |
| `name` | Default display name | ✅ |
| `group` | `content`, `media`, `module`, `other` | ✅ |
| `icon` | SVG or `<i class="...">` icon | — |
| `show` | Page restrictions (see below) | — |

**`show` field examples:**
```json
// Show only on specific page types
"show": { "only": ["hosting", "server"] }

// Do not show on specific page types
"show": { "except": ["domain", "transfer"] }

// Show on every page
"show": []
```

---

### Step 3: Create `config.php` file
The 'name_widget' field is required and is only used to define the component in the component listing in the admin panel.

```php
<?php
class FonCustomWidget_my_pricing_table
{
    public static function getConfig(array $language, array $widgetData): array
    {
        $widget        = $widgetData['widget'] ?? [];
        $multiLanguage = explode(',', (string)($widgetData['language'] ?? 'en'));
        $currentLang   = $widgetData['currentLang'] ?? 'en';

        // ... prepare values ...

        return [
            'general' => [
                'title'  => $language['widget_general_settings'] ?? 'General Settings',
                'icon'   => 'fa fa-cog',
                'fields' => [
                    // This field is required and is used to identify the component in the widget list.
                    'name_widget' => [ 
                        'type'    => 'textLanguage',
                        'default' => 'Pricing Table',
                        'values'  => [], // multilingual values
                        'name'    => $language['widget_name_widget'] ?? 'Widget Name',
                        'desc'    => '',
                        'inline'  => true,
                    ], 
                ],
            ],
            'content' => [
                'title'  => 'Content',
                'icon'   => 'far fa-file-alt',
                'fields' => [
                    // ... your fields ...
                ],
            ],
        ];
    }
}
```

**Rule:** Class name MUST be in the format `FonCustomWidget_{type}`.

---

### Step 4: Create language file (`lang/en.php`)

```php
<?php
return [
    'widget_name'        => 'My Pricing Table',
    'widget_description' => 'A beautiful pricing table widget.',
    'field_plan_name'    => 'Plan Name',
    'field_price'        => 'Price',
];
```

> ⚠️ Language files only allow `string key → string value` pairs.
> Keys must match the pattern `[a-z][a-z0-9_]*`.

---

## 🎨 Supported Field Types

FonPageBuilder's `formField()` engine supports all the following field types.
For each type, required/optional parameters and example usage are explained below.

---

### `text` — Single line text input

```php
'field_name' => [
    'type'        => 'text',          // ✅ required
    'default'     => 'default',       // ✅ required
    'name'        => 'Field Title',   // ✅ required
    'desc'        => 'Description',   // required (can be empty)
    'placeholder' => 'Hint...',       // optional
    'inline'      => true,            // optional (bool, default false)
    'responsive'  => false,           // optional — if true, value per breakpoint
    'addon'       => ['right' => 'px', 'left' => ''], // optional
    'as_title'    => false,           // optional — For repeatable fields, use as accordion field holder title. If not used, accordion titles like #Item1, #Item2 are shown.
    'depend'      => [/*...*/],       // optional — conditional visibility
],
```

**For `responsive: true` `default`:**
```php
'default' => ["xl" => "", "lg" => "", "md" => "", "sm" => "", "xs" => ""],
```

---

### `textLanguage` — Multilingual single line text

```php
'field_name' => [
    'type'        => 'textLanguage',
    'default'     => 'Default text',
    'values'      => ['tr' => 'Turkish text', 'en' => 'English text'],
    'name'        => 'Field Title',
    'desc'        => '',
    'placeholder' => '',            // optional
    'inline'      => true,          // optional
    'as_title'    => false,         // optional
    'depend'      => [/*...*/],     // optional
],
```

> The `values` array contains saved values for each active language code.

---

### `textarea` — Multi-line text

```php
'field_name' => [
    'type'        => 'textarea',
    'default'     => '',
    'name'        => 'Field Title',
    'desc'        => '',
    'placeholder' => '',   // optional
    'inline'      => true, // optional
],
```

---

### `textareaLanguage` — Multilingual multi-line text

```php
'field_name' => [
    'type'    => 'textareaLanguage',
    'default' => 'Default',
    'values'  => ['tr' => 'Turkish', 'en' => 'English'],
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true, // optional
],
```

---

### `textareaEditor` — HTML rich text editor (Summernote)

```php
'field_name' => [
    'type'    => 'textareaEditor',
    'default' => '<p>Content</p>',
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true, // optional
],
```

---

### `textareaEditorLanguage` — Multilingual HTML rich text editor

```php
'field_name' => [
    'type'    => 'textareaEditorLanguage',
    'default' => '',
    'values'  => ['tr' => '<p>Turkish</p>', 'en' => '<p>English</p>'],
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true, // optional
],
```

---

### `hidden` — Hidden input

```php
'field_name' => [
    'type'    => 'hidden',
    'default' => 'fixed_value',
    'name'    => '',
    'desc'    => '',
],
```

---

### `number` — Number input

```php
'field_name' => [
    'type'        => 'number',
    'default'     => '10',
    'name'        => 'Field Title',
    'desc'        => '',
    'min'         => 0,       // ✅ required
    'max'         => 100,     // ✅ required
    'step'        => 1,       // ✅ required
    'inline'      => true,    // optional
    'responsive'  => false,   // optional
    'addon'       => ['right' => 'px', 'left' => ''], // optional
    'placeholder' => '',      // optional
    'depend'      => [/*...*/], // optional
],
```

---

### `select` — Dropdown selection list

```php
'field_name' => [
    'type'      => 'select',
    'default'   => 'option1',         // single value or array (for multiple)
    'name'      => 'Field Title',
    'desc'      => '',
    'values'    => [                   // ✅ required — ['value' => 'Label', ...]
        'option1' => 'Option 1',
        'option2' => 'Option 2',
    ],
    'inline'    => true,               // optional
    'multiple'  => false,              // optional — multiple selection
    'class'     => '',                 // optional — additional CSS class
    'responsive'=> false,              // optional
    'addon'     => ['right' => ''],    // optional
    'depend'    => [/*...*/],          // optional
],
```

---

### `bool` — Yes/No toggle button

```php
'field_name' => [
    'type'    => 'bool',
    'default' => 'yes',    // ✅ 'yes' or 'no'
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true,     // optional
    'depend'  => [/*...*/], // optional
],
```

> ⚠️ `default` value must be `'yes'` or `'no'` — NOT `'1'`/`'0'` or `true`/`false`.

---

### `slider` — Slider (range slider)

```php
'field_name' => [
    'type'    => 'slider',
    'default' => '50',
    'name'    => 'Field Title',
    'desc'    => '',
    'min'     => 0,       // ✅ required
    'max'     => 100,     // ✅ required
    'step'    => 1,       // ✅ required
    'decimal' => 0,       // optional — number of decimal places
    'inline'  => true,    // optional
    'depend'  => [/*...*/], // optional
],
```

---

### `color` — Color picker

```php
'field_name' => [
    'type'    => 'color',
    'default' => '#3366FF',    // HEX, RGB or RGBA
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true,         // optional
    'depend'  => [/*...*/],    // optional
],
```

---

### `gradient` — Gradient color picker

```php
'field_name' => [
    'type'    => 'gradient',
    'default' => [
        'sc'  => 'rgba(0,122,122,1)',   // start color
        'ec'  => 'rgba(255,156,1,1)',   // end color
        'scp' => '0',                   // start position (%)
        'ecp' => '100',                 // end position (%)
        'gt'  => 'linear',              // 'linear' or 'radial'
        'lga' => '45',                  // linear angle (degrees)
        'rga' => 'center_center',       // radial position
    ],
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true, // optional
],
```

---

### `icon` — FontAwesome icon picker

```php
'field_name' => [
    'type'    => 'icon',
    'default' => 'fas fa-star',    // FontAwesome class
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true,             // optional
    'depend'  => [/*...*/],        // optional
],
```

---

### `image` — Image picker

```php
'field_name' => [
    'type'    => 'image',
    'default' => '',    // path or URL; empty = placeholder shown
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true,  // optional
],
```

---

### `imageLanguage` — Multilingual image picker

```php
'field_name' => [
    'type'    => 'imageLanguage',
    'default' => '',
    'values'  => ['tr' => '/image-tr.jpg', 'en' => '/image-en.jpg'],
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true, // optional
],
```

---

### `video` — Video picker

```php
'field_name' => [
    'type'    => 'video',
    'default' => '',    // .mp4 video path or URL
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true, // optional
],
```

---

### `videoLanguage` — Multilingual video picker

```php
'field_name' => [
    'type'    => 'videoLanguage',
    'default' => '',
    'values'  => ['tr' => '/video-tr.mp4', 'en' => '/video-en.mp4'],
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true, // optional
],
```

---

### `typography` — Typography (font) settings

```php
'field_name' => [
    'type'       => 'typography',
    'default'    => PageData::defaultTypography(),  // or manual array
    'name'       => 'Field Title',
    'desc'       => '',
    'inline'     => true,   // optional
    'text-align' => true,   // optional — false: hide text alignment section
],
```

**`PageData::defaultTypography()` structure:**
```php
[
    'font-size'      => ['value' => '', 'unit' => 'px'],
    'line-height'    => ['value' => '', 'unit' => 'px'],
    'letter-spacing' => ['value' => '', 'unit' => 'px'],
    'font-style'     => ['underline' => '0', 'italic' => '0', 'uppercase' => '0'],
    'font-weight'    => '0',
    'text-align'     => ['left' => '0', 'center' => '0', 'right' => '0'],
]
```

---

### `border` — Border picker

```php
'field_name' => [
    'type'    => 'border',
    'default' => 'none',             // 'none' or '1px solid #000000'
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true, // optional
],
```

---

### `shadow` — Shadow picker

```php
'field_name' => [
    'type'    => 'shadow',
    'default' => 'none',             // 'none' or '0px 4px 8px #000000'
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true, // optional
],
```

---

### `headings` — HTML heading tag picker

```php
'field_name' => [
    'type'    => 'headings',
    'default' => 'h3',    // h1, h2, h3, h4, h5, h6, div, p
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true,    // optional
    'depend'  => [/*...*/], // optional
],
```

---

### `datetime` — Date/Time input

```php
'field_name' => [
    'type'    => 'datetime',
    'default' => '',
    'name'    => 'Field Title',
    'desc'    => '',
    'kind'    => 'date',    // 'date', 'time' or 'datetime-local'
    'min'     => '',        // optional — minimum date
    'addon'   => [],        // optional
    'inline'  => true,      // optional
],
```

---

### `style_preview` — Widget Style Preview style picker

> ⚠️ The key of this field must **always** be `widget_style`.
> The frontend renderer uses this key to allow you to create different styles.

```php
'widget_style' => [
    'type'     => 'style_preview',
    'default'  => 'style1',
    'name'     => 'Field Title',
    'desc'     => '',
    'values'   => [             // ✅ required
        'style1' => 'Style 1',
        'style2' => 'Style 2',
    ],
    'previews' => [             // ✅ required — preview image URL for each value
        'style1' => '/image/style1.png',
        'style2' => '/image/style2.png',
    ],
    'inline'   => true, // optional
],
```

---

### `seperator` — Visual section separator

```php
'field_name' => [
    'type'    => 'seperator',
    'default' => 'Section Title',  // Title text to display
    'name'    => '',
    'desc'    => '',
],
```

---

### `info` — Info/warning note

```php
'field_name' => [
    'type'    => 'info',
    'default' => 'Description message.',
    'style'   => 'info',                   // ✅ required: info, warning, success, danger, primary
    'icon'    => 'fas fa-info-circle',     // ✅ required: icon CSS class
    'name'    => '',
    'desc'    => '',
],
```

---

### `category` — Product category picker

```php
'field_name' => [
    'type'    => 'category',
    'default' => 'hosting:5,8',    // '{type}:{id,id}' format or empty - possible values: 'hosting:1,2,3', 'server:4,5', 'special:6', 'sms:7', 'software:8',
    'name'    => 'Field Title',
    'desc'    => '',
    'inline'  => true, // optional
],
```

---

### `sources` — Source/page picker

```php
'field_name' => [
    'type'        => 'sources',
    'default'     => 'none',        // 'none', 'items:1,2,3' or 'cats:4,5'
    'source_type' => 'hosting',     // ✅ required — 'hosting', 'server', 'special', 'sms'
    'name'        => 'Field Title',
    'desc'        => '',
    'inline'      => true,          // optional
],
```

---

### `package` — Package / Product picker
Used to select from product/package categories on your site. Works similarly to the `sources` field but only lists product/package items. You can select any packages independently. You can filter by product group using the `plan_group` parameter. This definition is optional. If 'all' is entered, all packages will be displayed and selectable without any product group filtering. If a product group like 'hosting' is specified, only packages belonging to that product group will be displayed and selectable. If not defined at all, a conditional option list is shown where you first select the product group and then the package. If the `plan_group` parameter is defined, the `default` value should be selected from packages belonging to this plan. For example `hosting:1,2` → Packages with hosting product id 1 and 2 are selected. `server:5,8` → Packages with server product id 5 and 8 are selected.
```php
'field_name' => [
    'type'        => 'package',
    'default'     => 'all:none', // 'all:none' or product_group:1,2,3 format. For example 'hosting:1,2' → Packages with hosting product id 1 and 2 are selected. 'server:5,8' → Packages with server product id 5 and 8 are selected.
    'plan_group' => 'all',     // 🧩 optional — 'all', 'hosting', 'server', 'special', 'sms'  
    'name'        => 'Field Title',
    'desc'        => '',
    'inline'      => true,          // optional
],
```

---

### `repeatable` — Dynamic repeated item list

Creates a dynamic list where users can add and remove rows at runtime.
The `child` key is generated with `getRepeatableItems()` — each element is the field definitions for one row.

```php
// Inside getConfig():
'items' => [
    'type'  => 'repeatable',
    'name'  => 'Item List',   // panel title
    'child' => self::getRepeatableItems($language, $widgetData),
],
```

**`getRepeatableItems()` — Returns all rows:**
```php
protected static function getRepeatableItems(array $language, array $widgetData): array
{
    $widget = $widgetData['widget'] ?? [];
    $data   = [
        'language'      => $language,
        'currentLang'   => $widgetData['currentLang']  ?? 'en',
        'multilanguage' => explode(',', (string)($widgetData['language'] ?? 'en')),
    ];

    $items = [];

    if (!empty($widget['items']) && is_array($widget['items'])) {
        foreach ($widget['items'] as $id => $item) {
            if ($item) {
                $items[] = self::getRepeatableItem((int) $id, $item, $data);
            }
        }
    } else {
        // If no saved data, create one empty default row
        $items[] = self::getRepeatableItem(0, [], $data);
    }

    return $items;
}
```

**`getRepeatableItem()` — Returns field definitions for single row:**
```php
protected static function getRepeatableItem(int $id, array $item, array $data): array
{
    return [
        'field_name' => [
            'type'    => 'text',
            'default' => !empty($item['field_name']) ? $item['field_name'] : '',
            'name'    => $data['language']['field_field_name'] ?? 'Field Name',
            'desc'    => '',
        ],
        // ... other field definitions
    ];
}
```

> ⚠️ Fields with the `'child'` key inside `getRepeatableItem()` are accessed in **flat (plain)** form.
> See the `child` section below for details.

---

### `child_repeatable` — Nested repeated item list

Inside a **repeatable** item, creates a sub-list that users can add and remove.

```php
'social_links' => [
    'type'    => 'child_repeatable',
    'name'    => 'Social Links',
    'desc'    => '',
    // If saved data exists use it; otherwise assign default rows matching the field structure
    // — each array element represents one row
    // and must match the 'name' keys in 'values'.
    'default' => !empty($item['social_links']) ? $item['social_links'] : [
        [
            'icon' => 'fab fa-facebook',
            'url'  => 'https://facebook.com',
        ],
        [
            'icon' => 'fab fa-twitter',
            'url'  => 'https://twitter.com',
        ],
    ],
    // The 'values' array: Defines which sub-fields exist in each row and how they appear in the form.
    // Available Field 'type' values: 'image', 'text', 'number', 'textLanguage', 'select', 'checkbox','color and 'icon'
    'values'  => [                          // ✅ required — column definitions
        [
            'name' => 'icon',               // data key to save
            'lang' => 'Icon',               // form label
            'type' => 'icon',               // field type
        ],
        [
            'name' => 'url',
            'lang' => 'Link',
            'type' => 'text',
        ],
    ],
    'inline'  => true,                      // optional
],
```

> ✅ Each element in the `default` array must **exactly match** the `'name'` keys in `values`.
> That is, each row must be in the form `['icon' => '...', 'url' => '...']` in the example above.
> If you want an empty default, you can use `'default' => !empty($item['field']) ? $item['field'] : []`.

| Parameter | Description | Required |
|-----------|-------------|----------|
| `type` | `'child_repeatable'` | ✅ |
| `name` | Panel label title | ✅ |
| `default` | Initial data (array of arrays) | ✅ |
| `values` | Array defining each column (`name`, `lang`, `type`) | ✅ |
| `desc` | Description | — |
| `inline` | Inline appearance | — |

**Data access:**
```php
// $item['social_links'] → [['icon' => 'fab fa-facebook', 'url' => '...'], ...]
foreach ($item['social_links'] as $link) {
    echo $link['icon'];
    echo $link['url'];
}
```

---

### `child` — Multiple sub-field group (key, not type)

`'child'` is **not a field type**, it's a **special key** in a field definition.
Groups multiple sub-fields (sub-fields) under a single heading, displaying them in Bootstrap row/cols.
In this usage, the `'type'` key **DOES NOT** exist in the parent field definition.

```php
'field_group' => [
    'name'  => 'Normal / Hover Color',   // ✅ required — group title
    'desc'  => '',
    'child' => [
        'normal' => [                    // sub-field key
            'type'    => 'color',
            'default' => '#007a7a',
            'name'    => 'Normal',
            'desc'    => '',
        ],
        'hover' => [
            'type'    => 'color',
            'default' => '#ff9c01',
            'name'    => 'Hover',
            'desc'    => '',
        ],
    ],
    'depend' => [/*...*/],               // optional
],
```

**`child_col` — Show sub-fields as tabs (optional):**
```php
'field_group' => [
    'name'      => 'Gradient',
    'desc'      => '',
    'child_col' => 'tab',               // 'tab' → tab view instead of columns in row
    'child'     => [
        'normal' => ['type' => 'gradient', /* ... */],
        'hover'  => ['type' => 'gradient', /* ... */],
    ],
],
```

#### Data Access Difference — Critical!

| Location | Access form | Example |
|----------|-------------|---------|
| **Top level** (outside repeatable) | Array key | `$widget['field_group']['normal']` |
| **Inside repeatable** | Flat (with underscore) | `$item['field_group_normal']` |

```php
// Top level (outside repeatable) — inside getConfig():
'card_colors' => [
    'name'  => 'Card Color',
    'child' => [
        'normal' => [
            'type'    => 'color',
            'default' => !empty($widget['card_colors']['normal'])  // ← array key
                ? $widget['card_colors']['normal'] : '#ffffff',
            'name' => 'Normal', 'desc' => '',
        ],
        'hover' => [
            'type'    => 'color',
            'default' => !empty($widget['card_colors']['hover'])   // ← array key
                ? $widget['card_colors']['hover'] : '#f0f0f0',
            'name' => 'Hover', 'desc' => '',
        ],
    ],
],

// Inside repeatable — inside getRepeatableItem():
'button_color' => [
    'name'  => 'Button Color',
    'child' => [
        'normal' => [
            'type'    => 'color',
            'default' => !empty($item['button_color_normal'])  // ← flat, underscore
                ? $item['button_color_normal'] : '#007a7a',
            'name' => 'Normal', 'desc' => '',
        },
        'hover' => [
            'type'    => 'color',
            'default' => !empty($item['button_color_hover'])   // ← flat, underscore
                ? $item['button_color_hover'] : '#ff9c01',
            'name' => 'Hover', 'desc' => '',
        ],
    ],
],
```

---

## 🔗 Optional Common Parameters

### `inline` — Inline appearance

```php
'inline' => true,   // Label and field side by side
'inline' => false,  // Label on top, field below (default)
```

---

### `responsive` — Responsive value

Supported in `text`, `number`, `select` types.
When `true`, separate values can be entered for each screen size (xl/lg/md/sm/xs).

```php
'responsive' => true,
'default'    => ["xl" => "", "lg" => "", "md" => "", "sm" => "", "xs" => ""],
```

---

### `addon` — Additional text (input-group)

Adds text to the left/right of input box in `text` and `number` types.

```php
'addon' => ['right' => 'px'],
'addon' => ['left'  => '$'],
'addon' => ['left'  => 'min:', 'right' => 's'],
```

---

### `depend` — Conditional visibility

```php
'depend' => [
    ["field_name", "=",  "dependent_field_name"],        // field_name === 'dependent_field_name' show
    ["field_name", "!=", "dependent_field_name"],        // field_name !== 'dependent_field_name' don't show
    ["field_name", "=", ["dependent_field_name","dependent_field_name2"],        // if field_name is in the specified dependent_field_name values show
    ["field_name", "!=", ["dependent_field_name","dependent_field_name2"],        // if field_name is not in the specified dependent_field_name values show
    ["multilingual_field",  "!=", "", "ml"],       // show if multilingual field is filled
],
```

Multiple rules work with **AND** (AND) logic.

**Example:**
```php
'depend' => [
    ["heading_type",  "=",  "animation"],
    ["animated_text", "!=", "", "ml"],
],
```

---

## 🔒 Security Rules

```php
// ❌ PROHIBITED - Code execution
eval(...);  system(...);  exec(...);  shell_exec(...);  passthru(...);  assert(...);

// ❌ PROHIBITED - File write/delete
file_put_contents(...);  unlink(...);  rename(...);

// ❌ PROHIBITED - Network access
curl_exec(...);  fsockopen(...);

// ❌ PROHIBITED - Super global access
$_POST[...];  $_GET[...];  $_SERVER[...];  $_COOKIE[...];
```

---

## ✅ Permitted Uses

```php
// ✓ PageData helper methods
PageData::button_types($language);
PageData::aligns($language, 'horizontal');
PageData::defaultTypography();
PageData::borders_style($language);
PageData::cssAnimations($language);

// ✓ PHP standard string/array functions
implode(', ', $array);
array_map(fn($v) => trim($v), $array);
sprintf('%d items', count($items));
```

---

## 📝 Ready-Made Sample Widgets

- **`example_card`** — Card widget showing usage of `text`, `select`, `icon`, `bool`, `color`, `number`, `slider`, `responsive` and **`child` (top level, normal/hover color group)**
- **`notice_box`** — Notice box widget showing usage of `bool`, `select`, `textLanguage`, `textareaLanguage`, `depend`, **`repeatable`**, **`child` (flat access inside repeatable)** and **`child_repeatable`**

You can use these files as templates for your own widgets.
You can delete the `example_card` and `notice_box` directories before moving to production.

