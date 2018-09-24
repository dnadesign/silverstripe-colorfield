<?php

namespace Colymba\ColorField;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\View\Requirements;

/**
 * ColorField is a and advanced color picker.
 * Implements JQuery Minicolors, a preview and direct Hex, RGB and Alpha input.
 *
 * @author Thierry Francois @colymba
 */
class ColorField extends FormField
{
    /**
     * Field's max length RRGGBBAAA.
     *
     * @var int
     */
    protected $maxLength = 9;

    /**
     * JQuery Minicolors plugin config
     * https://github.com/claviska/jquery-minicolors.
     *
     * @var array
     */
    protected $jsConfig = [
      'animationSpeed' => 50,
      'animationEasing' => 'swing',
      'change' => null,
      'changeDelay' => 0,
      'control' => 'hue',
      'defaultValue' => '',
      'hide' => null,
      'hideSpeed' => 100,
      'letterCase' => 'lowercase',
      'opacity' => true,
      'position' => 'bottom left',
      'show' => null,
      'showSpeed' => 100,
      'theme' => 'default',
    ];

    /**
     * JQuery Minicolors plugin config overrides.
     *
     * @var array
     */
    protected $jsConfigOverrides = [
      'inline' => true,
    ];

    /**
     * Return a new ColorField.
     *
     * @param string $name     Field's name
     * @param string $title    Field's title
     * @param string $value    Field's value
     * @param array  $jsConfig JQuery Minicolors plugin config
     */
    public function __construct($name, $title = null, $value = null, $jsConfig = [])
    {
        $this->jsConfig = array_merge($this->jsConfig, $jsConfig);

        parent::__construct($name, $title, $value);
    }

    /**
     * Returns the field's HTML attributes.
     *
     * @return array HTML attributes
     */
    public function getAttributes()
    {
        return array_merge(
          parent::getAttributes(),
          [
            'size' => $this->maxLength,
            'class' => 'text colorField',
          ]
        );
    }

    /**
     * Return the JQuery Minicolors plugin config.
     *
     * @return array Minicolors plugin config
     */
    public function getJSConfig()
    {
        return $this->jsConfig;
    }

    /**
     * Sets a JQuery Minicolors plugin config option.
     *
     * @param string $key Config name
     * @param mixed  $val Config value
     */
    public function setJSConfig($key, $val)
    {
        $this->jsConfig[$key] = $val;

        return $this;
    }

    /**
     * Return's the field for the template.
     *
     * @param mixed $properties
     *
     * @return string
     */
    public function Field($properties = [])
    {
        // Requirements::javascript(FRAMEWORK_DIR.'/thirdparty/jquery/jquery.js');
        // Requirements::javascript(FRAMEWORK_DIR.'/thirdparty/jquery-entwine/dist/jquery.entwine-dist.js');

        // Requirements::javascript('colymba/colorfield:client/dist/js/bundle.js');
        // Requirements::css('colymba/colorfield:client/dist/styles/bundle.css');

        Requirements::javascript('colymba/colorfield:client/dist/js/vendor/jquery-minicolors/jquery.minicolors.js');
        Requirements::css('colymba/colorfield:client/dist/js/vendor/jquery-minicolors/jquery.minicolors.css');

        Requirements::javascript('colymba/colorfield:client/dist/js/ColorField.js');
        Requirements::css('colymba/colorfield:client/dist/css/ColorField.css');

        $jsConfig = array_merge($this->jsConfig, $this->jsConfigOverrides);
        $color = DBField::create_field('Color', $this->Value(), 'Color');
        $id = $this->ID();
        $hex = $color->Hex();
        $red = $color->R();
        $green = $color->G();
        $blue = $color->B();
        $alpha = $color->Alpha();

        if (!$jsConfig['opacity']) {
            $alpha = 1;
        }

        $data = [
        'JSConfig' => htmlspecialchars(json_encode($jsConfig)),
        'Options' => [
        'Alpha' => $this->jsConfig['opacity'],
      ],
      'Color' => [
        'Hex' => $hex,
        'R' => $red,
        'G' => $green,
        'B' => $blue,
        'A' => $alpha,
      ],
      'Controls' => [
        'Mode' => DropdownField::create(
            $id.'_mode',
            $jsConfig['control'],
            [
              'hue' => 'Hue',
              'brightness' => 'Brightness',
              'saturation' => 'Saturation',
              'wheel' => 'Wheel',
            ]
        )->addExtraClass('no-change-track colorMode'),

        'Proxy' => HiddenField::create($id.'_proxy', '', $hex)
            ->addExtraClass('no-change-track colorFieldProxy')
            ->setAttribute('data-opacity', $alpha),

        'Hex' => TextField::create($id.'_hex', '', $hex, 6)
            ->addExtraClass('no-change-track hex'),

        'Red' => NumericField::create($id.'_red', '', $red, 3)
            ->addExtraClass('no-change-track mode_wheel r'),

        'Green' => NumericField::create($id.'_green', '', $green, 3)
            ->addExtraClass('no-change-track mode_wheel g'),

        'Blue' => NumericField::create($id.'_blue', '', $blue, 3)
            ->addExtraClass('no-change-track mode_wheel b'),
        /*
        'Hue' => NumericField::create($id . '_hue', '', $hue, 3)
            ->addExtraClass('no-change-track'),

        'Saturation' => NumericField::create($id . '_saturation', '', $saturation, 3)
            ->addExtraClass('no-change-track'),

        'Brightness' => NumericField::create($id . '_brightness', '', $brightness, 3)
            ->addExtraClass('no-change-track'),
        */
        'Alpha' => TextField::create($id.'_alpha', '', $alpha, 3) //using TextField so 'step' can be overriden
            ->setAttribute('min', 0)
            ->setAttribute('max', 1)
            ->setAttribute('step', '0.01')
            ->setAttribute('type', 'number')
            ->addExtraClass('no-change-track alpha'),
      ],
    ];

        return $this->customise($data)->renderWith('ColorField');
    }
}
