<?php
/**
 * Example Card custom widget renderer.
 */
final class FonCustomWidgetRenderer_example_card
{
    /**
     * Prepares the data for rendering the Example Card widget on the frontend and renders the widget style(s).
     *
     * @param object $theme Active theme object.
     * @param object $content Widget content data.
     * @param string $moduleId Widget module ID. Generated uniquely and securely by the theme. E.g., 'example_card_1', 'custom_abc123', etc.
     * @param array<string,mixed> $pageData Page data.
     * @return string Rendered HTML output.
     * @throws \Throwable Passed to the upper layer in case of unexpected critical errors.
     */
    public static function render(
        object $theme,
        object $content,
        string $moduleId,
        array $pageData
    ): string {

        // You can use this to inspect the content data during development. Remember to remove it before going live.
        /*echo "<pre>content<br>";
        print_r(json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        echo "</pre>";*/

        $lang = Language::selected(); //Retrieves the currently active language code globally in the theme. For example: 'en', 'tr', etc.

        $widgetId = 'fon-widget-' . ($moduleId !== '' ? preg_replace('/[^a-zA-Z0-9_-]/', '', $moduleId) : uniqid('custom-', true)); // Creates a safe and unique widget ID. If moduleId is invalid, uniqid is used.

        $widgetData = [];
        $widgetData['name_widget']= $theme::getFieldMultilingualText($content->name_widget ?? null, $lang, 'Example Card'); // This value is standard and is shown only in the admin panel and used to identify the component. You can also use it in your own widgets.
        $widgetData['title'] = $theme::getFieldMultilingualText($content->title ?? null, $lang, 'Card Title');
        $widgetData['content_text'] =$theme::getFieldMultilingualText($content->content_text ?? null, $lang, '');
        $widgetData['icon_class'] = (string) (isset($content->icon_class) && $content->icon_class != 'empty' ? $content->icon_class : 'fa-solid fa-bolt');
        $widgetData['card_color'] = (string) (isset($content->card_color) && $content->card_color ? $content->card_color : '#007a7a');
        $widgetData['text_align'] = (string) (isset($content->text_align) && $content->text_align ? $content->text_align : 'left');
        $widgetData['button_text'] = (string) (isset($content->button_text) && $content->button_text ? $content->button_text : 'Learn More');
        $widgetData['button_url'] = (string) (isset($content->button_url) && $content->button_url ? $content->button_url : '#');
        $widgetData['button_target'] = (string) (isset($content->button_target) && $content->button_target ? $content->button_target : '_self');
        $widgetdata['widget_style'] =  (string)(isset($content->widget_style) && $content->widget_style ? $content->widget_style : 'default');
        $widgetData['card_text_color_normal'] = (string)(isset($content->card_text_color->normal) && $content->card_text_color->normal ? $content->card_text_color->normal : '#333333');
        $widgetData['card_text_color_hover'] = (string)(isset($content->card_text_color->hover) && $content->card_text_color->hover ? $content->card_text_color->hover : '#007a7a');
        $widgetData['card_bg_color_normal'] = (string)(isset($content->card_bg_color->normal) && $content->card_bg_color->normal ? $content->card_bg_color->normal : '#ffffff');
        $widgetData['card_bg_color_hover'] = (string)(isset($content->card_bg_color->hover) && $content->card_bg_color->hover ? $content->card_bg_color->hover : '#f0f9f9');
        $widgetData['border_radius'] = (string) (isset($content->border_radius) && $content->border_radius ? $content->border_radius : '10');
        $widgetData['opacity'] = (string) (isset($content->opacity) && $content->opacity ? $content->opacity : '100');
        $widgetData['bg_color'] = (string) (isset($content->bg_color) && $content->bg_color ? $content->bg_color : '#fff');
        $widgetData['padding'] = (object) (isset($content->padding) && $content->padding ? $content->padding : (object)['xl'=> '20px', 'lg' => '20px', 'md' => '20px', 'sm' => '20px', 'xs' => '20px']);

        //Widget-specific CSS formatting:
        self::renderWidgetCss(
            $widgetId,
            $widgetData,
            $theme,
            $pageData
        );

        //Widget-specific JS formatting:
        self::renderWidgetJs(
            $widgetId,
            $widgetData,
            $theme,
            $pageData
        );

        // Adding widget-specific assets (CSS/JS files, fonts, etc.):
        self::addWidgetAssets(
            $theme
        );

		// Rendering the widget HTML content:
        // 1. Determine the style name (if missing or invalid, it falls back directly to 'default').
        // If you offer multiple styles, you should create a render function for each style. For example: renderWidgetStyleDefault, renderVidgetStyleModern, etc.
        $funcWidget  = 'renderWidgetStyle' . ucfirst($widgetdata['widget_style'] ?? 'default');

        // 2. Check whether the method exists; otherwise fall back to the 'default' method.
        if (!method_exists(self::class, $funcWidget)) {
            $funcWidget = 'renderWidgetStyleDefault';
        }

        // 3. Return from a single place according to the selected widget style.
        return self::$funcWidget(
            $widgetId,
            $widgetData,
            $theme,
            $pageData
        );
    }

