<?php
namespace LightboxPhotoSwipe;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Twig extension to provide filters and functions needed for the frontend
 */
class TwigExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('wptrans', [$this, 'wpTranslate'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('wpcontroltext', [$this, 'wpControlText'], ['is_safe' => ['html']]),
            new TwigFunction('wpcontrolcheckbox', [$this, 'wpControlCheckbox'], ['is_safe' => ['html']]),
            new TwigFunction('wpcontrolradio', [$this, 'wpControlRadio'], ['is_safe' => ['html']]),
            new TwigFunction('wpsettingsfield', [$this, 'wpSettingsField'], ['is_safe' => ['html']]),
            new TwigFunction('wpsubmitbutton', [$this, 'wpSubmitButton'], ['is_safe' => ['html']]),
            new TwigFunction('wpgetposttypes', [$this, 'wpGetPostTypes', ['is_safe' => ['html']]]),
            new TwigFunction('wptransmulti', [$this, 'wpTranslateMulti', ['is_safe' => ['html']]]),
        ];
    }

    public function wpControlText(string $name, string $value, ?string $placeholder = null): string
    {
        if ($placeholder) {
            return sprintf(
                '<input id="%1$s" class="regular-text" type="text" name="%1$s" value="%2$s" placeholder="%3$s" />',
                esc_attr($name),
                esc_attr($value),
                esc_attr($placeholder)
            );
        }

        return sprintf(
            '<input id="%1$s" class="regular-text" type="text" name="%1$s" value="%2$s" />',
            esc_attr($name),
            esc_attr($value)
        );
    }

    public function wpControlCheckbox(string $name, string $value): string
    {
        return sprintf(
            '<input id="%1$s" type="checkbox" name="%1$s" value="1"%2$s/>',
            esc_attr($name),
            1 === (int)$value ? ' checked' : ''
        );
    }

    public function wpControlRadio(string $name, string $value, array $optionValues, array $optionLabels, string $separator): string
    {
        $output = '';
        $num = 0;
        while ($num < count($optionValues)) {
            $output .= sprintf(
                '<label style="margin-right:0.5em"><input id="%1$s" type="radio" name="%1$s"%2$s/>%3$s</label>%4$s',
                esc_attr($name),
                $value === $optionValues[$num] ? ' checked' : '',
                $optionLabels[$num] ?? '',
                $separator
            );
            $num++;
        }

        return $output;
    }

    public function wpTranslate(string $text): string
    {
        return __($text, LightboxPhotoSwipe::NAME);
    }

    public function wpSettingsField(): string
    {
        return sprintf(
            '<input type="hidden" name="option_page" value="%1s" /><input type="hidden" name="action" value="update" />%2s',
            esc_attr('lightbox-photoswipe-settings-group'),
            wp_nonce_field('lightbox-photoswipe-settings-group-options','_wpnonce', true, false)
        );
    }

    public function wpSubmitButton(): string
    {
        return get_submit_button();
    }

    public function wpGetPostTypes(): string
    {
        return _wp_specialchars(implode(', ', get_post_types()));
    }

    public function wpTranslateMulti(string $singular, string $plural, int $value): string
    {
        return _n($singular, $plural, $value, LightboxPhotoSwipe::NAME);
    }
}
