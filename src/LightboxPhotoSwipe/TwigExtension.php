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
    private $optionsManager;

    /**
     * Constructor
     */
    public function __construct(OptionsManager $optionsManager)
    {
        $this->optionsManager = $optionsManager;
    }

    /**
     * Get supported filters
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('wptrans', [$this, 'wpTranslate'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Get supported functions
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('wpsettingsfield', [$this, 'wpSettingsField'], ['is_safe' => ['html']]),
            new TwigFunction('wpsubmitbutton', [$this, 'wpSubmitButton'], ['is_safe' => ['html']]),
            new TwigFunction('wpoption', [$this, 'wpOption', ['is_safe' => ['html']]]),
            new TwigFunction('wpcontroltext', [$this, 'wpControlText'], ['is_safe' => ['html']]),
            new TwigFunction('wpcontrolcheckbox', [$this, 'wpControlCheckbox'], ['is_safe' => ['html']]),
            new TwigFunction('wpcontrolradio', [$this, 'wpControlRadio'], ['is_safe' => ['html']]),
            new TwigFunction('wpgetposttypes', [$this, 'wpGetPostTypes', ['is_safe' => ['html']]]),
            new TwigFunction('wptransmulti', [$this, 'wpTranslateMulti', ['is_safe' => ['html']]]),
        ];
    }

    /**
     * Filter to translate text in the frontend
     */
    public function wpTranslate(string $text): string
    {
        return __($text, LightboxPhotoSwipe::SLUG);
    }

    /**
     * Function to create the hidden settings fields required to submit the admin form
     */
    public function wpSettingsField(): string
    {
        return sprintf(
            '<input type="hidden" name="option_page" value="%1s" /><input type="hidden" name="action" value="update" />%2s',
            esc_attr('lightbox-photoswipe-settings-group'),
            wp_nonce_field('lightbox-photoswipe-settings-group-options','_wpnonce', true, false)
        );
    }

    /**
     * Function to create the submit button in the admin form
     */
    public function wpSubmitButton(): string
    {
        return get_submit_button();
    }

    /**
     * Get an option value
     */
    public function wpOption(string $name): string
    {
        switch ($this->optionsManager->getOptionType($name))
        {
            case 'list':
                $value = implode(',', $this->optionsManager->getOption($name));
                break;

            default:
                $value = $this->optionsManager->getOption($name);
                break;
        }

        return _wp_specialchars($value);
    }

    /**
     * Function to create a text control with an optional placeholder in the admin page
     */
    public function wpControlText(string $name, string $placeholder = ''): string
    {
        switch ($this->optionsManager->getOptionType($name)) {
            case 'list':
                $value = implode(',', $this->optionsManager->getOption($name));
                break;

            default:
                $value = $this->optionsManager->getOption($name);
                break;
        }

        return sprintf(
            '<input id="%1$s" class="regular-text" type="text" name="%1$s" value="%2$s" placeholder="%3$s" />',
            esc_attr('lightbox_photoswipe_'.$name),
            esc_attr($value),
            esc_attr($placeholder)
        );
    }

    /**
     * Function to create a checkbox control in the admin page
     *
     * @param string $name
     * @return string
     */
    public function wpControlCheckbox(string $name): string
    {
        return sprintf(
            '<input id="%1$s" type="checkbox" name="%1$s" value="1"%2$s/>',
            esc_attr('lightbox_photoswipe_'.$name),
            1 === (int)$this->optionsManager->getOption($name) ? ' checked' : ''
        );
    }

    /**
     * Function to create a group of radio controls with custom separator in the admin page
     */
    public function wpControlRadio(string $name, array $optionValues, array $optionLabels, string $separator): string
    {
        $value = $this->optionsManager->getOption($name);
        $output = '';
        $num = 0;
        while ($num < count($optionValues)) {
            $output .= sprintf(
                '<label style="margin-right:0.5em"><input id="%1$s" type="radio" name="%1$s" value="%2$s"%3$s/>%4$s</label>%5$s',
                esc_attr('lightbox_photoswipe_'.$name),
                $optionValues[$num],
                $value === $optionValues[$num] ? ' checked' : '',
                $optionLabels[$num] ?? '',
                $separator
            );
            $num++;
        }

        return $output;
    }

    /**
     * Function to get all available post types as comma separated text
     *
     * @return string
     */
    public function wpGetPostTypes(): string
    {
        return _wp_specialchars(implode(', ', get_post_types()));
    }

    /**
     * Function to get a translation for singular and plural form
     *
     * @param string $singular
     * @param string $plural
     * @param int $value
     * @return string
     */
    public function wpTranslateMulti(string $singular, string $plural, int $value): string
    {
        return _n($singular, $plural, $value, LightboxPhotoSwipe::SLUG);
    }
}