    /**
     * Generates the widget HTML for the default style.
     *
     * @param string $widgetId
     * @param array $widgetData
     * @param object $theme
     * @param array<string,mixed> $pageData
     * @return string
     */
    private static function renderWidgetStyleDefault(
        string $widgetId,
        array $widgetData,
        object $theme,
        array $pageData
    ): string {
        // Process and sanitize the data appropriately. You can add any extra checks or processing you consider necessary.
        $textAlignClass = 'text-center';
        if ($widgetData['text_align'] === 'left') {
            $textAlignClass = 'text-start';
        } elseif ($widgetData['text_align'] === 'right') {
            $textAlignClass = 'text-end';
        }
        $output = '';
        $output .= '<div id="' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . '" class="fon-widget-container fon-example-card-widget">';
        $output .= '    <div class="card border-0 shadow-sm fon-example-card border-top border-4 border-' . htmlspecialchars($widgetData['card_color'], ENT_QUOTES, 'UTF-8') . ' ' . $textAlignClass . '">';
        $output .= '        <div class="card-body">';
        $output .= '            <div class="mb-3"><i class="' . htmlspecialchars($widgetData['icon_class'], ENT_QUOTES, 'UTF-8') . ' fs-2"></i></div>';

        $output .= '            <h4 class="card-title mb-2">' . htmlspecialchars($widgetData['title']) . '</h4>';

        // Description check
        if ($widgetData['content_text'] !== '') {
            $output .= '            <div class="card-text mb-3">' . htmlspecialchars($widgetData['content_text']) . '</div>';
        }

        // Button check
        if ($widgetData['button_text'] !== '' && $widgetData['button_url'] !== '') {
            $output .= '            <a href="' . htmlspecialchars($widgetData['button_url'], ENT_QUOTES, 'UTF-8') . '" class="btn btn-' . htmlspecialchars($widgetData['card_color'], ENT_QUOTES, 'UTF-8') . '" target="' . htmlspecialchars($widgetData['button_target'], ENT_QUOTES, 'UTF-8') . '">';
            $output .= '                ' . htmlspecialchars($widgetData['button_text'], ENT_QUOTES, 'UTF-8');
            $output .= '            </a>';
        }

        $output .= '        </div>';
        $output .= '    </div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Generates the widget HTML for the modern style.
     *
     * @param string $widgetId
     * @param array $widgetData
     * @param object $theme
     * @param array<string,mixed> $pageData
     * @return string
     */
    private static function renderWidgetStyleModern(
        string $widgetId,
        array $widgetData,
        object $theme,
        array $pageData
    ): string {
		// Process and sanitize the data appropriately. You can add any extra checks or processing you consider necessary.
        $textAlignClass = 'text-center';
        if ($widgetData['text_align'] === 'left') {
            $textAlignClass = 'text-start';
        } elseif ($widgetData['text_align'] === 'right') {
            $textAlignClass = 'text-end';
        }

		// Widget HTML output
        $output = '';
        $output .= '<div id="' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . '" class="fon-widget-container fon-example-card-widget fon-example-card-modern">';
        $output .= '    <div class="card border-0 fon-example-card ' . $textAlignClass . '">';
        $output .= '        <div class="card-body position-relative">';
        $output .= '            <div class="mb-3">';
        $output .= '                <span class="fon-modern-icon-wrap border border-' . htmlspecialchars($widgetData['card_color'], ENT_QUOTES, 'UTF-8') . '">';
        $output .= '                    <i class="' . htmlspecialchars($widgetData['icon_class'], ENT_QUOTES, 'UTF-8') . ' fs-4"></i>';
        $output .= '                </span>';
        $output .= '            </div>';
        $output .= '            <h4 class="card-title mb-2 fw-semibold">' . htmlspecialchars($widgetData['title']) . '</h4>';

        // Description field check
        if ($widgetData['content_text'] !== '') {
            $output .= '            <div class="card-text mb-3 opacity-90">' . htmlspecialchars($widgetData['content_text']) . '</div>';
        }

        // Button field check
        if ($widgetData['button_text'] !== '' && $widgetData['button_url'] !== '') {

            $output .= '            <a href="' . htmlspecialchars($widgetData['button_url'], ENT_QUOTES, 'UTF-8') . '" class="btn btn-' . htmlspecialchars($widgetData['card_color'], ENT_QUOTES, 'UTF-8') . ' rounded-pill px-4" target="' . htmlspecialchars($widgetData['button_target'], ENT_QUOTES, 'UTF-8') . '">';
            $output .= '                ' . htmlspecialchars($widgetData['button_text'], ENT_QUOTES, 'UTF-8') ;
            $output .= '            </a>';
        }

        $output .= '        </div>';
        $output .= '    </div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Used to add widget-specific CSS code.
     *
     * Do not use <style></style> tags inside the style code. Add only raw CSS code. The theme will add this code to the page appropriately.
     *
     * @param string $widgetId
     * @param array $widgetData
     * @param object $theme
     * @param array<string,mixed> $pageData
     * @return void
     */
    private static function renderWidgetCss(
        string $widgetId,
        array $widgetData,
        object $theme,
        array $pageData
    ): void {
        $page = isset($pageData['page']) && is_string($pageData['page']) && trim($pageData['page']) !== ''
            ? trim($pageData['page'])
            : 'index';
        $css = ''; // You can assign CSS code to this variable using data from your widget settings, add style-specific CSS (Modern, Default, etc.), or place your CSS code directly here.

        if ($widgetData['widget_style'] === 'modern') {
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-example-card {' . "\n";
            $css .= '    background: linear-gradient(135deg, ' . htmlspecialchars($widgetData['card_bg_color_normal'], ENT_QUOTES, 'UTF-8') . ' 0%, rgba(0, 0, 0, 0.06) 100%);' . "\n";
            $css .= '    color: ' . htmlspecialchars($widgetData['card_text_color_normal'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    border-radius: ' . (int) $widgetData['border_radius'] . 'px;' . "\n";
            $css .= '    opacity: ' . (((int) $widgetData['opacity']) / 100) . ';' . "\n";
            $css .= '    border: 1px solid rgba(0, 0, 0, 0.08);' . "\n";
            $css .= '    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);' . "\n";
            $css .= '    transition: all 0.25s ease-in-out;' . "\n";
            $css .= '    overflow: hidden;' . "\n";
            $css .= '    position: relative;' . "\n";
            $css .= '}' . "\n";
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-example-card::before {' . "\n";
            $css .= '    content: \'\';' . "\n";
            $css .= '    position: absolute;' . "\n";
            $css .= '    inset: 0;' . "\n";
            $css .= '    background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.28), transparent 45%);' . "\n";
            $css .= '    pointer-events: none;' . "\n";
            $css .= '}' . "\n";
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-example-card:hover {' . "\n";
            $css .= '    background: linear-gradient(135deg, ' . htmlspecialchars($widgetData['card_bg_color_hover'], ENT_QUOTES, 'UTF-8') . ' 0%, rgba(0, 0, 0, 0.08) 100%);' . "\n";
            $css .= '    color: ' . htmlspecialchars($widgetData['card_text_color_hover'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    transform: translateY(-4px);' . "\n";
            $css .= '    box-shadow: 0 18px 34px rgba(0, 0, 0, 0.16);' . "\n";
            $css .= '}' . "\n";
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-modern-icon-wrap {' . "\n";
            $css .= '    width: 56px;' . "\n";
            $css .= '    height: 56px;' . "\n";
            $css .= '    border-radius: 50%;' . "\n";
            $css .= '    display: inline-flex;' . "\n";
            $css .= '    align-items: center;' . "\n";
            $css .= '    justify-content: center;' . "\n";
            $css .= '    background: rgba(255, 255, 255, 0.22);' . "\n";
            $css .= '    backdrop-filter: blur(2px);' . "\n";
            $css .= '}' . "\n";
        }
        else {
            // Default style
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-example-card {' . "\n";
            $css .= '    background: ' . htmlspecialchars($widgetData['card_bg_color_normal'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    color: ' . htmlspecialchars($widgetData['card_text_color_normal'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    border-radius: ' . (int) $widgetData['border_radius'] . 'px;' . "\n";
            $css .= '    opacity: ' . (((int) $widgetData['opacity']) / 100) . ';' . "\n";
            $css .= '    transition: all 0.25s ease-in-out;' . "\n";
            $css .= '}' . "\n";
            $css .= '#' . htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') . ' .fon-example-card:hover {' . "\n";
            $css .= '    background: ' . htmlspecialchars($widgetData['card_bg_color_hover'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    color: ' . htmlspecialchars($widgetData['card_text_color_hover'], ENT_QUOTES, 'UTF-8') . ';' . "\n";
            $css .= '    transform: translateY(-2px);' . "\n";
            $css .= '}' . "\n";
        }

        // Responsive padding example (based on values from widget settings):

        if($widgetData['padding'] && is_object($widgetData['padding'])) {
            // We prepare responsive padding values in a format compatible with the theme's parseResponsiveItems method. In this example, the same padding value is used for all screen sizes, but you can assign different values per breakpoint if needed. If you want to add more responsive properties, continue by adding them to the responsiveItems array.
            $responsiveItems = [
                '.fon-example-card' => [
                    [
                        'type'  => 'padding',
                        'unit'  => 'px',
                        'value' => $widgetData['padding'],
                    ]
                ],
            ];

            $css .= $theme::parseResponsiveItems($responsiveItems, $widgetId);

        }


        // Save the dynamically generated CSS
        if ($css !== '' && is_callable([$theme, 'registerWidgetStyle'])) {
            $theme->registerWidgetStyle($page, $css);
        }

    }
    /**
     * Used to add widget-specific JavaScript code.
     *
     * Wrap your script code within <script></script> tags. You can use HEREDOC to improve code readability.
     *
     * @param string $widgetId
     * @param array $widgetData
     * @param object $theme
     * @param array<string,mixed> $pageData
     * @return void
     */

    private static function renderWidgetJs(
        string $widgetId,
        array $widgetData,
        object $theme,
        array $pageData
    ): void {
        // You can access data from your widget settings via the {$widgetData[]} variable and use it in your JS code.
        $page = isset($pageData['page']) && is_string($pageData['page']) && trim($pageData['page']) !== ''
            ? trim($pageData['page'])
            : 'index';

        //Sanitize variables for HEREDOC usage
        $widgetIdJs = htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8');
        $pageJs = htmlspecialchars($page, ENT_QUOTES, 'UTF-8');
        $js = <<<JS
<script>
    // As an example, add a simple JavaScript snippet that logs a message to the console when the widget loads. In real-world usage, you can place any widget-specific JavaScript code here.
    console.log("{$widgetData['name_widget']} - {$widgetData['title']} JS Loaded for widget ID: {$widgetIdJs} on page: {$pageJs}");
</script>
JS;

        // Save the dynamically generated JavaScript
        if ($js !== '' && is_callable([$theme, 'registerWidgetScript'])) {
            // If you want to add it to the header, use 'header' instead of 'footer'.
            $theme->registerWidgetScript('footer', $page, $js);
        }

    }

    /**
     * Used to add widget-specific CSS/JS files, fonts, or other assets.
     *
     * If your widget needs custom CSS or JS files, you can use the theme object's appropriate methods within this method to add them. You can discover predefined assets supported by the theme and available for use in your components through the checks in the $hoptions array inside the templates/website/TemaAdi/inc/main-head.php and templates/website/TemaAdi/inc/constant-footer-js.php files. For example: $hoptions['magnific'], $hoptions['jsvectormap'], $hoptions['intlTelInput'], $hoptions['select2'], etc. Remember that you can always add your own custom CSS and JS code to templates/website/TemaAdi/css/custom.css and templates/website/TemaAdi/js/custom.js. However, you can also dynamically add predefined assets supported by the theme within this method.
     *
     * @param object $theme
     * @param array<string,mixed> $pageData
     * @return void
     */

    private static function addWidgetAssets(
        object $theme
    ): void {
        $requiredAssets = []; // You can assign the assets you want to add to this variable. For example: ['magnific', 'select2'], etc.

        if (!empty($requiredAssets) && is_callable([$theme, 'registerWidgetAsset'])) {
            $theme->registerWidgetAsset($requiredAssets);
        }

    }
}

